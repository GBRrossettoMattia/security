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
 * Interface for role hierarchical.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface RoleHierarchicalInterface extends RoleInterface
{
    /**
     * Add a parent on the current role.
     *
     * @param RoleHierarchicalInterface $role
     *
     * @return self
     */
    public function addParent(self $role);

    /**
     * Remove a parent on the current role.
     *
     * @param RoleHierarchicalInterface $parent
     *
     * @return self
     */
    public function removeParent(self $parent);

    /**
     * Gets all parent.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getParents();

    /**
     * Gets all parent names.
     *
     * @return array
     */
    public function getParentNames();

    /**
     * Check if role has parent.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParent($name);

    /**
     * Add a child on the current role.
     *
     * @param RoleHierarchicalInterface $role
     *
     * @return self
     */
    public function addChild(self $role);

    /**
     * Remove a child on the current role.
     *
     * @param RoleHierarchicalInterface $child
     *
     * @return self
     */
    public function removeChild(self $child);

    /**
     * Gets all children.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getChildren();

    /**
     * Gets all children names.
     *
     * @return array
     */
    public function getChildrenNames();

    /**
     * Check if role has child.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasChild($name);
}
