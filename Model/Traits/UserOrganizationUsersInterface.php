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
use Fxp\Component\Security\Model\OrganizationUserInterface;

/**
 * Trait of organization users in user model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface UserOrganizationUsersInterface
{
    /**
     * Get the organizations associated with this user.
     *
     * @return Collection|OrganizationUserInterface[]
     */
    public function getUserOrganizations();

    /**
     * Get the names of organizations associated with this user.
     *
     * @return string[]
     */
    public function getUserOrganizationNames();

    /**
     * Check if the organization is associated with this user.
     *
     * @param string $name The name of organization
     *
     * @return bool
     */
    public function hasUserOrganization($name);

    /**
     * Get the associated organization with this user.
     *
     * @param string $name The name of organization
     *
     * @return OrganizationUserInterface|null
     */
    public function getUserOrganization($name);

    /**
     * Associate an organization with this user.
     *
     * @param OrganizationUserInterface $organizationUser The user organization
     *
     * @return self
     */
    public function addUserOrganization(OrganizationUserInterface $organizationUser);

    /**
     * Dissociate an organization with this user.
     *
     * @param OrganizationUserInterface $organizationUser The user organization
     *
     * @return self
     */
    public function removeUserOrganization(OrganizationUserInterface $organizationUser);
}
