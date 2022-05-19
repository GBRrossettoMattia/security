<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Fxp\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingDeleteListenerTest extends TestCase
{
    /**
     * @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingManager;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $qb;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    /**
     * @var SharingDeleteListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $this->listener = new SharingDeleteListener(MockSharing::class);

        $this->listener->setSharingManager($this->sharingManager);

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            false,
            true,
            [
                'execute',
            ]
        );

        $this->assertCount(2, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return [
            ['setSharingManager', []],
        ];
    }

    /**
     * @dataProvider getInvalidInitMethods
     *
     * @expectedException \Fxp\Component\Security\Exception\SecurityException
     *
     * @param string   $method  The method
     * @param string[] $setters The setters
     */
    public function testInvalidInit($method, array $setters)
    {
        $msg = sprintf('The "%s()" method must be called before the init of the "Fxp\Component\Security\Doctrine\ORM\Listener\SharingDeleteListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new SharingDeleteListener(MockSharing::class);

        if (in_array('sharingManager', $setters)) {
            $listener->setSharingManager($this->sharingManager);
        }

        $listener->getSharingManager();
    }

    public function testGetSharingManager()
    {
        $this->assertSame($this->sharingManager, $this->listener->getSharingManager());
    }

    public function testOnFlush()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        /* @var PostFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $postArgs */
        $postArgs = $this->getMockBuilder(PostFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $args->expects($this->atLeast(1))
            ->method('getEntityManager')
            ->willReturn($this->em);

        $postArgs->expects($this->atLeast(1))
            ->method('getEntityManager')
            ->willReturn($this->em);

        // on flush
        $object = new MockObject('foo', 42);
        $object2 = new MockObject('bar', 50);
        $role = new MockRole('ROLE_TEST', 23);
        $group = new MockGroup('GROUP_TEST', 32);
        $deletions = [$object, $role, $object2, $group];

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($deletions);

        $this->sharingManager->expects($this->atLeastOnce())
            ->method('hasSubjectConfig')
            ->willReturnCallback(function ($type) {
                return MockObject::class === $type;
            });

        $this->sharingManager->expects($this->atLeastOnce())
            ->method('hasIdentityConfig')
            ->willReturnCallback(function ($type) {
                return MockRole::class === $type || MockGroup::class === $type;
            });

        // post flush: query builder
        $this->em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('delete')
            ->with(MockSharing::class, 's')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('andWhere')
            ->with('(s.subjectClass = :subjectClass_0 AND s.subjectId IN (:subjectIds_0))')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('setParameter')
            ->with('subjectClass_0', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('subjectIds_0', [42, 50])
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('andWhere')
            ->with('(s.identityClass = :identityClass_0 AND s.identityName IN (:identityNames_0)) OR (s.identityClass = :identityClass_1 AND s.identityName IN (:identityNames_1))')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('identityClass_0', MockRole::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('identityNames_0', [23])
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('setParameter')
            ->with('identityClass_1', MockGroup::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('setParameter')
            ->with('identityNames_1', [32])
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute');

        $this->listener->onFlush($args);
        $this->listener->postFlush($postArgs);
    }
}
