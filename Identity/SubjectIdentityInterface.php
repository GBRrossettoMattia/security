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
 * Represents the identity of an individual subject object instance.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SubjectIdentityInterface
{
    /**
     * Get the type of the subject. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the unique identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get the instance of subject.
     *
     * @return object|null
     */
    public function getObject();

    /**
     * We specifically require this method so we can check for object equality
     * explicitly, and do not have to rely on referential equality instead.
     *
     * Though in most cases, both checks should result in the same outcome.
     *
     * Referential Equality: $subject1 === $subject2
     * Example for Subject Equality: $subject1->getId() === $subject2->getId()
     *
     * @param SubjectIdentityInterface $identity The subject identity
     *
     * @return bool
     */
    public function equals(self $identity);
}
