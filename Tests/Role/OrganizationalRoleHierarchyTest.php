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
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\Role\OrganizationalRoleHierarchy;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalRoleHierarchyTest extends TestCase
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
     * @var OrganizationalContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var OrganizationalRoleHierarchy
     */
    protected $roleHierarchy;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder(ManagerRegistryInterface::class)->getMock();
        $this->roleClassname = MockRole::class;
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->repo = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->filters = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $hierarchy = [
            'ROLE_ADMIN' => [
                'ROLE_USER',
            ],
        ];
        $this->roleHierarchy = new OrganizationalRoleHierarchy(
            $hierarchy,
            $this->registry,
            $this->roleClassname,
            $this->cache,
            $this->context
        );

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

    public function getOrganizationContextType()
    {
        return [
            [null],
            ['user'],
            ['organization'],
        ];
    }

    /**
     * @dataProvider getOrganizationContextType
     *
     * @param string|null $orgContextType The organization context type
     */
    public function testGetReachableRolesWithCustomRoles($orgContextType)
    {
        $this->initOrgContextType($orgContextType);

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
    }

    /**
     * Init the organization context type.
     *
     * @param string|null $orgContextType The organization context type
     */
    protected function initOrgContextType($orgContextType)
    {
        $org = null;

        if (in_array($orgContextType, ['user', 'organization'])) {
            $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
            $org->expects($this->once())
                ->method('isUserOrganization')
                ->willReturn('user' === $orgContextType);
        }

        $this->context->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn($org);
    }
}
