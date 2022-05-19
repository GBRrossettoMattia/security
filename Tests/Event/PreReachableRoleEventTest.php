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

use Fxp\Component\Security\Event\PreReachableRoleEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PreReachableRoleEventTest extends TestCase
{
    public function testEvent()
    {
        $roles = [
            new Role('ROLE_FOO'),
            new Role('ROLE_BAR'),
        ];

        $event = new PreReachableRoleEvent($roles);
        $this->assertSame($roles, $event->getReachableRoles());
        $this->assertTrue($event->isPermissionEnabled());

        $roles[] = new Role('ROLE_BAZ');
        $event->setReachableRoles($roles);
        $event->setPermissionEnabled(false);
        $this->assertSame($roles, $event->getReachableRoles());
        $this->assertFalse($event->isPermissionEnabled());
    }
}
