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

use Fxp\Component\Security\Firewall\HostRoleListener;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class HostRoleListenerTest extends TestCase
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
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var HostRoleListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->config = [
            '/foo.bar.tld/' => 'ROLE_HOST',
            '/.*.baz.tld/' => 'ROLE_HOST_BAZ',
            '/.*.foo.*/' => 'ROLE_HOST_FOO',
            '*.bar' => 'ROLE_HOST_BAR',
        ];
        $this->request = $this->getMockBuilder(Request::class)->getMock();
        $this->event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $this->event->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->listener = new HostRoleListener($this->sidManager, $this->config);
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

        $this->listener->setEnabled(false);
        $this->listener->handle($this->event);
    }

    public function testHandleWithoutHostRole()
    {
        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('no.host-role.tld');

        $this->sidManager->expects($this->never())
            ->method('addSpecialRole');

        $this->listener->handle($this->event);
    }

    public function testHandleWithoutToken()
    {
        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld');

        $this->sidManager->expects($this->once())
            ->method('addSpecialRole')
            ->with(new Role('ROLE_HOST'));

        $this->listener->handle($this->event);
    }

    public function testHandleWithAlreadyRoleIncluded()
    {
        $token = new AnonymousToken('secret', 'user', [
            'ROLE_HOST',
        ]);

        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld');

        $this->sidManager->expects($this->once())
            ->method('addSpecialRole')
            ->with(new Role('ROLE_HOST'));

        $this->listener->handle($this->event);

        $this->assertCount(1, $token->getRoles());
    }

    public function getHosts()
    {
        return [
            ['foo.bar.tld', 'ROLE_HOST'],
            ['foo.baz.tld', 'ROLE_HOST_BAZ'],
            ['a.foo.tld', 'ROLE_HOST_FOO'],
            ['b.foo.tld', 'ROLE_HOST_FOO'],
            ['a.foo.com', 'ROLE_HOST_FOO'],
            ['b.foo.com', 'ROLE_HOST_FOO'],
            ['a.foo.org', 'ROLE_HOST_FOO'],
            ['b.foo.org', 'ROLE_HOST_FOO'],
            ['www.example.bar', 'ROLE_HOST_BAR'],
        ];
    }

    /**
     * @dataProvider getHosts
     *
     * @param string $host      The host name
     * @param string $validRole The valid role
     */
    public function testHandle($host, $validRole)
    {
        $token = new AnonymousToken('secret', 'user', [
            'ROLE_FOO',
        ]);

        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn($host);

        $this->sidManager->expects($this->once())
            ->method('addSpecialRole')
            ->with(new Role($validRole));

        $this->listener->handle($this->event);

        $this->assertCount(1, $token->getRoles());
    }
}
