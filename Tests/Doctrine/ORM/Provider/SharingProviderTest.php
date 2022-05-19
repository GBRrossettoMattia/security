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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Fxp\Component\Security\Doctrine\ORM\Provider\SharingProvider;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingProviderTest extends TestCase
{
    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $roleRepo;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingRepo;

    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingManager;

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
        $this->roleRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->sharingRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
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
                'execute',
            ]
        );
    }

    public function testGetPermissionRoles()
    {
        $roles = [
            'ROLE_USER',
        ];
        $result = [
            new MockRole('ROLE_USER'),
        ];

        $this->roleRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('r')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('leftJoin')
            ->with('r.permissions', 'p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('UPPER(r.name) IN (:roles)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('roles', $roles)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider();
        $this->assertSame($result, $provider->getPermissionRoles($roles));
    }

    public function testGetPermissionRolesWithEmptyRoles()
    {
        $this->roleRepo->expects($this->never())
            ->method('createQueryBuilder');

        $provider = $this->createProvider();
        $this->assertSame([], $provider->getPermissionRoles([]));
    }

    public function testGetSharingEntries()
    {
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('subject0_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('subject0_id', 42)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('subject1_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('subject1_id', 23)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('andWhere')
            ->with('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(11))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider();
        $this->assertSame($result, $provider->getSharingEntries($subjects));
    }

    public function testGetSharingEntriesWithEmptySubjects()
    {
        $this->sharingRepo->expects($this->never())
            ->method('createQueryBuilder');

        $provider = $this->createProvider();
        $this->assertSame([], $provider->getSharingEntries([]));
    }

    public function testGetPermissionRolesWithSecurityIdentities()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingManager->expects($this->at(0))
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(new SharingIdentityConfig(MockRole::class, 'role'));

        $this->sharingManager->expects($this->at(1))
            ->method('getIdentityConfig')
            ->with(MockUserRoleable::class)
            ->willReturn(new SharingIdentityConfig(MockUserRoleable::class, 'role'));

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('subject0_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('subject0_id', 42)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('subject1_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('subject1_id', 23)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('andWhere')
            ->with('(s.identityClass = :sid0_class AND s.identityName IN (:sid0_ids)) OR (s.identityClass = :sid1_class AND s.identityName IN (:sid1_ids))')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('setParameter')
            ->with('sid0_class', MockRole::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('setParameter')
            ->with('sid0_ids', ['ROLE_USER'])
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('setParameter')
            ->with('sid1_class', MockUserRoleable::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(11))
            ->method('setParameter')
            ->with('sid1_ids', ['user.test'])
            ->willReturn($this->qb);

        $this->qb->expects($this->at(12))
            ->method('andWhere')
            ->with('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(13))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(14))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(15))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(16))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider();
        $this->assertSame($result, $provider->getSharingEntries($subjects, $sids));
    }

    public function testGetPermissionRolesWithEmptySecurityIdentities()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'IS_AUTHENTICATED_ANONYMOUSLY'),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];
        $result = [];

        $this->sharingManager->expects($this->never())
            ->method('getIdentityConfig');

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('subject0_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('subject0_id', 42)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('subject1_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('subject1_id', 23)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('andWhere')
            ->with('s.enabled = TRUE AND (s.startedAt IS NULL OR s.startedAt <= CURRENT_TIMESTAMP()) AND (s.endedAt IS NULL OR s.endedAt >= CURRENT_TIMESTAMP())')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(11))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider();
        $this->assertSame($result, $provider->getSharingEntries($subjects, $sids));
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The "setSharingManager()" must be called before
     */
    public function testGetSharingEntriesWithoutSharingManager()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $subjects = [
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        ];

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('leftJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb);

        $provider = $this->createProvider(MockRole::class, MockSharing::class, false);
        $provider->getSharingEntries($subjects, $sids);
    }

    public function testRenameIdentity()
    {
        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('update')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('set')
            ->with('s.identityName', ':newName')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('s.identityClass = :type')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('andWhere')
            ->with('s.identityName = :oldName')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('type', MockRole::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('oldName', 'ROLE_FOO')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('newName', 'ROLE_BAR')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn('RESULT');

        $provider = $this->createProvider();
        $provider->renameIdentity(MockRole::class, 'ROLE_FOO', 'ROLE_BAR');
    }

    public function testDeleteIdentity()
    {
        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('where')
            ->with('s.identityClass = :type')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('andWhere')
            ->with('s.identityName = :name')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('type', MockRole::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('name', 'ROLE_FOO')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn('RESULT');

        $provider = $this->createProvider();
        $provider->deleteIdentity(MockRole::class, 'ROLE_FOO');
    }

    public function testDeletes()
    {
        $ids = [42, 50];

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('where')
            ->with('s.id IN (:ids)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('setParameter')
            ->with('ids', $ids)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn('RESULT');

        $provider = $this->createProvider();
        $provider->deletes($ids);
    }

    protected function createProvider($roleClass = MockRole::class, $sharingClass = MockSharing::class, $addManager = true)
    {
        $this->roleRepo->expects($this->any())
            ->method('getClassName')
            ->willReturn($roleClass);

        $this->sharingRepo->expects($this->any())
            ->method('getClassName')
            ->willReturn($sharingClass);

        $provider = new SharingProvider(
            $this->roleRepo,
            $this->sharingRepo,
            $this->sidManager,
            $this->tokenStorage
        );

        if ($addManager) {
            $provider->setSharingManager($this->sharingManager);
        }

        return $provider;
    }
}
