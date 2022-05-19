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

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The abstract load permissions event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractLoadPermissionsEvent extends Event
{
    /**
     * @var SecurityIdentityInterface[]
     */
    protected $sids;

    /**
     * @var string[]
     */
    protected $roles;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface[] $sids  The security identities
     * @param string[]                    $roles The role names
     */
    public function __construct(array $sids, array $roles)
    {
        $this->sids = $sids;
        $this->roles = $roles;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->sids;
    }

    /**
     * Get the roles.
     *
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
