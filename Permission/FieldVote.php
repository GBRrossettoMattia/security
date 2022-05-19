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

use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Identity\SubjectUtils;

/**
 * Field vote.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FieldVote
{
    /**
     * @var SubjectIdentityInterface
     */
    private $subject;

    /**
     * @var string
     */
    private $field;

    /**
     * Constructor.
     *
     * @param SubjectIdentityInterface|object|string $subject The subject instance or classname
     * @param string                                 $field   The field name
     */
    public function __construct($subject, $field)
    {
        $this->subject = SubjectUtils::getSubjectIdentity($subject);
        $this->field = $field;
    }

    /**
     * Get the subject.
     *
     * @return SubjectIdentityInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
