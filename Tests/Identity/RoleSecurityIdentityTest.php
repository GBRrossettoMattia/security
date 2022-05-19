<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Identity;

use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleSecurityIdentityTest extends TestCase
{
    public function testDebugInfo()
    {
        $sid = new RoleSecurityIdentity(MockRole::class, 'ROLE_TEST');

        $this->assertSame('RoleSecurityIdentity(ROLE_TEST)', (string) $sid);
    }

    public function testTypeAndIdentifier()
    {
        $identity = new RoleSecurityIdentity(MockRole::class, 'identifier');

        $this->assertSame(MockRole::class, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities()
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(MockRole::class);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new RoleSecurityIdentity(MockRole::class, 'identifier'), true],
            [new RoleSecurityIdentity(MockRole::class, 'other'), false],
            [$id3, false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result)
    {
        $identity = new RoleSecurityIdentity(MockRole::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount()
    {
        /* @var Role|\PHPUnit_Framework_MockObject_MockObject $role */
        $role = $this->getMockBuilder(Role::class)->disableOriginalConstructor()->getMock();
        $role->expects($this->once())
            ->method('getRole')
            ->willReturn('ROLE_TEST');

        $sid = RoleSecurityIdentity::fromAccount($role);

        $this->assertInstanceOf(RoleSecurityIdentity::class, $sid);
        $this->assertSame(get_class($role), $sid->getType());
        $this->assertSame('ROLE_TEST', $sid->getIdentifier());
    }

    public function testFormToken()
    {
        /* @var Role|\PHPUnit_Framework_MockObject_MockObject $role */
        $role = $this->getMockBuilder(Role::class)->disableOriginalConstructor()->getMock();
        $role->expects($this->once())
            ->method('getRole')
            ->willReturn('ROLE_TEST');

        /* @var RoleableInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(RoleableInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getRoles')
            ->willReturn([$role]);

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $sids = RoleSecurityIdentity::fromToken($token);

        $this->assertCount(1, $sids);
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[0]);
        $this->assertSame(get_class($role), $sids[0]->getType());
        $this->assertSame('ROLE_TEST', $sids[0]->getIdentifier());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The user class must implement "Fxp\Component\Security\Model\Traits\RoleableInterface"
     */
    public function testFormTokenWithInvalidInterface()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        RoleSecurityIdentity::fromToken($token);
    }
}
