<?php

declare(strict_types=1);

namespace BjyAuthorizeTest\View;

use BjyAuthorize\Exception\UnAuthorizedException;
use BjyAuthorize\Guard\Route;
use BjyAuthorize\View\RedirectionStrategy;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\ResponseInterface;
use PHPUnit\Framework\TestCase;

/**
 * UnauthorizedStrategyTest view strategy test
 */
class RedirectionStrategyTest extends TestCase
{
    /** @var RedirectionStrategy */
    protected $strategy;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->strategy = new RedirectionStrategy();
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::attach
     * @covers \BjyAuthorize\View\RedirectionStrategy::detach
     */
    public function testAttachDetach()
    {
        $eventManager = $this->getMockBuilder(EventManagerInterface::class)
            ->getMock();

        $callbackDummy = new class {
            public function __invoke()
            {
            }
        };

        $eventManager
            ->expects($this->once())
            ->method('attach')
            ->with()
            ->will($this->returnValue($callbackDummy));
        $this->strategy->attach($eventManager);
        $eventManager
            ->expects($this->once())
            ->method('detach')
            ->with($callbackDummy)
            ->will($this->returnValue(true));
        $this->strategy->detach($eventManager);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     */
    public function testWillIgnoreUnrecognizedResponse()
    {
        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(ResponseInterface::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Route::ERROR));
        $mvcEvent->expects($this->never())->method('setResponse');

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     */
    public function testWillIgnoreUnrecognizedErrorType()
    {
        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(Response::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();
        $route      = $this->createMock(RouteInterface::class);

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getRouter')->will($this->returnValue($route));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue('unknown'));
        $mvcEvent->expects($this->never())->method('setResponse');

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     */
    public function testWillIgnoreOnExistingResult()
    {
        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(Response::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();

        $mvcEvent->expects($this->any())->method('getResult')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Route::ERROR));
        $mvcEvent->expects($this->never())->method('setResponse');

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     */
    public function testWillIgnoreOnMissingRouteMatch()
    {
        $mvcEvent = $this->createMock(MvcEvent::class);
        $response = $this->createMock(Response::class);

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Route::ERROR));
        $mvcEvent->expects($this->never())->method('setResponse');

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     * @covers \BjyAuthorize\View\RedirectionStrategy::setRedirectRoute
     * @covers \BjyAuthorize\View\RedirectionStrategy::setRedirectUri
     */
    public function testWillRedirectToRouteOnSetRoute()
    {
        $this->strategy->setRedirectRoute('redirect/route');
        $this->strategy->setRedirectUri(null);

        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(Response::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();
        $route      = $this->getMockForAbstractClass(RouteInterface::class, [], '', true, true, true, ['assemble']);
        $headers    = $this->createMock(Headers::class);

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getRouter')->will($this->returnValue($route));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Route::ERROR));

        $response->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $response->expects($this->once())->method('setStatusCode')->with(302);

        $headers->expects($this->once())->method('addHeaderLine')->with('Location', 'http://www.example.org/');

        $route
            ->expects($this->any())
            ->method('assemble')
            ->with([], ['name' => 'redirect/route'])
            ->will($this->returnValue('http://www.example.org/'));

        $mvcEvent->expects($this->once())->method('setResponse')->with($response);

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     * @covers \BjyAuthorize\View\RedirectionStrategy::setRedirectUri
     */
    public function testWillRedirectToRouteOnSetUri()
    {
        $this->strategy->setRedirectUri('http://www.example.org/');

        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(Response::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();
        $route      = $this->createMock(RouteInterface::class);
        $headers    = $this->createMock(Headers::class);

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getRouter')->will($this->returnValue($route));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Route::ERROR));

        $response->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $response->expects($this->once())->method('setStatusCode')->with(302);

        $headers->expects($this->once())->method('addHeaderLine')->with('Location', 'http://www.example.org/');

        $mvcEvent->expects($this->once())->method('setResponse')->with($response);

        $this->strategy->onDispatchError($mvcEvent);
    }

    /**
     * @covers \BjyAuthorize\View\RedirectionStrategy::onDispatchError
     * @covers \BjyAuthorize\View\RedirectionStrategy::setRedirectUri
     */
    public function testWillRedirectToRouteOnSetUriWithApplicationError()
    {
        $this->strategy->setRedirectUri('http://www.example.org/');

        $mvcEvent   = $this->createMock(MvcEvent::class);
        $response   = $this->createMock(Response::class);
        $routeMatch = $this->getMockBuilder(RouteMatch::class)->disableOriginalConstructor()->getMock();
        $route      = $this->createMock(RouteInterface::class);
        $headers    = $this->createMock(Headers::class);
        $exception  = $this->createMock(UnAuthorizedException::class);

        $mvcEvent->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $mvcEvent->expects($this->any())->method('getRouteMatch')->will($this->returnValue($routeMatch));
        $mvcEvent->expects($this->any())->method('getRouter')->will($this->returnValue($route));
        $mvcEvent->expects($this->any())->method('getError')->will($this->returnValue(Application::ERROR_EXCEPTION));
        $mvcEvent->expects($this->any())->method('getParam')->with('exception')->will($this->returnValue($exception));

        $response->expects($this->any())->method('getHeaders')->will($this->returnValue($headers));
        $response->expects($this->once())->method('setStatusCode')->with(302);

        $headers->expects($this->once())->method('addHeaderLine')->with('Location', 'http://www.example.org/');

        $mvcEvent->expects($this->once())->method('setResponse')->with($response);

        $this->strategy->onDispatchError($mvcEvent);
    }
}
