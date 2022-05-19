<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Identity;

/**
 * This interface provides an additional level of indirection,
 * so that we can work with abstracted versions of security objects.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SecurityIdentityInterface
{
    /**
     * Get the identity type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the identifier.
     * Typically, the name of subject.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * This method is used to compare two security identities in order to
     * not rely on referential equality.
     *
     * @param SecurityIdentityInterface $identity
     */
    public function equals(self $identity);
}
