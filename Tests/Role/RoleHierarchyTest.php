<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Role;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Role\RoleHierarchy;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleHierarchyTest extends TestCase
{
    /**
     * @var ManagerRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var string
     */
    protected $roleClassname;

    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repo;

    /**
     * @var FilterCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filters;

    /**
     * @var RoleHierarchy
     */
    protected $roleHierarchy;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder(ManagerRegistryInterface::class)->getMock();
        $this->roleClassname = MockRole::class;
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->repo = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->filters = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $hierarchy = [
            'ROLE_ADMIN' => [
                'ROLE_USER',
            ],
        ];
        $this->roleHierarchy = new RoleHierarchy($hierarchy, $this->registry, $this->roleClassname, $this->cache);

        $this->roleHierarchy->setEventDispatcher($this->eventDispatcher);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->roleClassname)
            ->willReturn($this->em);

        $this->em->expects($this->any())
            ->method('getRepository')
            ->with($this->roleClassname)
            ->willReturn($this->repo);

        $this->em->expects($this->any())
            ->method('getFilters')
            ->willReturn($this->filters);
    }

    public function testGetReachableRolesWithCustomRoles()
    {
        $roles = [
            new MockRole('ROLE_ADMIN'),
        ];
        $validRoles = [
            new Role('ROLE_ADMIN'),
            new Role('ROLE_USER'),
        ];

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)->getMock();

        $this->cache->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn(null);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(false);

        $this->eventDispatcher->expects($this->atLeastOnce())
            ->method('dispatch');

        $sqlFilters = [
            'test_filter' => $this->getMockForAbstractClass(SQLFilter::class, [], '', false),
        ];

        $this->filters->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn($sqlFilters);

        $this->filters->expects($this->once())
            ->method('disable')
            ->with('test_filter');

        $dbRole = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $dbRoleChildren = $this->getMockBuilder(Collection::class)->getMock();

        $dbRole->expects($this->any())
            ->method('getRole')
            ->willReturn('ROLE_ADMIN');

        $dbRole->expects($this->any())
            ->method('getName')
            ->willReturn('ROLE_ADMIN');

        $dbRole->expects($this->once())
            ->method('getChildren')
            ->willReturn($dbRoleChildren);

        $dbRoleChildren->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->repo->expects($this->once())
            ->method('findBy')
            ->with(['name' => ['ROLE_ADMIN']])
            ->willReturn([$dbRole]);

        $this->filters->expects($this->once())
            ->method('enable')
            ->with('test_filter');

        $cacheItem->expects($this->once())
            ->method('set');

        $this->cache->expects($this->once())
            ->method('save');

        $fullRoles = $this->roleHierarchy->getReachableRoles($roles);

        $this->assertCount(2, $fullRoles);
        $this->assertEquals($validRoles, $fullRoles);

        $fullExecCachedRoles = $this->roleHierarchy->getReachableRoles($roles);

        $this->assertCount(2, $fullExecCachedRoles);
        $this->assertEquals($validRoles, $fullExecCachedRoles);
    }

    public function testGetReachableRolesWithCachedRoles()
    {
        $roles = [
            new MockRole('ROLE_ADMIN'),
        ];
        $validRoles = [
            new Role('ROLE_ADMIN'),
            new Role('ROLE_USER'),
        ];

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)->getMock();

        $this->cache->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn([
                new Role('ROLE_ADMIN'),
                new Role('ROLE_USER'),
            ]);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(true);

        $fullRoles = $this->roleHierarchy->getReachableRoles($roles);

        $this->assertCount(2, $fullRoles);
        $this->assertEquals($validRoles, $fullRoles);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\SecurityException
     * @expectedExceptionMessage The Role class must be an instance of "Symfony\Component\Security\Core\Role\Role"
     */
    public function testInvalidRoleClassName()
    {
        $roles = [
            $this->getMockBuilder(\stdClass::class)->getMock(),
        ];

        $this->roleHierarchy->getReachableRoles($roles);
    }
}
