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

use Fxp\Component\Security\Event\AddSecurityIdentityEvent;
use Fxp\Component\Security\Listener\GroupSecurityIdentitySubscriber;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserGroupable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GroupSecurityIdentitySubscriberTest extends TestCase
{
    public function testAddGroupSecurityIdentitiesWithException()
    {
        $listener = new GroupSecurityIdentitySubscriber();
        $this->assertCount(1, $listener->getSubscribedEvents());

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }

    public function testAddGroupSecurityIdentities()
    {
        $listener = new GroupSecurityIdentitySubscriber();
        $this->assertCount(1, $listener->getSubscribedEvents());

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];

        $user = new MockUserGroupable();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }
}
