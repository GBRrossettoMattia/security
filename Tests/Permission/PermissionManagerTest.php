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

use Fxp\Component\Security\Event\CheckPermissionEvent;
use Fxp\Component\Security\Event\PostLoadPermissionsEvent;
use Fxp\Component\Security\Event\PreLoadPermissionsEvent;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;
use Fxp\Component\Security\Model\PermissionChecking;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Permission\PermissionManager;
use Fxp\Component\Security\Permission\PermissionProviderInterface;
use Fxp\Component\Security\PermissionEvents;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrgOptionalRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrgRequiredRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionManagerTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var PermissionProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var PermissionManager
     */
    protected $pm;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->provider = $this->getMockBuilder(PermissionProviderInterface::class)->getMock();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor
        );
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->pm->isEnabled());

        $this->pm->setEnabled(false);
        $this->assertFalse($this->pm->isEnabled());

        $this->pm->setEnabled(true);
        $this->assertTrue($this->pm->isEnabled());
    }

    public function testSetEnabledWithSharingManager()
    {
        $sm = $this->getMockBuilder(SharingManagerInterface::class)->getMock();

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sm
        );

        $sm->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $this->pm->setEnabled(false);
    }

    public function testHasConfig()
    {
        $pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            [
                new PermissionConfig(MockObject::class),
            ]
        );

        $this->assertTrue($pm->hasConfig(MockObject::class));
    }

    public function testHasNotConfig()
    {
        $this->assertFalse($this->pm->hasConfig(MockObject::class));
    }

    public function testAddConfig()
    {
        $this->assertFalse($this->pm->hasConfig(MockObject::class));

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->assertTrue($this->pm->hasConfig(MockObject::class));
    }

    public function testGetConfig()
    {
        $config = new PermissionConfig(MockObject::class);
        $this->pm->addConfig($config);

        $this->assertTrue($this->pm->hasConfig(MockObject::class));
        $this->assertSame($config, $this->pm->getConfig(MockObject::class));
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\PermissionConfigNotFoundException
     * @expectedExceptionMessage The permission configuration for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockObject" is not found
     */
    public function testGetConfigWithNotManagedClass()
    {
        $this->pm->getConfig(MockObject::class);
    }

    public function testGetConfigs()
    {
        $expected = [
            MockObject::class => new PermissionConfig(MockObject::class),
        ];

        $this->pm->addConfig($expected[MockObject::class]);

        $this->assertSame($expected, $this->pm->getConfigs());
    }

    public function testIsManaged()
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class));
        $object = new MockObject('foo');

        $this->assertTrue($this->pm->isManaged($object));
    }

    public function testIsManagedWithInvalidSubject()
    {
        $object = new \stdClass();

        $this->assertFalse($this->pm->isManaged($object));
    }

    public function testIsManagedWithNonExistentSubject()
    {
        $this->assertFalse($this->pm->isManaged('FooBar'));
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "FieldVote|SubjectIdentityInterface|object|string", "NULL"
     */
    public function testIsManagedWithUnexpectedTypeException()
    {
        $this->assertFalse($this->pm->isManaged(null));
    }

    public function testIsManagedWithNonManagedClass()
    {
        $this->assertFalse($this->pm->isManaged(MockObject::class));
    }

    public function testIsFieldManaged()
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class, [], [], [
            new PermissionFieldConfig('name'),
        ]));

        $object = new MockObject('foo');
        $field = 'name';

        $this->assertTrue($this->pm->isFieldManaged($object, $field));
    }

    public function testIsGranted()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithNonExistentSubject()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = 'FooBar';
        $permission = 'view';

        $this->assertFalse($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithGlobalPermission()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = null;
        $permission = 'foo';
        $perm = new MockPermission();
        $perm->setOperation('foo');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm]);

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMaster()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm]);

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization'));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $orgUser));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMasterWithEmptyObjectOfSubject()
    {
        $permConfigOrg = new PermissionConfig(MockOrganization::class);
        $permConfigOrgUser = new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization');

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $object = new SubjectIdentity(MockOrganizationUser::class, 42);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects($this->once())
            ->method('getMasterClass')
            ->with($permConfigOrgUser)
            ->willReturn(MockOrganization::class);

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm]);

        $this->pm->addConfig($permConfigOrg);
        $this->pm->addConfig($permConfigOrgUser);

        $res = $this->pm->isGranted($sids, $permission, $object);
        $this->assertTrue($res);
    }

    public function testIsGrantedWithGlobalPermissionWithoutGrant()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER__foo'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_ADMIN__foo'),
        ];
        $object = null;
        $permission = 'bar';
        $perm = new MockPermission();
        $perm->setOperation('baz');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER', 'ROLE_ADMIN'])
            ->willReturn([$perm]);

        $this->assertFalse($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsFieldGranted()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $field = 'name';
        $permission = 'view';

        $this->assertTrue($this->pm->isFieldGranted($sids, $permission, $object, $field));
    }

    public function testIsGrantedWithSharingPermission()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $permission = 'test';

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([]);

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('preloadRolePermissions')
            ->with([SubjectIdentity::fromObject($object)]);

        $sharingManager->expects($this->once())
            ->method('isGranted')
            ->with($permission, SubjectIdentity::fromObject($object))
            ->willReturn(true);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );
        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithSystemPermission()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([]);

        $this->pm->addConfig(new PermissionConfig(
            MockOrganization::class,
            [
                'view',
                'create',
                'update',
            ],
            [],
            [
                new PermissionFieldConfig('name', ['read']),
            ]
        ));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ],
            'organization',
            [
                'create' => 'edit',
                'update' => 'edit',
            ]
        ));

        $this->assertTrue($this->pm->isGranted($sids, 'view', $org));
        $this->assertTrue($this->pm->isGranted($sids, 'view', $orgUser));
        $this->assertTrue($this->pm->isFieldGranted($sids, 'read', $org, 'name'));
        $this->assertFalse($this->pm->isFieldGranted($sids, 'edit', $org, 'name'));
        $this->assertFalse($this->pm->isFieldGranted($sids, 'read', $orgUser, 'organization'));
        $this->assertTrue($this->pm->isFieldGranted($sids, 'edit', $orgUser, 'organization'));
        $this->pm->clear();
    }

    public function getRoles()
    {
        return [
            [new MockRole('ROLE_TEST')],
            [new MockOrgOptionalRole('ROLE_TEST')],
            [new MockOrgRequiredRole('ROLE_TEST')],
        ];
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissions(MockRole $role)
    {
        $subject = null;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([]);

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions);

        $res = $this->pm->getRolePermissions($role, $subject);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithConfigPermissions(MockRole $role)
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([]);

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions);

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test'],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithClassConfigPermission(MockRole $role)
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([]);

        $this->provider->expects($this->once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions);

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithFieldConfigPermission(MockRole $role)
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([]);

        $this->provider->expects($this->once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions);

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithFieldConfigPermissionAndMaster(MockRole $role)
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false, true),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([]);

        $this->provider->expects($this->once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions);

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ],
            'organization'
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     *
     * @expectedException \Fxp\Component\Security\Exception\PermissionNotFoundException
     * @expectedExceptionMessage The permission "test" for "Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUser" is not found ant it required by the permission configuration
     */
    public function testGetRolePermissionsWithRequiredConfigPermission(MockRole $role)
    {
        $subject = MockOrganizationUser::class;
        $permissions = [];

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions);

        $this->provider->expects($this->once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn([]);

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $this->pm->getRolePermissions($role, $subject);
    }

    public function testGetFieldRolePermissions()
    {
        $role = new MockRole('ROLE_TEST');
        $subject = MockObject::class;
        $field = 'name';
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass($subject);
        $permission->setField($field);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true),
        ];

        $this->provider->expects($this->once())
            ->method('getPermissionsBySubject')
            ->with(new FieldVote($subject, $field))
            ->willReturn($permissions);

        $res = $this->pm->getRoleFieldPermissions($role, $subject, $field);

        $this->assertEquals($expected, $res);
    }

    public function testPreloadPermissions()
    {
        $objects = [new MockObject('foo')];

        $pm = $this->pm->preloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testPreloadPermissionsWithSharing()
    {
        $objects = [new MockObject('foo')];

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('preloadPermissions')
            ->with($objects);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );

        $pm = $this->pm->preloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissions()
    {
        $objects = [
            new MockObject('foo'),
        ];

        $pm = $this->pm->resetPreloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissionsWithSharing()
    {
        $objects = [new MockObject('foo')];

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('resetPreloadPermissions')
            ->with($objects);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testEvents()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setOperation($permission);
        $perm->setClass(MockObject::class);
        $preLoad = false;
        $postLoad = false;
        $checkPerm = false;

        $this->dispatcher->addListener(PermissionEvents::PRE_LOAD, function (PreLoadPermissionsEvent $event) use ($sids, &$preLoad) {
            $preLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PermissionEvents::POST_LOAD, function (PostLoadPermissionsEvent $event) use ($sids, &$postLoad) {
            $postLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PermissionEvents::CHECK_PERMISSION, function (CheckPermissionEvent $event) use ($sids, &$checkPerm) {
            $checkPerm = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm]);

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->assertTrue($preLoad);
        $this->assertTrue($postLoad);
        $this->assertTrue($checkPerm);
    }

    public function testOverrideGrantValueWithEvent()
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $checkPerm = false;

        $this->dispatcher->addListener(PermissionEvents::CHECK_PERMISSION, function (CheckPermissionEvent $event) use ($sids, &$checkPerm) {
            $checkPerm = true;
            $event->setGranted(true);
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([]);

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->assertTrue($checkPerm);
    }
}
