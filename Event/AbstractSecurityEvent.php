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

use Symfony\Component\EventDispatcher\Event;

/**
 * The abstract security event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSecurityEvent extends Event
{
    /**
     * @var bool
     */
    protected $permissionEnabled = true;

    /**
     * Check if the permission manager is enabled.
     *
     * @return bool
     */
    public function isPermissionEnabled()
    {
        return $this->permissionEnabled;
    }
}
