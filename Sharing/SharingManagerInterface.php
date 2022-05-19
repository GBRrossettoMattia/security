<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

use Fxp\Component\Security\Identity\SubjectIdentityInterface;

/**
 * Sharing manager Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SharingManagerInterface
{
    /**
     * Check if sharing manager is enabled.
     *
     * If the sharing manager is disabled, all sharing visibilities are the value NONE.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Define if the sharing manager is enable or not.
     *
     * @param bool $enabled The value
     *
     * @return self
     */
    public function setEnabled($enabled);

    /**
     * Add the sharing subject config.
     *
     * @param SharingSubjectConfigInterface $config The sharing subject config
     *
     * @return self
     */
    public function addSubjectConfig(SharingSubjectConfigInterface $config);

    /**
     * Check if the sharing subject config is present.
     *
     * @param string $class The class name of sharing subject
     *
     * @return bool
     */
    public function hasSubjectConfig($class);

    /**
     * Get the sharing subject config.
     *
     * @param string $class The class name of sharing subject
     *
     * @return SharingSubjectConfigInterface
     */
    public function getSubjectConfig($class);

    /**
     * Get the sharing subject configs.
     *
     * @return SharingSubjectConfigInterface[]
     */
    public function getSubjectConfigs();

    /**
     * Check if the subject has sharing visibility of subject identity.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return bool
     */
    public function hasSharingVisibility(SubjectIdentityInterface $subject);

    /**
     * Get the sharing visibility of subject identity.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return string
     */
    public function getSharingVisibility(SubjectIdentityInterface $subject);

    /**
     * Add the sharing identity config.
     *
     * @param SharingIdentityConfigInterface $config The sharing identity config
     *
     * @return self
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config);

    /**
     * Check if the sharing identity config is present.
     *
     * @param string $class The class name of sharing identity
     *
     * @return bool
     */
    public function hasIdentityConfig($class);

    /**
     * Get the sharing identity config.
     *
     * @param string $class The class name of sharing identity
     *
     * @return SharingIdentityConfigInterface
     */
    public function getIdentityConfig($class);

    /**
     * Get the sharing identity configs.
     *
     * @return SharingIdentityConfigInterface[]
     */
    public function getIdentityConfigs();

    /**
     * Check if there is an identity config with the roleable option.
     *
     * @return bool
     */
    public function hasIdentityRoleable();

    /**
     * Check if there is an identity config with the permissible option.
     *
     * @return bool
     */
    public function hasIdentityPermissible();

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param SubjectIdentityInterface|null $subject   The subject
     * @param string|null                   $field     The field of subject
     *
     * @return bool
     */
    public function isGranted($operation, $subject = null, $field = null);

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
     */
    public function preloadPermissions(array $objects);

    /**
     * Preload the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    public function preloadRolePermissions(array $subjects);

    /**
     * Reset the preload permissions of objects.
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

    /**
     * Rename the identity of sharing.
     *
     * @param string $type    The identity type. Typically the PHP class name
     * @param string $oldName The old identity name
     * @param string $newName The new identity name
     *
     * @return self
     */
    public function renameIdentity($type, $oldName, $newName);

    /**
     * Delete the identity of sharing.
     *
     * @param string $type The identity type. Typically the PHP class name
     * @param string $name The identity name
     *
     * @return self
     */
    public function deleteIdentity($type, $name);

    /**
     * Delete the sharing entry with ids.
     *
     * @param array $ids The sharing ids
     *
     * @return self
     */
    public function deletes(array $ids);
}
