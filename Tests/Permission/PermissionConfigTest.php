<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission;

use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionConfigTest extends TestCase
{
    public function testPermissionConfigByDefault()
    {
        $operations = ['create', 'view', 'update', 'delete'];
        $config = new PermissionConfig(MockObject::class, $operations);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame([], $config->getFields());
        $this->assertNull($config->getMaster());
    }

    public function testPermissionConfig()
    {
        $operations = ['invite', 'view', 'update', 'revoke'];
        $alias = [
            'create' => 'invite',
            'delete' => 'revoke',
        ];
        $fields = [
            'name' => new PermissionFieldConfig('name'),
        ];
        $master = 'foo';
        $config = new PermissionConfig(MockObject::class, $operations, $alias, array_values($fields), $master);

        $this->assertSame(MockObject::class, $config->getType());

        $this->assertSame($fields, $config->getFields());
        $this->assertSame($fields['name'], $config->getField('name'));
        $this->assertNull($config->getField('foo'));

        $this->assertSame($master, $config->getMaster());

        $this->assertSame($operations, $config->getOperations());
        $this->assertTrue($config->hasOperation('view'));
        $this->assertFalse($config->hasOperation('foo'));
        $this->assertSame($alias, $config->getMappingPermissions());
        $this->assertTrue($config->hasOperation('create'));
    }
}
