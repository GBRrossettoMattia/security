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

use Fxp\Component\Security\Event\SetCurrentOrganizationEvent;
use Fxp\Component\Security\Model\OrganizationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SetCurrentOrganizationEventTest extends TestCase
{
    public function testEvent()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $event = new SetCurrentOrganizationEvent($org);

        $this->assertSame($org, $event->getOrganization());
    }
}
