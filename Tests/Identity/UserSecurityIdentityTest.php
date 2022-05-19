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

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\UserSecurityIdentity;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class UserSecurityIdentityTest extends TestCase
{
    public function testDebugInfo()
    {
        $sid = new UserSecurityIdentity(MockUserRoleable::class, 'user.test');

        $this->assertSame('UserSecurityIdentity(user.test)', (string) $sid);
    }

    public function testTypeAndIdentifier()
    {
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        $this->assertSame(MockUserRoleable::class, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities()
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(MockUserRoleable::class);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new UserSecurityIdentity(MockUserRoleable::class, 'identifier'), true],
            [new UserSecurityIdentity(MockUserRoleable::class, 'other'), false],
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
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test');

        $sid = UserSecurityIdentity::fromAccount($user);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(get_class($user), $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormToken()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test');

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $sid = UserSecurityIdentity::fromToken($token);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(get_class($user), $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The user class must implement "Fxp\Component\Security\Model\UserInterface"
     */
    public function testFormTokenWithInvalidInterface()
    {
        /* @var AdvancedUserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(AdvancedUserInterface::class)->getMock();

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        UserSecurityIdentity::fromToken($token);
    }
}
