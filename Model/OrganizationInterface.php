<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Organization interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface OrganizationInterface
{
    /**
     * Get the id of model.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Set the name.
     *
     * @param string $name The name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the user of organization.
     *
     * @param UserInterface|null $user The user of organization
     *
     * @return self
     */
    public function setUser($user);

    /**
     * Get the user of organization.
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Check if the organization is a dedicated organization for the user.
     *
     * @return bool
     */
    public function isUserOrganization();

    /**
     * Get the users of organization.
     *
     * @return Collection|OrganizationUserInterface[]
     */
    public function getOrganizationUsers();

    /**
     * Get the usernames of organization.
     *
     * @return string[]
     */
    public function getOrganizationUserNames();

    /**
     * Check the presence of username in organization.
     *
     * @param string $username The username
     *
     * @return bool
     */
    public function hasOrganizationUser($username);

    /**
     * Add a organization user in organization.
     *
     * @param OrganizationUserInterface $organizationUser The organization user
     *
     * @return self
     */
    public function addOrganizationUser(OrganizationUserInterface $organizationUser);

    /**
     * Remove a organization user in organization.
     *
     * @param OrganizationUserInterface $organizationUser The organization user
     *
     * @return self
     */
    public function removeOrganizationUser(OrganizationUserInterface $organizationUser);

    /**
     * @return string
     */
    public function __toString();
}
