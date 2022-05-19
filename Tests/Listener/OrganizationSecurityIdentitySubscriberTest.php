<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Listener;

use Fxp\Component\Security\Event\AddSecurityIdentityEvent;
use Fxp\Component\Security\Listener\OrganizationSecurityIdentitySubscriber;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationSecurityIdentitySubscriberTest extends TestCase
{
    /**
     * @var RoleHierarchyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $roleHierarchy;

    /**
     * @var OrganizationalContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orgContext;

    /**
     * @var OrganizationSecurityIdentitySubscriber
     */
    protected $listener;

    protected function setUp()
    {
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchyInterface::class)->getMock();
        $this->orgContext = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->listener = new OrganizationSecurityIdentitySubscriber($this->roleHierarchy, $this->orgContext);

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testCacheIdWithPersonalOrganization()
    {
        $this->orgContext->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn(null);

        $this->assertSame('', $this->listener->getCacheId());
    }

    public function testCacheIdWithOrganization()
    {
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->once())
            ->method('getId')
            ->willReturn(42);

        $this->orgContext->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn($org);

        $this->assertSame('org42', $this->listener->getCacheId());
    }

    public function testAddOrganizationSecurityIdentities()
    {
        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];
        $event = new AddSecurityIdentityEvent($token, $sids);

        $this->listener->addOrganizationSecurityIdentities($event);

        $this->assertSame($sids, $event->getSecurityIdentities());
    }

    public function testAddOrganizationSecurityIdentitiesWithInvalidArgument()
    {
        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [];
        $event = new AddSecurityIdentityEvent($token, $sids);

        $token->expects($this->once())
            ->method('getUser')
            ->willThrowException(new \InvalidArgumentException('Test'));

        $this->listener->addOrganizationSecurityIdentities($event);

        $this->assertSame($sids, $event->getSecurityIdentities());
    }
}
