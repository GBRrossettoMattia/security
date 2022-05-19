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
final class PermissionEvents
{
    /**
     * The PRE_LOAD event occurs before the loading of the permissions.
     *
     * @Event("Fxp\Component\Security\Event\PreLoadPermissionsEvent")
     *
     * @var string
     */
    const PRE_LOAD = 'fxp_security.permission_manager.pre_load';

    /**
     * The POST_LOAD event occurs after the loading of the permissions.
     *
     * @Event("Fxp\Component\Security\Event\PostLoadPermissionsEvent")
     *
     * @var string
     */
    const POST_LOAD = 'fxp_security.permission_manager.post_load';

    /**
     * The CHECK_PERMISSION event occurs when the permission is checked.
     * You can override the result with this event.
     *
     * @Event("Fxp\Component\Security\Event\CheckPermissionEvent")
     *
     * @var string
     */
    const CHECK_PERMISSION = 'fxp_security.permission_manager.check_permission';
}
