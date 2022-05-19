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

use Fxp\Component\Security\Model\OrganizationUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Trait of roleable model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait RoleableTrait
{
    /**
     * @var string[]
     */
    protected $roles = [];

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true) && !in_array($role, ['ROLE_USER', 'ROLE_ORGANIZATION_USER'])) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        if ($this instanceof UserInterface && !in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        if ($this instanceof OrganizationUserInterface && !in_array('ROLE_ORGANIZATION_USER', $roles, true)) {
            $roles[] = 'ROLE_ORGANIZATION_USER';
        }

        return $roles;
    }
}
