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

use Fxp\Component\Security\Event\CheckPermissionEvent;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CheckPermissionEventTest extends TestCase
{
    public function testEvent()
    {
        $sids = [
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $permissionMap = [
            '_global' => [
                'test' => true,
            ],
        ];
        $operation = 'test';
        $subject = MockObject::class;
        $field = 'name';

        $event = new CheckPermissionEvent($sids, $permissionMap, $operation, $subject, $field);

        $this->assertSame($sids, $event->getSecurityIdentities());
        $this->assertSame($permissionMap, $event->getPermissionMap());
        $this->assertSame($operation, $event->getOperation());
        $this->assertSame($subject, $event->getSubject());
        $this->assertSame($field, $event->getField());
        $this->assertNull($event->isGranted());

        $event->setGranted(true);

        $this->assertTrue($event->isGranted());
    }
}
