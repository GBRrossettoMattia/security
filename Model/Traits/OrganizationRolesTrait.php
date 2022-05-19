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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fxp\Component\Security\Model\RoleInterface;

/**
 * Trait of roles in organization model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait OrganizationRolesTrait
{
    /**
     * @var Collection|null
     */
    protected $organizationRoles;

    /**
     * {@inheritdoc}
     */
    public function getOrganizationRoles()
    {
        return $this->organizationRoles ?: $this->organizationRoles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationRoleNames()
    {
        $names = [];
        foreach ($this->getOrganizationRoles() as $role) {
            $names[] = $role->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationRole($role)
    {
        return in_array($role, $this->getOrganizationRoleNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationRole(RoleInterface $role)
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->add($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationRole(RoleInterface $role)
    {
        if ($this->getOrganizationRoles()->contains($role)) {
            $this->getOrganizationRoles()->removeElement($role);
        }

        return $this;
    }

    /**
     * Check if the organization is a user organization or not.
     *
     * @return bool
     */
    abstract public function isUserOrganization();
}
