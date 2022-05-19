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

use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Exception\InvalidSubjectIdentityException;
use Fxp\Component\Security\Exception\PermissionConfigNotFoundException;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Model\PermissionChecking;
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalInterface;
use Fxp\Component\Security\PermissionContexts;
use Fxp\Component\Security\Sharing\SharingManagerInterface;

/**
 * Abstract permission manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractPermissionManager implements PermissionManagerInterface
{
    /**
     * @var array|PermissionConfigInterface[]
     */
    protected $configs;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var SharingManagerInterface|null
     */
    protected $sharingManager;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * Constructor.
     *
     * @param SharingManagerInterface|null $sharingManager The sharing manager
     * @param PermissionConfigInterface[]  $configs        The permission configs
     */
    public function __construct(SharingManagerInterface $sharingManager = null,
                                array $configs = [])
    {
        $this->sharingManager = $sharingManager;
        $this->configs = [];

        foreach ($configs as $config) {
            $this->addConfig($config);
        }
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
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        if (null !== $this->sharingManager) {
            $this->sharingManager->setEnabled($enabled);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfig(PermissionConfigInterface $config)
    {
        $this->configs[$config->getType()] = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig($class)
    {
        return isset($this->configs[ClassUtils::getRealClass($class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($class)
    {
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasConfig($class)) {
            throw new PermissionConfigNotFoundException($class);
        }

        return $this->configs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function isManaged($subject)
    {
        try {
            /* @var SubjectIdentityInterface $subject */
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject);

            return $this->doIsManaged($subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldManaged($subject, $field)
    {
        return $this->isManaged(new FieldVote($subject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $sids, $permissions, $subject = null)
    {
        try {
            /* @var SubjectIdentityInterface|null $subject */
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);
            list($permissions, $subject, $field) = $this->getMasterPermissions((array) $permissions,
                $subject, $field);

            if (null !== $subject && !$this->doIsManaged($subject, $field)) {
                return true;
            }

            return $this->doIsGranted($sids, $this->getRealPermissions($permissions, $subject, $field), $subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(array $sids, $permissions, $subject, $field)
    {
        return $this->isGranted($sids, $permissions, new FieldVote($subject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePermissions(RoleInterface $role, $subject = null)
    {
        return $this->doGetRolePermissions($role, $subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleFieldPermissions(RoleInterface $role, $subject, $field)
    {
        return $this->getRolePermissions($role, new FieldVote($subject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function preloadPermissions(array $objects)
    {
        if (null !== $this->sharingManager) {
            $this->sharingManager->preloadPermissions($objects);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPreloadPermissions(array $objects)
    {
        if (null !== $this->sharingManager) {
            $this->sharingManager->resetPreloadPermissions($objects);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cache = [];

        if (null !== $this->sharingManager) {
            $this->sharingManager->clear();
        }

        return $this;
    }

    /**
     * Build the permission contexts for the role.
     *
     * @param RoleInterface $role The role
     *
     * @return string[]|null
     */
    protected function buildContexts(RoleInterface $role)
    {
        $contexts = null;

        if ($role instanceof OrganizationalInterface) {
            $contexts = [null !== $role->getOrganization() ? PermissionContexts::ORGANIZATION_ROLE : PermissionContexts::ROLE];
        }

        return $contexts;
    }

    /**
     * Get the real permissions.
     *
     * @param string[]                      $permissions The permissions
     * @param SubjectIdentityInterface|null $subject     The subject identity
     * @param string|null                   $field       The field name
     *
     * @return string[]
     */
    private function getRealPermissions(array $permissions, $subject = null, $field = null)
    {
        if (null !== $subject && $this->hasConfig($subject->getType())) {
            $config = $this->getConfig($subject->getType());

            if (null !== $field && $config->hasField($field)) {
                $config = $config->getField($field);
            }

            foreach ($permissions as $key => &$permission) {
                $permission = $config->getMappingPermission($permission);
            }
        }

        return $permissions;
    }

    /**
     * Get the master subject and permissions.
     *
     * @param string[]                      $permissions The permissions
     * @param SubjectIdentityInterface|null $subject     The subject identity
     * @param string|null                   $field       The field name
     *
     * @return array
     */
    private function getMasterPermissions(array $permissions, $subject, $field)
    {
        $master = $this->getMaster($subject);

        if (null !== $subject && null !== $master && $subject !== $master) {
            if (null !== $field) {
                $permissions = $this->buildMasterFieldPermissions($subject, $permissions);
            }

            $subject = $master;
            $field = null;
        }

        return [$permissions, $subject, $field];
    }

    /**
     * Build the master permissions.
     *
     * @param SubjectIdentityInterface $subject     The subject identity
     * @param string[]                 $permissions The permissions
     *
     * @return string[]
     */
    private function buildMasterFieldPermissions(SubjectIdentityInterface $subject, array $permissions)
    {
        if ($this->hasConfig($subject->getType())) {
            $map = $this->getConfig($subject->getType())->getMasterFieldMappingPermissions();

            foreach ($permissions as &$permission) {
                if (false !== $key = array_search($permission, $map)) {
                    $permission = $key;
                }
            }
        }

        return $permissions;
    }

    /**
     * Get the master subject.
     *
     * @param SubjectIdentityInterface|object|string|null $subject The subject instance or classname
     *
     * @return SubjectIdentityInterface|null
     */
    abstract protected function getMaster($subject);

    /**
     * Action to check if the subject is managed.
     *
     * @param SubjectIdentityInterface $subject The subject identity
     * @param string|null              $field   The field name
     *
     * @return bool
     */
    abstract protected function doIsManaged(SubjectIdentityInterface $subject, $field = null);

    /**
     * Action to determine whether access is granted.
     *
     * @param SecurityIdentityInterface[]   $sids        The security identities
     * @param string[]                      $permissions The required permissions
     * @param SubjectIdentityInterface|null $subject     The subject
     * @param string|null                   $field       The field of subject
     *
     * @return bool
     */
    abstract protected function doIsGranted(array $sids, array $permissions, $subject = null, $field = null);

    /**
     * Action to retrieve the permissions of role and subject.
     *
     * @param RoleInterface                                         $role    The role
     * @param SubjectIdentityInterface|FieldVote|object|string|null $subject The object or class name or field vote
     *
     * @return PermissionChecking[]
     */
    abstract protected function doGetRolePermissions(RoleInterface $role, $subject = null);
}
