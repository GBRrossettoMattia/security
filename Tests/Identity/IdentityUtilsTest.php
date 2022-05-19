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

use Fxp\Component\Security\Identity\IdentityUtils;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IdentityUtilsTest extends TestCase
{
    public function testMerge()
    {
        $role1 = new RoleSecurityIdentity(MockRole::class, 'ROLE_USER');
        $role2 = new RoleSecurityIdentity(MockRole::class, 'ROLE_ADMIN');
        $role3 = new RoleSecurityIdentity(MockRole::class, 'ROLE_USER');
        $role4 = new RoleSecurityIdentity(MockRole::class, 'ROLE_FOO');

        $sids = [$role1, $role2];
        $newSids = [$role3, $role4];
        $valid = [$role1, $role2, $role4];

        $sids = IdentityUtils::merge($sids, $newSids);

        $this->assertEquals($valid, $sids);
    }
}
