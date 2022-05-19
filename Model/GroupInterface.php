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

use Fxp\Component\Security\Model\Traits\RoleableInterface;

/**
 * User interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface GroupInterface extends RoleableInterface
{
    /**
     * Get the group name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the group name used by security.
     *
     * @return string
     */
    public function getGroup();
}
