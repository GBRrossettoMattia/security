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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * This is the domain class for the Permission object.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class Permission implements PermissionInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    /**
     * @var string[]
     */
    protected $contexts = [];

    /**
     * @var string|null
     */
    protected $class;

    /**
     * @var string|null
     */
    protected $field;

    /**
     * @var string|null
     */
    protected $operation;

    /**
     * @var Collection|RoleInterface[]|null
     */
    protected $roles;

    /**
     * @var Collection|SharingInterface[]|null
     */
    protected $sharingEntries;

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
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * {@inheritdoc}
     */
    public function setContexts(array $contexts)
    {
        $this->contexts = $contexts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles ?: $this->roles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingEntries()
    {
        return $this->sharingEntries ?: $this->sharingEntries = new ArrayCollection();
    }
}
