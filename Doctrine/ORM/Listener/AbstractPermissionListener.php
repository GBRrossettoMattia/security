<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Listener;

/**
 * Abstract class for permission listeners.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractPermissionListener extends AbstractListener
{
    /**
     * @var array
     */
    protected $postResetPermissions = [];

    /**
     * Reset the preloaded permissions used for the insertions.
     */
    public function postFlush()
    {
        $this->getPermissionManager()->resetPreloadPermissions($this->postResetPermissions);
        $this->postResetPermissions = [];
    }
}
