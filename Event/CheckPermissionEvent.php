<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The check permission event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CheckPermissionEvent extends Event
{
    /**
     * @var SecurityIdentityInterface[]
     */
    protected $sids;

    /**
     * @var array
     */
    protected $permissionMap;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var SubjectIdentityInterface|null
     */
    protected $subject;

    /**
     * @var string|null
     */
    protected $field;

    /**
     * @var bool|null
     */
    protected $granted;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface[]   $sids          The security identities
     * @param array                         $permissionMap The map of permissions
     * @param string                        $operation     The operation
     * @param SubjectIdentityInterface|null $subject       The subject
     * @param string|null                   $field         The field of subject
     */
    public function __construct(array $sids,
                                array $permissionMap,
                                $operation,
                                $subject = null,
                                $field = null)
    {
        $this->sids = $sids;
        $this->permissionMap = $permissionMap;
        $this->operation = $operation;
        $this->subject = $subject;
        $this->field = $field;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->sids;
    }

    /**
     * Get the map of permissions.
     *
     * @return array
     */
    public function getPermissionMap()
    {
        return $this->permissionMap;
    }

    /**
     * Get the operation.
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Get the subject.
     *
     * @return SubjectIdentityInterface|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the field.
     *
     * @return string|null
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Define the granted value.
     *
     * @param bool|null $granted The granted value
     *
     * @return self
     */
    public function setGranted($granted)
    {
        $this->granted = $granted;

        return $this;
    }

    /**
     * Check if the permission is granted or not.
     *
     * @return bool|null
     */
    public function isGranted()
    {
        return $this->granted;
    }
}
