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

use Fxp\Component\Security\Event\Traits\ReachableRoleEventTrait;

/**
 * The pre reachable role event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PreReachableRoleEvent extends AbstractEditableSecurityEvent
{
    use ReachableRoleEventTrait;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Security\Core\Role\Role[] $reachableRoles The reachable roles
     */
    public function __construct(array $reachableRoles)
    {
        $this->reachableRoles = $reachableRoles;
    }
}
