<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

/**
 * Sharing identity config Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SharingIdentityConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the alias.
     *
     * @return string
     */
    public function getAlias();

    /**
     * Check if the identity can be use the roles.
     *
     * @return bool
     */
    public function isRoleable();

    /**
     * Check if the identity can be use the permissions.
     *
     * @return bool
     */
    public function isPermissible();
}
