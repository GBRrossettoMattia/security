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

use Fxp\Component\Security\Exception\PermissionConfigNotFoundException;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Model\PermissionChecking;
use Fxp\Component\Security\Model\RoleInterface;

/**
 * Permission manager Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionManagerInterface
{
    /**
     * Check if permission manager is disabled.
     *
     * If the permission manager is disabled, all asked authorizations will be
     * always accepted.
     *
     * If the permission manager is enabled, all asked authorizations will be accepted
     * depending on the permissions.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Define if the permission manager is enable or not.
     *
     * @param bool $enabled The value
     *
     * @return self
     */
    public function setEnabled($enabled);

    /**
     * Add the permission config.
     *
     * @param PermissionConfigInterface $config The permission config
     */
    public function addConfig(PermissionConfigInterface $config);

    /**
     * Check if the configuration of permission is present.
     *
     * @param string $class The class name
     *
     * @return bool
     */
    public function hasConfig($class);

    /**
     * Get the configuration of permission.
     *
     * @param string $class The class name
     *
     * @return PermissionConfigInterface
     *
     * @throws PermissionConfigNotFoundException When the configuration of permission is not found
     */
    public function getConfig($class);

    /**
     * Get the configurations of permissions.
     *
     * @return PermissionConfigInterface[]
     */
    public function getConfigs();

    /**
     * Check if the subject is managed.
     *
     * @param SubjectIdentityInterface|FieldVote|object|string $subject The object or class name
     *
     * @return bool
     */
    public function isManaged($subject);

    /**
     * Check if the field of subject is managed.
     *
     * @param SubjectIdentityInterface|object|string $subject The object or class name
     * @param string                                 $field   The field
     *
     * @return bool
     */
    public function isFieldManaged($subject, $field);

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]                           $sids        The security identities
     * @param string|string[]                                       $permissions The permissions
     * @param SubjectIdentityInterface|FieldVote|object|string|null $subject     The object or class name or field vote
     *
     * @return bool
     */
    public function isGranted(array $sids, $permissions, $subject = null);

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]            $sids        The security identities
     * @param string|string[]                        $permissions The permissions
     * @param SubjectIdentityInterface|object|string $subject     The object or class name
     * @param string                                 $field       The field
     *
     * @return bool
     */
    public function isFieldGranted(array $sids, $permissions, $subject, $field);

    /**
     * Get the permissions of the role and subject.
     *
     * @param RoleInterface                                         $role    The role
     * @param SubjectIdentityInterface|FieldVote|object|string|null $subject The object or class name or field vote
     *
     * @return PermissionChecking[]
     */
    public function getRolePermissions(RoleInterface $role, $subject = null);

    /**
     * Get the permissions of the role and subject field.
     *
     * @param RoleInterface                          $role    The role
     * @param SubjectIdentityInterface|object|string $subject The object or class name
     * @param string                                 $field   The field
     *
     * @return PermissionChecking[]
     */
    public function getRoleFieldPermissions(RoleInterface $role, $subject, $field);

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
     */
    public function preloadPermissions(array $objects);

    /**
     * Reset the preload permissions for specific objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
     */
    public function resetPreloadPermissions(array $objects);

    /**
     * Clear all permission caches.
     *
     * @return self
     */
    public function clear();
}
