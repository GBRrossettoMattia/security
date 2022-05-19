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

use Fxp\Component\Security\Model\UserInterface;

/**
 * Interface of add dependency entity with an optional user.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface OwnerableOptionalInterface
{
    /**
     * Set the owner.
     *
     * @param UserInterface|null $user The organization
     *
     * @return self
     */
    public function setOwner($user);

    /**
     * Get the owner.
     *
     * @return UserInterface|null
     */
    public function getOwner();

    /**
     * Get the owner id.
     *
     * @return int|string|null
     */
    public function getOwnerId();
}
