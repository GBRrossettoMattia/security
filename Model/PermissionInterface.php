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

use Doctrine\Common\Collections\Collection;

/**
 * Permission interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionInterface
{
    /**
     * Get the id.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Set the operation.
     *
     * @param string|null $operation The operation
     *
     * @return self
     */
    public function setOperation($operation);

    /**
     * Get the operation.
     *
     * @return string|null
     */
    public function getOperation();

    /**
     * Set the permission contexts.
     *
     * @param string[] $contexts The permission contexts
     *
     * @return self
     */
    public function setContexts(array $contexts);

    /**
     * Get the permission contexts.
     *
     * @return string[]
     */
    public function getContexts();

    /**
     * Set the classname.
     *
     * @param string|null $class The classname
     *
     * @return self
     */
    public function setClass($class);

    /**
     * Get the classname.
     *
     * @return string|null
     */
    public function getClass();

    /**
     * Set the field.
     *
     * @param string|null $field The field
     *
     * @return self
     */
    public function setField($field);

    /**
     * Get the field.
     *
     * @return string|null
     */
    public function getField();

    /**
     * @return Collection|RoleInterface[]
     */
    public function getRoles();
}
