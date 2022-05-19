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

use Fxp\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Fxp\Component\Security\OrganizationalTypes;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SetOrganizationalOptionalFilterTypeEventTest extends TestCase
{
    public function testEvent()
    {
        $type = OrganizationalTypes::OPTIONAL_FILTER_ALL;

        $event = new SetOrganizationalOptionalFilterTypeEvent($type);

        $this->assertSame($type, $event->getOptionalFilterType());
    }
}
