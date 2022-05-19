<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Listener;

use Fxp\Component\Security\Event\AbstractEditableSecurityEvent;
use Fxp\Component\Security\Event\PostReachableRoleEvent;
use Fxp\Component\Security\Listener\DisablePermissionSubscriber;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DisablePermissionSubscriberTest extends TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permManager;

    protected function setUp()
    {
        $this->permManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
    }

    public function testDisable()
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        /* @var AbstractEditableSecurityEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockForAbstractClass(AbstractEditableSecurityEvent::class);

        $this->permManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->permManager->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $listener->disablePermissionManager($event);
    }

    public function testEnable()
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        $event = new PostReachableRoleEvent([], true);

        $this->permManager->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $listener->enablePermissionManager($event);
    }
}
