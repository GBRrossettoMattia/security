<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Fxp\Component\Cache\Adapter\AdapterInterface;
use Fxp\Component\Security\Identity\CacheSecurityIdentityManagerInterface;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Invalidate the role hierarchy cache when users, roles or groups is inserted,
 * updated or deleted.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleHierarchyListener implements EventSubscriber
{
    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sim;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected $cache;

    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface    $sim     The security identity manager
     * @param CacheItemPoolInterface|null         $cache   The cache
     * @param OrganizationalContextInterface|null $context The organizational context
     */
    public function __construct(SecurityIdentityManagerInterface $sim,
                                CacheItemPoolInterface $cache = null,
                                OrganizationalContextInterface $context = null)
    {
        $this->sim = $sim;
        $this->cache = $cache;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $collection = $this->getAllCollections($uow);
        $invalidates = [];

        // check all scheduled insertions
        foreach ($collection as $object) {
            $invalidate = $this->invalidateCache($uow, $object);

            if (is_string($invalidate)) {
                $invalidates[] = $invalidate;
            }
        }

        $this->flushCache(array_unique($invalidates));
    }

    /**
     * Flush the cache.
     *
     * @param array $invalidates The prefix must be invalidated
     */
    protected function flushCache(array $invalidates)
    {
        if (count($invalidates) > 0) {
            if ($this->cache instanceof AdapterInterface && null !== $this->context) {
                $this->cache->clearByPrefixes($invalidates);
            } elseif (null !== $this->cache) {
                $this->cache->clear();
            }

            if ($this->sim instanceof CacheSecurityIdentityManagerInterface) {
                $this->sim->invalidateCache();
            }
        }
    }

    /**
     * Get the merged collection of all scheduled collections.
     *
     * @param UnitOfWork $uow The unit of work
     *
     * @return array
     */
    protected function getAllCollections(UnitOfWork $uow)
    {
        return array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions()
        );
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param UnitOfWork $uow    The unit of work
     * @param object     $object The object
     *
     * @return string|false
     */
    protected function invalidateCache(UnitOfWork $uow, $object)
    {
        if ($this->isCacheableObject($object)) {
            return $this->invalidateCacheableObject($uow, $object);
        } elseif ($object instanceof PersistentCollection && $this->isRequireAssociation($object->getMapping())) {
            return $this->getPrefix($object->getOwner());
        }

        return false;
    }

    /**
     * Check if the object cache must be invalidated.
     *
     * @param UnitOfWork $uow    The unit of work
     * @param object     $object The object
     *
     * @return bool|string
     */
    private function invalidateCacheableObject(UnitOfWork $uow, $object)
    {
        $fields = array_keys($uow->getEntityChangeSet($object));
        $checkFields = ['roles'];

        if ($object instanceof RoleHierarchicalInterface || $object instanceof OrganizationUserInterface) {
            $checkFields = array_merge($checkFields, ['name']);
        }

        foreach ($fields as $field) {
            if (in_array($field, $checkFields)) {
                return $this->getPrefix($object);
            }
        }

        return false;
    }

    /**
     * Check if the object is cacheable or not.
     *
     * @param object $object The object
     *
     * @return bool
     */
    protected function isCacheableObject($object)
    {
        return $object instanceof UserInterface || $object instanceof RoleHierarchicalInterface || $object instanceof GroupInterface || $object instanceof OrganizationUserInterface;
    }

    /**
     * Check if the association must be flush the cache.
     *
     * @param array $mapping The mapping
     *
     * @return bool
     */
    protected function isRequireAssociation(array $mapping)
    {
        $ref = new \ReflectionClass($mapping['sourceEntity']);

        if (in_array(RoleHierarchicalInterface::class, $ref->getInterfaceNames())
                && 'children' === $mapping['fieldName']) {
            return true;
        } elseif (in_array(GroupableInterface::class, $ref->getInterfaceNames())
                && 'groups' === $mapping['fieldName']) {
            return true;
        }

        return false;
    }

    /**
     * Get the cache prefix key.
     *
     * @param object $object
     *
     * @return string
     */
    protected function getPrefix($object)
    {
        $id = 'user';

        if (method_exists($object, 'getOrganization')) {
            $org = $object->getOrganization();

            if ($org instanceof OrganizationInterface) {
                $id = (string) $org->getId();
            }
        }

        return $id.'__';
    }
}
