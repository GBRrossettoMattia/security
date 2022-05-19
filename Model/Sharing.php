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

use Fxp\Component\Security\Model\Traits\PermissionsTrait;
use Fxp\Component\Security\Model\Traits\RoleableTrait;

/**
 * Sharing model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class Sharing implements SharingInterface
{
    use PermissionsTrait;
    use RoleableTrait;

    /**
     * @var int|string|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $subjectClass;

    /**
     * @var int|string|null
     */
    protected $subjectId;

    /**
     * @var string|null
     */
    protected $identityClass;

    /**
     * @var int|string|null
     */
    protected $identityName;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var \DateTime|null
     */
    protected $startedAt;

    /**
     * @var \DateTime|null
     */
    protected $endedAt;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectClass($class)
    {
        $this->subjectClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectClass()
    {
        return $this->subjectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectId($id)
    {
        $this->subjectId = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityClass($class)
    {
        $this->identityClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityName($name)
    {
        $this->identityName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityName()
    {
        return $this->identityName;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt($date)
    {
        $this->startedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndedAt($date)
    {
        $this->endedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }
}
