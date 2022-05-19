<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Event;

use Fxp\Component\Security\Event\PostLoadPermissionsEvent;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PostLoadPermissionsEventTest extends TestCase
{
    public function testEvent()
    {
        $sids = [
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $roles = [
            'ROLE_USER',
        ];
        $permissionMap = [];

        $event = new PostLoadPermissionsEvent($sids, $roles, $permissionMap);

        $this->assertSame($sids, $event->getSecurityIdentities());
        $this->assertSame($roles, $event->getRoles());
        $this->assertSame($permissionMap, $event->getPermissionMap());

        $permissionMap2 = [
            '_global' => [
                'test' => true,
            ],
        ];
        $event->setPermissionMap($permissionMap2);

        $this->assertSame($permissionMap2, $event->getPermissionMap());
    }
}
