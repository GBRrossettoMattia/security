<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Fxp\Component\Security\Model\OrganizationInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The event of set current organization by the organizational context.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SetCurrentOrganizationEvent extends Event
{
    /**
     * @var OrganizationInterface|false|null
     */
    protected $organization;

    /**
     * Constructor.
     *
     * @param OrganizationInterface|false|null $organization The current organization
     */
    public function __construct($organization)
    {
        $this->organization = $organization;
    }

    /**
     * Get the current organization.
     *
     * @return OrganizationInterface|false|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
