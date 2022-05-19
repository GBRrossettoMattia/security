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

use Doctrine\Common\Collections\Collection;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationInterface;

/**
 * Trait of groups in organization model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface OrganizationGroupsInterface extends OrganizationInterface
{
    /**
     * Get the groups of organization.
     *
     * @return Collection
     */
    public function getOrganizationGroups();

    /**
     * Get the group names of organization.
     *
     * @return string[]
     */
    public function getOrganizationGroupNames();

    /**
     * Check the presence of group in organization.
     *
     * @param string $group The group name
     *
     * @return bool
     */
    public function hasOrganizationGroup($group);

    /**
     * Add a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function addOrganizationGroup(GroupInterface $group);

    /**
     * Remove a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function removeOrganizationGroup(GroupInterface $group);
}
