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

use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationTest extends TestCase
{
    public function testModel()
    {
        $org = new MockOrganization('FOO');

        $this->assertSame(23, $org->getId());
        $this->assertSame('FOO', $org->getName());
        $this->assertNull($org->getUser());
        $this->assertFalse($org->isUserOrganization());
        $this->assertCount(0, $org->getOrganizationRoles());
        $this->assertCount(0, $org->getOrganizationRoleNames());
        $this->assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));
        $this->assertCount(0, $org->getOrganizationGroups());
        $this->assertCount(0, $org->getOrganizationGroupNames());
        $this->assertFalse($org->hasOrganizationGroup('GROUP_DEFAULT'));
        $this->assertCount(0, $org->getOrganizationUsers());
        $this->assertCount(0, $org->getOrganizationUserNames());
        $this->assertFalse($org->hasOrganizationUser('user.test'));
        $this->assertSame('FOO', (string) $org);
    }

    public function testModelName()
    {
        $org = new MockOrganization('FOO');

        $this->assertSame('FOO', $org->getName());
        $org->setName('BAR');
        $this->assertSame('BAR', $org->getName());
    }

    public function testModelUser()
    {
        /* @var UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $org = new MockOrganization('FOO');

        $this->assertNull($org->getUser());
        $this->assertFalse($org->isUserOrganization());

        $org->setUser($user);
        $this->assertSame($user, $org->getUser());
        $this->assertTrue($org->isUserOrganization());
    }

    public function testModelRoles()
    {
        $role = new MockRole('ROLE_ADMIN');
        $org = new MockOrganization('FOO');

        $this->assertCount(0, $org->getOrganizationRoles());
        $this->assertCount(0, $org->getOrganizationRoleNames());
        $this->assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));

        $org->addOrganizationRole($role);

        $this->assertCount(1, $org->getOrganizationRoles());
        $this->assertCount(1, $org->getOrganizationRoleNames());
        $this->assertTrue($org->hasOrganizationRole('ROLE_ADMIN'));
        $this->assertSame('ROLE_ADMIN', current($org->getOrganizationRoleNames()));

        $org->removeOrganizationRole($role);

        $this->assertCount(0, $org->getOrganizationRoles());
        $this->assertCount(0, $org->getOrganizationRoleNames());
        $this->assertFalse($org->hasOrganizationRole('ROLE_ADMIN'));
    }

    public function testModelGroups()
    {
        /* @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->any())
            ->method('getName')
            ->willReturn('GROUP_DEFAULT');

        $org = new MockOrganization('FOO');

        $this->assertCount(0, $org->getOrganizationGroups());
        $this->assertCount(0, $org->getOrganizationGroupNames());
        $this->assertFalse($org->hasOrganizationRole('GROUP_DEFAULT'));

        $org->addOrganizationGroup($group);

        $this->assertCount(1, $org->getOrganizationGroups());
        $this->assertCount(1, $org->getOrganizationGroupNames());
        $this->assertTrue($org->hasOrganizationGroup('GROUP_DEFAULT'));
        $this->assertSame('GROUP_DEFAULT', current($org->getOrganizationGroupNames()));

        $org->removeOrganizationGroup($group);

        $this->assertCount(0, $org->getOrganizationGroups());
        $this->assertCount(0, $org->getOrganizationGroupNames());
        $this->assertFalse($org->hasOrganizationGroup('GROUP_DEFAULT'));
    }

    public function testModelUsers()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->any())
            ->method('getUsername')
            ->willReturn('user.test');

        /* @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $orgUser->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $org = new MockOrganization('FOO');

        $this->assertCount(0, $org->getOrganizationUsers());
        $this->assertCount(0, $org->getOrganizationUserNames());
        $this->assertFalse($org->hasOrganizationUser($user->getUsername()));

        $org->addOrganizationUser($orgUser);

        $this->assertCount(1, $org->getOrganizationUsers());
        $this->assertCount(1, $org->getOrganizationUserNames());
        $this->assertTrue($org->hasOrganizationUser($user->getUsername()));
        $this->assertSame($user->getUsername(), current($org->getOrganizationUserNames()));

        $org->removeOrganizationUser($orgUser);

        $this->assertCount(0, $org->getOrganizationUsers());
        $this->assertCount(0, $org->getOrganizationUserNames());
        $this->assertFalse($org->hasOrganizationUser($user->getUsername()));
    }
}
