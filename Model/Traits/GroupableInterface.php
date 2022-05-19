<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Fxp\Component\Security\Model\GroupInterface;

/**
 * Groupable interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface GroupableInterface
{
    /**
     * Indicates whether the model belongs to the specified group or not.
     *
     * @param string $name The name of the group
     *
     * @return bool
     */
    public function hasGroup($name);

    /**
     * Gets the groups granted to the user.
     *
     * @return \Traversable|GroupInterface[]
     */
    public function getGroups();
}
