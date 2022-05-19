<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Model;

use Fxp\Component\Security\Model\PermissionChecking;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionCheckingTest extends TestCase
{
    public function testModel()
    {
        $perm = new MockPermission();
        $permChecking = new PermissionChecking($perm, true, true);

        $this->assertSame($perm, $permChecking->getPermission());
        $this->assertTrue($permChecking->isGranted());
        $this->assertTrue($permChecking->isLocked());
    }
}
