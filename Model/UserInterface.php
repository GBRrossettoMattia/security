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

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface UserInterface extends AdvancedUserInterface
{
    /**
     * Get id.
     *
     * @return int|string|null
     */
    public function getId();
}
