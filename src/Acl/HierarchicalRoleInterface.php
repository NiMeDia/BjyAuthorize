<?php

declare(strict_types=1);

namespace BjyAuthorize\Acl;

use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Interface for a role with a possible parent role.
 */
interface HierarchicalRoleInterface extends RoleInterface
{
    /**
     * Get the parent role
     *
     * @return RoleInterface|null
     */
    public function getParent();
}
