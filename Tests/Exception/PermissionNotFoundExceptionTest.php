<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Exception;

use Fxp\Component\Security\Exception\PermissionNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionNotFoundExceptionTest extends TestCase
{
    public function testException()
    {
        $expected = 'The permission "read" for "stdClass" is not found ant it required by the permission configuration';
        $e = new PermissionNotFoundException('read', \stdClass::class);

        $this->assertSame($expected, $e->getMessage());
    }

    public function testExceptionWithField()
    {
        $expected = 'The permission "read" for "stdClass::foo" is not found ant it required by the permission configuration';
        $e = new PermissionNotFoundException('read', \stdClass::class, 'foo');

        $this->assertSame($expected, $e->getMessage());
    }
}
