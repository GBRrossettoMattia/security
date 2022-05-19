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

/**
 * This is the domain class for the Organization User object.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class OrganizationUser implements OrganizationUserInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    /**
     * @var OrganizationInterface
     */
    protected $organization;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param OrganizationInterface $organization The organization
     * @param UserInterface         $user         The user
     */
    public function __construct(OrganizationInterface $organization, UserInterface $user)
    {
        $this->organization = $organization;
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrganization(OrganizationInterface $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->organization->getName().':'.$this->user->getUsername();
    }
}
