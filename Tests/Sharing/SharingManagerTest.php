<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Sharing;

use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingManager;
use Fxp\Component\Security\Sharing\SharingProviderInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfig;
use Fxp\Component\Security\SharingEvents;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingManagerTest extends TestCase
{
    /**
     * @var SharingProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var SharingManager
     */
    protected $sm;

    protected function setUp()
    {
        $this->provider = $this->getMockBuilder(SharingProviderInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->provider->expects($this->atLeastOnce())
            ->method('setSharingManager');

        $this->sm = new SharingManager($this->provider);
        $this->sm->setEventDispatcher($this->dispatcher);
    }

    public function testIsEnabled()
    {
        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(SharingEvents::DISABLED);

        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(SharingEvents::ENABLED);

        $this->assertTrue($this->sm->isEnabled());

        $this->sm->setEnabled(false);
        $this->assertFalse($this->sm->isEnabled());

        $this->sm->setEnabled(true);
        $this->assertTrue($this->sm->isEnabled());
    }

    public function testHasSubjectConfig()
    {
        $pm = new SharingManager($this->provider, [
            new SharingSubjectConfig(MockObject::class),
        ]);

        $this->assertTrue($pm->hasSubjectConfig(MockObject::class));
    }

    public function testHasIdentityConfig()
    {
        $pm = new SharingManager($this->provider, [], [
            new SharingIdentityConfig(MockRole::class),
        ]);

        $this->assertTrue($pm->hasIdentityConfig(MockRole::class));
    }

    public function testHasNotSubjectConfig()
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testHasNotIdentityConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddSubjectConfig()
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class));

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testAddIdentityConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class));

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\AlreadyConfigurationAliasExistingException
     * @expectedExceptionMessage The alias "foo" of sharing identity configuration for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockGroup
     */
    public function testAddIdentityConfigWithAlreadyExistingAlias()
    {
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class, 'foo'));
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockGroup::class, 'foo'));
    }

    public function testGetSubjectConfig()
    {
        $config = new SharingSubjectConfig(MockObject::class);
        $this->sm->addSubjectConfig($config);

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
        $this->assertSame($config, $this->sm->getSubjectConfig(MockObject::class));
    }

    public function testGetIdentityConfig()
    {
        $config = new SharingIdentityConfig(MockRole::class, 'role');
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
        $this->assertSame($config, $this->sm->getIdentityConfig(MockRole::class));
        $this->assertTrue($this->sm->hasIdentityConfig('role'));
        $this->assertSame($config, $this->sm->getIdentityConfig('role'));
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\SharingSubjectConfigNotFoundException
     * @expectedExceptionMessage The sharing subject configuration for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockRole" is not found
     */
    public function testGetSubjectConfigWithNotManagedClass()
    {
        $this->sm->getSubjectConfig(MockRole::class);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\SharingIdentityConfigNotFoundException
     * @expectedExceptionMessage The sharing identity configuration for the class "Fxp\Component\Security\Tests\Fixtures\Model\MockRole" is not found
     */
    public function testGetIdentityConfigWithNotManagedClass()
    {
        $this->sm->getIdentityConfig(MockRole::class);
    }

    public function testGetSubjectConfigs()
    {
        $config = new SharingSubjectConfig(MockRole::class);
        $this->sm->addSubjectConfig($config);

        $this->assertSame([$config], $this->sm->getSubjectConfigs());
    }

    public function testGetIdentityConfigs()
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        $this->assertSame([$config], $this->sm->getIdentityConfigs());
    }

    public function testHasIdentityRoleable()
    {
        $this->assertFalse($this->sm->hasIdentityRoleable());

        $config = new SharingIdentityConfig(MockRole::class, null, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityRoleable());
    }

    public function testHasIdentityPermissible()
    {
        $this->assertFalse($this->sm->hasIdentityPermissible());

        $config = new SharingIdentityConfig(MockRole::class, null, false, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityPermissible());
    }

    public function testHasSharingVisibilityWithoutConfig()
    {
        /* @var SubjectIdentityInterface|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class);

        $this->assertFalse($this->sm->hasSharingVisibility($subject));
    }

    public function getSharingVisibilities()
    {
        return [
            [SharingVisibilities::TYPE_NONE, false],
            [SharingVisibilities::TYPE_PUBLIC, true],
            [SharingVisibilities::TYPE_PRIVATE, true],
        ];
    }

    /**
     * @dataProvider getSharingVisibilities
     *
     * @param string $visibility The sharing visibility
     * @param bool   $result     The result
     */
    public function testHasSharingVisibility($visibility, $result)
    {
        /* @var SubjectIdentityInterface|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class);

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));
        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));

        $this->assertSame($result, $this->sm->hasSharingVisibility($subject));
    }

    public function testResetPreloadPermissions()
    {
        $object = new MockObject('foo', 42);
        $sm = $this->sm->resetPreloadPermissions([$object]);

        $this->assertSame($sm, $this->sm);
    }

    public function testResetPreloadPermissionsWithInvalidSubjectIdentity()
    {
        $sm = $this->sm->resetPreloadPermissions([42]);

        $this->assertSame($sm, $this->sm);
    }

    public function testClear()
    {
        $sm = $this->sm->clear();

        $this->assertSame($sm, $this->sm);
    }

    public function testIsGranted()
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $sharing2 = new MockSharing();
        $sharing2->setSubjectClass(MockObject::class);
        $sharing2->setSubjectId(42);
        $sharing2->setIdentityClass(MockUserRoleable::class);
        $sharing2->setIdentityName('user.test');
        $sharing2->setRoles(['ROLE_TEST']);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing, $sharing2]);

        $roleTest = new MockRole('ROLE_TEST');
        $perm2 = new MockPermission();
        $perm2->setOperation('test');
        $roleTest->addPermission($perm2);

        $this->provider->expects($this->once())
            ->method('getPermissionRoles')
            ->with(['ROLE_TEST'])
            ->willReturn([$roleTest]);

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);
        $iConfig2 = new SharingIdentityConfig(MockUserRoleable::class, 'user', true);
        $this->sm->addIdentityConfig($iConfig2);

        $this->sm->preloadPermissions([$object]);
        $this->sm->preloadRolePermissions([$subject]);

        $this->assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithField()
    {
        $operation = 'view';
        $field = 'name';
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $this->assertFalse($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithoutIdentityConfigRoleable()
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing]);

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);

        $this->sm->preloadPermissions([$object]);

        $this->assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testRenameIdentity()
    {
        $this->provider->expects($this->once())
            ->method('renameIdentity')
            ->with(MockRole::class, 'ROLE_FOO', 'ROLE_BAR')
            ->willReturn('QUERY');

        $this->sm->renameIdentity(MockRole::class, 'ROLE_FOO', 'ROLE_BAR');
    }

    public function testDeletes()
    {
        $ids = [42, 50];

        $this->provider->expects($this->once())
            ->method('deletes')
            ->with($ids)
            ->willReturn('QUERY');

        $this->sm->deletes($ids);
    }

    public function testDeleteIdentity()
    {
        $this->provider->expects($this->once())
            ->method('deleteIdentity')
            ->with(MockRole::class, 'ROLE_FOO')
            ->willReturn('QUERY');

        $this->sm->deleteIdentity(MockRole::class, 'ROLE_FOO');
    }
}
