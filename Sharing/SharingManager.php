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

use Fxp\Component\Security\Exception\InvalidSubjectIdentityException;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Model\SharingInterface;
use Fxp\Component\Security\Permission\PermissionUtils;

/**
 * Sharing manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingManager extends AbstractSharingManager implements SharingManagerInterface
{
    /**
     * @var array
     */
    protected $cacheSharing = [];

    /**
     * @var array
     */
    protected $cacheRoleSharing = [];

    /**
     * @var array
     */
    protected $cacheSubjectSharing = [];

    /**
     * {@inheritdoc}
     */
    public function isGranted($operation, $subject = null, $field = null)
    {
        $this->preloadPermissions([$subject]);
        $this->preloadRolePermissions([$subject]);

        $sharingId = null !== $subject ? SharingUtils::getCacheId($subject) : null;
        $classAction = PermissionUtils::getMapAction($subject instanceof SubjectIdentityInterface ? $subject->getType() : null);
        $fieldAction = PermissionUtils::getMapAction($field);

        return isset($this->cacheSharing[$sharingId][$classAction][$fieldAction][$operation])
            || $this->isSharingGranted($operation, $subject, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function preloadPermissions(array $objects)
    {
        $subjects = $this->buildSubjects($objects);
        $entries = $this->buildSharingEntries($subjects);

        foreach ($subjects as $id => $subject) {
            if (isset($entries[$id])) {
                foreach ($entries[$id] as $entrySharing) {
                    $operations = isset($this->cacheSubjectSharing[$id]['operations'])
                        ? $this->cacheSubjectSharing[$id]['operations']
                        : [];

                    $this->cacheSubjectSharing[$id]['sharings'][] = $entrySharing;
                    $this->cacheSubjectSharing[$id]['operations'] = array_unique(array_merge($operations,
                        SharingUtils::buildOperations($entrySharing)
                    ));
                }
            }
        }

        $this->preloadPermissionsOfSharingRoles($objects);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function preloadRolePermissions(array $subjects)
    {
        $roles = [];
        $idSubjects = [];

        foreach ($subjects as $subject) {
            $subjectId = SharingUtils::getCacheId($subject);
            $idSubjects[$subjectId] = $subject;

            if (!array_key_exists($subjectId, $this->cacheSharing)
                    && isset($this->cacheRoleSharing[$subjectId])) {
                $roles = array_merge($roles, $this->cacheRoleSharing[$subjectId]);
                $this->cacheSharing[$subjectId] = [];
            }
        }

        $roles = array_unique($roles);

        if (!empty($roles)) {
            $this->doLoadSharingPermissions($idSubjects, $roles);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetPreloadPermissions(array $objects)
    {
        foreach ($objects as $object) {
            try {
                $subject = SubjectIdentity::fromObject($object);
                $id = SharingUtils::getCacheId($subject);
                unset($this->cacheSharing[$id]);
                unset($this->cacheRoleSharing[$id]);
                unset($this->cacheSubjectSharing[$id]);
            } catch (InvalidSubjectIdentityException $e) {
                // do nothing
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cacheSharing = [];
        $this->cacheRoleSharing = [];
        $this->cacheSubjectSharing = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function renameIdentity($type, $oldName, $newName)
    {
        $this->provider->renameIdentity($type, $oldName, $newName);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIdentity($type, $name)
    {
        $this->provider->deleteIdentity($type, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deletes(array $ids)
    {
        $this->provider->deletes($ids);

        return $this;
    }

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param SubjectIdentityInterface|null $subject   The subject
     * @param string|null                   $field     The field of subject
     *
     * @return bool
     */
    private function isSharingGranted($operation, $subject = null, $field = null)
    {
        if (null !== $subject && null === $field) {
            $id = SharingUtils::getCacheId($subject);

            return isset($this->cacheSubjectSharing[$id]['operations'])
            && in_array($operation, $this->cacheSubjectSharing[$id]['operations']);
        }

        return false;
    }

    /**
     * Convert the objects into subject identities.
     *
     * @param object[] $objects The objects
     *
     * @return SubjectIdentityInterface[]
     */
    private function buildSubjects(array $objects)
    {
        $subjects = [];

        foreach ($objects as $object) {
            $subject = SubjectIdentity::fromObject($object);
            $id = SharingUtils::getCacheId($subject);

            if ($this->hasSubjectConfig($subject->getType())
                    && $this->hasIdentityPermissible()
                    && $this->hasSharingVisibility($subject)
                    && !array_key_exists($id, $this->cacheSubjectSharing)) {
                $subjects[$id] = $subject;
                $this->cacheSubjectSharing[$id] = false;
            }
        }

        return $subjects;
    }

    /**
     * Build the sharing entries with the subject identities.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     *
     * @return array The map of cache id and sharing instance
     */
    private function buildSharingEntries(array $subjects)
    {
        $entries = [];

        if (!empty($subjects)) {
            $res = $this->provider->getSharingEntries(array_values($subjects));

            foreach ($res as $sharing) {
                $id = SharingUtils::getSharingCacheId($sharing);
                $entries[$id][] = $sharing;
            }
        }

        return $entries;
    }

    /**
     * Preload permissions of sharing roles.
     *
     * @param object[] $objects The objects
     */
    private function preloadPermissionsOfSharingRoles(array $objects)
    {
        if (!$this->hasIdentityRoleable()) {
            return;
        }

        $subjects = $this->buildMapSubject($objects);

        foreach ($subjects as $id => $subject) {
            if (!isset($this->cacheRoleSharing[$id])
                    && isset($this->cacheSubjectSharing[$id]['sharings'])) {
                $this->buildCacheRoleSharing($this->cacheSubjectSharing[$id]['sharings'], $id);
            }
        }
    }

    /**
     * Build the map of subjects with cache ids.
     *
     * @param object[] $objects The objects
     *
     * @return array The map of cache id and subject
     */
    private function buildMapSubject(array $objects)
    {
        $subjects = [];

        foreach ($objects as $object) {
            $subject = SubjectIdentity::fromObject($object);
            $id = SharingUtils::getCacheId($subject);
            $subjects[$id] = $subject;
        }

        return $subjects;
    }

    /**
     * Build the cache role sharing.
     *
     * @param SharingInterface[] $sharings The sharing instances
     * @param string             $id       The cache id
     */
    private function buildCacheRoleSharing(array $sharings, $id)
    {
        $this->cacheRoleSharing[$id] = [];

        foreach ($sharings as $sharing) {
            foreach ($sharing->getRoles() as $role) {
                $this->cacheRoleSharing[$id][] = $role;
            }
        }

        $this->cacheRoleSharing[$id] = array_unique($this->cacheRoleSharing[$id]);
    }

    /**
     * Action to load the permissions of sharing roles.
     *
     * @param array    $idSubjects The map of subject id and subject
     * @param string[] $roles      The roles
     */
    private function doLoadSharingPermissions(array $idSubjects, array $roles)
    {
        /* @var RoleInterface[] $mapRoles */
        $mapRoles = [];
        $cRoles = $this->provider->getPermissionRoles($roles);

        foreach ($cRoles as $role) {
            $mapRoles[$role->getRole()] = $role;
        }

        /* @var SubjectIdentityInterface $subject */
        foreach ($idSubjects as $id => $subject) {
            foreach ($this->cacheRoleSharing[$id] as $role) {
                if (isset($mapRoles[$role])) {
                    $cRole = $mapRoles[$role];

                    foreach ($cRole->getPermissions() as $perm) {
                        $class = $subject->getType();
                        $field = PermissionUtils::getMapAction($perm->getField());
                        $this->cacheSharing[$id][$class][$field][$perm->getOperation()] = true;
                    }
                }
            }
        }
    }
}
