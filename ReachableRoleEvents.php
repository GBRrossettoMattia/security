<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class ReachableRoleEvents
{
    /**
     * The ReachableRoleEvents::RETRIEVAL_PRE event occurs before the research of all
     * children roles.
     *
     * @Event("Fxp\Component\Security\Event\PreReachableRoleEvent")
     *
     * @var string
     */
    const PRE = 'fxp_security.reachable_roles.pre';

    /**
     * The ReachableRoleEvents::RETRIEVAL_POST event occurs after the research of all
     * children roles.
     *
     * @Event("Fxp\Component\Security\Event\PostReachableRoleEvent")
     *
     * @var string
     */
    const POST = 'fxp_security.reachable_roles.post';
}
