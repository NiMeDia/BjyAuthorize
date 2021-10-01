<?php

/** @noinspection ALL */

declare(strict_types=1);

namespace BjyAuthorize\Guard;

use BjyAuthorize\Provider\Resource\ProviderInterface as ResourceProviderInterface;
use BjyAuthorize\Provider\Rule\ProviderInterface as RuleProviderInterface;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\AbstractListenerAggregate;

use function array_keys;

abstract class AbstractGuard extends AbstractListenerAggregate implements
    GuardInterface,
    RuleProviderInterface,
    ResourceProviderInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array[] */
    protected $rules = [];

    /**
     * @param array $rules
     */
    public function __construct(array $rules, ContainerInterface $container)
    {
        $this->container = $container;
        foreach ($rules as $rule) {
            $rule['roles']  = (array) $rule['roles'];
            $rule['action'] = isset($rule['action']) ? (array) $rule['action'] : [null];
            foreach ($this->extractResourcesFromRule($rule) as $resource) {
                $this->rules[$resource] = ['roles' => (array) $rule['roles']];
                if (isset($rule['assertion'])) {
                    $this->rules[$resource]['assertion'] = $rule['assertion'];
                }
            }
        }
    }

    abstract protected function extractResourcesFromRule(array $rule);

    /**
     * {@inheritDoc}
     */
    public function getResources()
    {
        $resources = [];
        foreach (array_keys($this->rules) as $resource) {
            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        $rules = [];
        foreach ($this->rules as $resource => $ruleData) {
            $rule   = [];
            $rule[] = $ruleData['roles'];
            $rule[] = $resource;
            if (isset($ruleData['assertion'])) {
                $rule[] = null;
        // no privilege
                $rule[] = $ruleData['assertion'];
            }

            $rules[] = $rule;
        }

        return ['allow' => $rules];
    }
}
