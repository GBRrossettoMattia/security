<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Role;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Fxp\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Fxp\Component\Security\Event\PostReachableRoleEvent;
use Fxp\Component\Security\Event\PreReachableRoleEvent;
use Fxp\Component\Security\Exception\SecurityException;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\ReachableRoleEvents;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    /**
     * @var ManagerRegistryInterface
     */
    private $registry;

    /**
     * @var string
     */
    private $roleClassname;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param array                       $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface    $registry      The doctrine registry
     * @param string                      $roleClassname The classname of role
     * @param CacheItemPoolInterface|null $cache         The cache
     */
    public function __construct(array $hierarchy,
                                ManagerRegistryInterface $registry,
                                $roleClassname,
                                CacheItemPoolInterface $cache = null)
    {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cacheExec = [];
        $this->cache = $cache;
    }

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param Role[] $roles An array of role instances
     *
     * @return Role[] An array of role instances
     *
     * @throws SecurityException When the role class is not an instance of '\Symfony\Component\Security\Core\Role\Role'
     */
    public function getReachableRoles(array $roles)
    {
        return $this->doGetReachableRoles($roles);
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param Role[] $roles  An array of role instances
     * @param string $suffix The role name suffix
     *
     * @return Role[] An array of role instances
     *
     * @throws SecurityException When the role class is not an instance of '\Symfony\Component\Security\Core\Role\Role'
     */
    public function doGetReachableRoles(array $roles, $suffix = '')
    {
        if (0 === count($roles)) {
            return $roles;
        }

        $item = null;
        $roles = $this->formatRoles($roles);
        $id = $this->getUniqueId(array_keys($roles));

        if (null !== ($reachableRoles = $this->getCachedReachableRoles($id, $item))) {
            return $reachableRoles;
        }

        // build hierarchy
        /* @var Role[] $reachableRoles */
        $reachableRoles = parent::getReachableRoles(array_values($roles));
        $isPermEnabled = true;

        if (null !== $this->eventDispatcher) {
            $event = new PreReachableRoleEvent($reachableRoles);
            $this->eventDispatcher->dispatch(ReachableRoleEvents::PRE, $event);
            $reachableRoles = $event->getReachableRoles();
            $isPermEnabled = $event->isPermissionEnabled();
        }

        return $this->getAllRoles($reachableRoles, $roles, $id, $item, $isPermEnabled, $suffix);
    }

    /**
     * Get the unique id.
     *
     * @param array $roleNames The role names
     *
     * @return string
     */
    protected function getUniqueId(array $roleNames)
    {
        return sha1(implode('|', $roleNames));
    }

    /**
     * Format the role name.
     *
     * @param Role|RoleInterface|string $role The role
     *
     * @return array The map of role name and role instance
     */
    protected function formatRoleName($role)
    {
        $name = $role instanceof Role ? $role->getRole() : (string) $role;

        return [$name, new Role($name)];
    }

    /**
     * Build the suffix of role.
     *
     * @param Role|null $role The role
     *
     * @return string
     */
    protected function buildRoleSuffix($role)
    {
        return '';
    }

    /**
     * Clean the role names.
     *
     * @param string[] $roles The role names
     *
     * @return string[]
     */
    protected function cleanRoleNames(array $roles)
    {
        return $roles;
    }

    /**
     * Format the cleaned role name.
     *
     * @param string $name The role name
     *
     * @return string
     */
    protected function formatCleanedRoleName($name)
    {
        return $name;
    }

    /**
     * Get the reachable roles in cache if available.
     *
     * @param string $id   The cache id
     * @param null   $item The cache item variable passed by reference
     *
     * @return Role[]|null
     */
    private function getCachedReachableRoles($id, &$item)
    {
        $roles = null;

        // find the hierarchy in execution cache
        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        // find the hierarchy in cache
        if (null !== $this->cache) {
            $item = $this->cache->getItem($id);
            $reachableRoles = $item->get();

            if ($item->isHit() && null !== $reachableRoles) {
                $roles = $reachableRoles;
            }
        }

        return $roles;
    }

    /**
     * Get all roles.
     *
     * @param Role[]                  $reachableRoles The reachable roles
     * @param Role[]                  $roles          The roles
     * @param string                  $id             The cache item id
     * @param CacheItemInterface|null $item           The cache item
     * @param bool                    $isPermEnabled  Check if the permission manager is enabled
     * @param string                  $suffix         The role name suffix
     *
     * @return Role[]
     */
    private function getAllRoles(array $reachableRoles, array $roles, $id, $item, $isPermEnabled, $suffix = '')
    {
        $reachableRoles = $this->findRecords($reachableRoles, $roles);
        $reachableRoles = $this->getCleanedRoles($reachableRoles, $suffix);

        // insert in cache
        if (null !== $this->cache && $item instanceof CacheItemInterface) {
            $item->set($reachableRoles);
            $this->cache->save($item);
        }

        $this->cacheExec[$id] = $reachableRoles;

        if (null !== $this->eventDispatcher) {
            $event = new PostReachableRoleEvent($reachableRoles, $isPermEnabled);
            $this->eventDispatcher->dispatch(ReachableRoleEvents::POST, $event);
            $reachableRoles = $event->getReachableRoles();
        }

        return $reachableRoles;
    }

    /**
     * Format the roles.
     *
     * @param Role[] $roles The roles
     *
     * @return Role[]
     *
     * @throws SecurityException When the role is not a string or an instance of RoleInterface
     */
    private function formatRoles(array $roles)
    {
        $nRoles = [];

        foreach ($roles as $role) {
            if (!is_string($role) && !($role instanceof Role)) {
                throw new SecurityException(sprintf('The Role class must be an instance of "%s"', Role::class));
            }

            list($name, $role) = $this->formatRoleName($role);
            $nRoles[$name] = $role;
        }

        return $nRoles;
    }

    /**
     * Find the roles in database.
     *
     * @param Role[] $reachableRoles The reachable roles
     * @param Role[] $roles          The map of role names and role instances
     *
     * @return Role[]
     */
    private function findRecords(array $reachableRoles, array $roles)
    {
        $recordRoles = [];
        $om = $this->registry->getManagerForClass($this->roleClassname);
        $repo = $om->getRepository($this->roleClassname);

        $filters = SqlFilterUtil::findFilters($om, [], true);
        SqlFilterUtil::disableFilters($om, $filters);

        if (count($roles) > 0) {
            $recordRoles = $repo->findBy(['name' => $this->cleanRoleNames(array_keys($roles))]);
        }

        /* @var RoleHierarchicalInterface $eRole */
        foreach ($recordRoles as $eRole) {
            $suffix = $this->buildRoleSuffix(isset($roles[$eRole->getRole()]) ? $roles[$eRole->getRole()] : null);
            $reachableRoles = array_merge($reachableRoles, $this->doGetReachableRoles($eRole->getChildren()->toArray(), $suffix));
        }

        SqlFilterUtil::enableFilters($om, $filters);

        return $reachableRoles;
    }

    /**
     * Cleaning the double roles.
     *
     * @param Role[] $reachableRoles The reachable roles
     * @param string $suffix         The role name suffix
     *
     * @return Role[]
     */
    private function getCleanedRoles(array $reachableRoles, $suffix = '')
    {
        $existingRoles = [];
        $finalRoles = [];

        foreach ($reachableRoles as $role) {
            $name = $this->formatCleanedRoleName($role->getRole());

            if (!in_array($name, $existingRoles)) {
                $rSuffix = 'ROLE_USER' !== $name ? $suffix : '';
                $role = new Role($role->getRole().$rSuffix);
                $existingRoles[] = $name;
                $finalRoles[] = $role;
            }
        }

        return $finalRoles;
    }
}
