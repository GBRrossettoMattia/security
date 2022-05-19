<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Firewall;

use Fxp\Component\Security\Firewall\AnonymousRoleListener;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnonymousRoleListenerTest extends TestCase
{
    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AuthenticationTrustResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trustResolver;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var AnonymousRoleListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->config = [
            'role' => 'ROLE_CUSTOM_ANONYMOUS',
        ];
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->request = $this->getMockBuilder(Request::class)->getMock();
        $this->event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $this->event->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->listener = new AnonymousRoleListener(
            $this->sidManager,
            $this->config,
            $this->trustResolver,
            $this->tokenStorage
        );
    }

    public function testBasic()
    {
        $this->assertTrue($this->listener->isEnabled());
        $this->listener->setEnabled(false);
        $this->assertFalse($this->listener->isEnabled());
    }

    public function testHandleWithDisabledListener()
    {
        $this->sidManager->expects($this->never())
            ->method('addSpecialRole');

        $this->tokenStorage->expects($this->never())
            ->method('getToken');

        $this->trustResolver->expects($this->never())
            ->method('isAnonymous');

        $this->listener->setEnabled(false);
        $this->listener->handle($this->event);
    }

    public function testHandleWithoutAnonymousRole()
    {
        $this->listener = new AnonymousRoleListener(
            $this->sidManager,
            [
                'role' => null,
            ],
            $this->trustResolver,
            $this->tokenStorage
        );

        $this->sidManager->expects($this->never())
            ->method('addSpecialRole');

        $this->tokenStorage->expects($this->never())
            ->method('getToken');

        $this->trustResolver->expects($this->never())
            ->method('isAnonymous');

        $this->listener->handle($this->event);
    }

    public function testHandleWithoutToken()
    {
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->trustResolver->expects($this->never())
            ->method('isAnonymous');

        $this->sidManager->expects($this->once())
            ->method('addSpecialRole')
            ->with(new Role('ROLE_CUSTOM_ANONYMOUS'));

        $this->listener->handle($this->event);
    }

    public function testHandleWithToken()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->trustResolver->expects($this->once())
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(true);

        $this->sidManager->expects($this->once())
            ->method('addSpecialRole')
            ->with(new Role('ROLE_CUSTOM_ANONYMOUS'));

        $this->listener->handle($this->event);
    }
}
