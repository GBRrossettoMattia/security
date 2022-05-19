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
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Fxp\Component\Security\Doctrine\ORM\Listener\PermissionCheckerListener;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionCheckerListenerTest extends TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authChecker;

    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionManager;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var PermissionCheckerListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new PermissionCheckerListener();

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setAuthorizationChecker($this->authChecker);
        $this->listener->setPermissionManager($this->permissionManager);

        $this->assertCount(2, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return [
            ['setTokenStorage', []],
            ['setAuthorizationChecker', ['tokenStorage']],
            ['setPermissionManager', ['tokenStorage', 'authChecker']],
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
        $msg = sprintf('The "%s()" method must be called before the init of the "Fxp\Component\Security\Doctrine\ORM\Listener\PermissionCheckerListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new PermissionCheckerListener();

        if (in_array('tokenStorage', $setters)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (in_array('authChecker', $setters)) {
            $listener->setAuthorizationChecker($this->authChecker);
        }

        if (in_array('permissionManager', $setters)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $listener->onFlush($args);
    }

    public function testPostFlush()
    {
        $this->permissionManager->expects($this->once())
            ->method('resetPreloadPermissions')
            ->with([]);

        $this->listener->postFlush();
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

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithEmptyToken()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

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

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to create the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToCreateEntity()
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

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([$object]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->permissionManager->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('perm_create', $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to update the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToUpdateEntity()
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

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$object]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->permissionManager->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('perm_update', $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to delete the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToDeleteEntity()
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

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$object]);

        $this->permissionManager->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('perm_delete', $object)
            ->willReturn(false);

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

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->permissionManager->expects($this->once())
            ->method('preloadPermissions')
            ->with([]);

        $this->listener->onFlush($args);
    }
}
