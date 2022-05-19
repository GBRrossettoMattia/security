<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Symfony\Component\Security\Core\Role\Role;

/**
 * Interface of roleable model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface RoleableInterface
{
    /**
     * Check if the role exist.
     *
     * @param string $role The role name
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Set the roles.
     *
     * This overwrites any previous roles.
     *
     * @param string[] $roles The roles
     *
     * @return self
     */
    public function setRoles(array $roles);

    /**
     * Add a role.
     *
     * @param string $role The role name
     *
     * @return self
     */
    public function addRole($role);

    /**
     * Remove a role.
     *
     * @param string $role The role name
     *
     * @return self
     */
    public function removeRole($role);

    /**
     * Get the roles.
     *
     * @return Role[]|string[] The user roles
     */
    public function getRoles();
}
