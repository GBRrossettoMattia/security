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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Fxp\Component\Security\Doctrine\ORM\Listener\ObjectFilterListener;
use Fxp\Component\Security\ObjectFilter\ObjectFilterInterface;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFilterListenerTest extends TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionManager;

    /**
     * @var ObjectFilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFilter;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var ObjectFilterListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->objectFilter = $this->getMockBuilder(ObjectFilterInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new ObjectFilterListener();

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setPermissionManager($this->permissionManager);
        $this->listener->setObjectFilter($this->objectFilter);

        $this->assertCount(3, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return [
            ['setTokenStorage', []],
            ['setPermissionManager', ['tokenStorage']],
            ['setObjectFilter', ['tokenStorage', 'permissionManager']],
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
        $msg = sprintf('The "%s()" method must be called before the init of the "Fxp\Component\Security\Doctrine\ORM\Listener\ObjectFilterListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new ObjectFilterListener();

        if (in_array('tokenStorage', $setters)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (in_array('permissionManager', $setters)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        if (in_array('objectFilter', $setters)) {
            $listener->setObjectFilter($this->objectFilter);
        }

        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();

        $listener->postLoad($args);
    }

    public function testPostFlush()
    {
        $this->permissionManager->expects($this->once())
            ->method('resetPreloadPermissions')
            ->with([]);

        $this->listener->postFlush();
    }

    public function testPostLoadWithDisabledPermissionManager()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithEmptyToken()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithConsoleToken()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoad()
    {
        /* @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $entity = new \stdClass();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);

        $this->objectFilter->expects($this->once())
            ->method('filter')
            ->with($entity);

        $this->listener->postLoad($args);
    }

    public function testOnFlushWithDisabledPermissionManager()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithEmptyToken()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithConsoleToken()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithCreateEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([$object]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->objectFilter->expects($this->once())
            ->method('restore');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithUpdateEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$object]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->objectFilter->expects($this->once())
            ->method('restore');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithDeleteEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$object]);

        $this->listener->onFlush($args);
    }

    public function testOnFLush()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->objectFilter->expects($this->once())
            ->method('commit');

        $this->listener->onFlush($args);
    }
}
