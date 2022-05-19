<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

use Fxp\Component\Security\Exception\UnexpectedTypeException;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Identity\SubjectUtils;

/**
 * Permission utils.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class PermissionUtils
{
    /**
     * Get the action for the map of permissions.
     *
     * @param string|null $action  The action
     * @param string      $default The default value
     *
     * @return string
     */
    public static function getMapAction($action = null, $default = '_global')
    {
        return null !== $action
            ? $action
            : $default;
    }

    /**
     * Get the subject identity and field.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string|null $subject  The subject instance or classname
     * @param bool                                                  $optional Check if the subject id optional
     *
     * @return array
     */
    public static function getSubjectAndField($subject, $optional = false)
    {
        if ($subject instanceof FieldVote) {
            $field = $subject->getField();
            $subject = $subject->getSubject();
        } else {
            if (null === $subject && !$optional) {
                throw new UnexpectedTypeException($subject, 'FieldVote|SubjectIdentityInterface|object|string');
            }

            $field = null;
            $subject = null !== $subject
                ? SubjectUtils::getSubjectIdentity($subject)
                : null;
        }

        return [$subject, $field];
    }

    /**
     * Get the class and field.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string|null $subject  The subject instance or classname
     * @param bool                                                  $optional Check if the subject id optional
     *
     * @return array
     */
    public static function getClassAndField($subject, $optional = false)
    {
        /* @var SubjectIdentityInterface|null $subject */
        list($subject, $field) = static::getSubjectAndField($subject, $optional);

        $class = null !== $subject
            ? $subject->getType()
            : null;

        return [$class, $field];
    }
}
