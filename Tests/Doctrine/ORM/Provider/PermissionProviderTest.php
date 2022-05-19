<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Fxp\Component\Security\Doctrine\ORM\Provider\PermissionProvider;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use Fxp\Component\Security\Permission\PermissionProviderInterface;
use Fxp\Component\Security\PermissionContexts;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionProviderTest extends TestCase
{
    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionRepo;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $qb;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    protected function setUp()
    {
        $this->permissionRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->registry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();

        $this->query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getResult',
            ]
        );
    }

    public function testGetPermissions()
    {
        $roles = [
            'ROLE_USER',
        ];
        $result = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('leftJoin')
            ->with('p.roles', 'r')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('where')
            ->with('UPPER(r.name) IN (:roles)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('setParameter')
            ->with('roles', $roles)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider();
        $this->assertSame($result, $provider->getPermissions($roles));
    }

    public function testGetPermissionsOptimizationWithEmptyRoles()
    {
        $this->permissionRepo->expects($this->never())
            ->method('createQueryBuilder');

        $provider = $this->createProvider();
        $this->assertSame([], $provider->getPermissions([]));
    }

    public function testGetMasterClass()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockOrganizationUser::class);

        $permConfig->expects($this->atLeast(2))
            ->method('getMaster')
            ->willReturn('organization');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($om);

        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $meta->expects($this->once())
            ->method('getAssociationTargetClass')
            ->with('organization')
            ->willReturn(MockOrganization::class);

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with(MockOrganizationUser::class)
            ->willReturn($meta);

        $provider = $this->createProvider();
        $this->assertSame(MockOrganization::class, $provider->getMasterClass($permConfig));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The doctrine object manager is not found for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockObject"
     */
    public function testGetMasterClassWithoutObjectManagerForClass()
    {
        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class);

        $provider = $this->createProvider();
        $provider->getMasterClass($permConfig);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The permission master association is not configured for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockObject"
     */
    public function testGetMasterClassWithoutMaster()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class);

        $permConfig->expects($this->once())
            ->method('getMaster')
            ->willReturn(null);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($om);

        $provider = $this->createProvider();
        $provider->getMasterClass($permConfig);
    }

    public function testGetPermissionsBySubject()
    {
        $subject = new FieldVote(MockObject::class, 'name');
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('class', $subject->getSubject()->getType())
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('andWhere')
            ->with('p.field = :field')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('field', $subject->getField())
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($expected);

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject);

        $this->assertSame($expected, $res);
    }

    public function testGetPermissionsBySubjectAndContexts()
    {
        $subject = new FieldVote(MockObject::class, 'name');
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('context_role', '%"'.PermissionContexts::ROLE.'"%')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('context_organization_role', '%"'.PermissionContexts::ORGANIZATION_ROLE.'"%')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('context_sharing', '%"'.PermissionContexts::SHARING.'"%')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('andWhere')
            ->with('p.contexts IS NULL OR p.contexts LIKE :context_role OR p.contexts LIKE :context_organization_role OR p.contexts LIKE :context_sharing')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('setParameter')
            ->with('class', $subject->getSubject()->getType())
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('andWhere')
            ->with('p.field = :field')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('setParameter')
            ->with('field', $subject->getField())
            ->willReturn($this->qb);

        $this->qb->expects($this->at(11))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($expected);

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject, [
            PermissionContexts::ROLE,
            PermissionContexts::ORGANIZATION_ROLE,
            PermissionContexts::SHARING,
        ]);

        $this->assertSame($expected, $res);
    }

    public function testGetPermissionsBySubjectWithoutSubject()
    {
        $subject = null;
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('andWhere')
            ->with('p.class IS NULL')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('andWhere')
            ->with('p.field IS NULL')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($expected);

        $provider = $this->createProvider();
        $res = $provider->getPermissionsBySubject($subject);

        $this->assertSame($expected, $res);
    }

    public function testGetConfigPermissions()
    {
        $expected = [
            new MockPermission(),
        ];

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('andWhere')
            ->with('p.class = :class')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('class', PermissionProviderInterface::CONFIG_CLASS)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($expected);

        $provider = $this->createProvider();
        $res = $provider->getConfigPermissions();

        $this->assertSame($expected, $res);
    }

    protected function createProvider()
    {
        return new PermissionProvider(
            $this->permissionRepo,
            $this->registry
        );
    }
}
