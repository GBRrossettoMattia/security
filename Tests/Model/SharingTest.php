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

use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingTest extends TestCase
{
    public function testModel()
    {
        $startDate = new \DateTime('now');
        $endDate = new \DateTime('now + 1 day');

        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName(23);
        $sharing->setEnabled(true);
        $sharing->setStartedAt($startDate);
        $sharing->setEndedAt($endDate);

        $this->assertNull($sharing->getId());
        $this->assertSame(MockObject::class, $sharing->getSubjectClass());
        $this->assertSame(42, $sharing->getSubjectId());
        $this->assertSame(MockRole::class, $sharing->getIdentityClass());
        $this->assertSame(23, $sharing->getIdentityName());
        $this->assertTrue($sharing->isEnabled());
        $this->assertSame($startDate, $sharing->getStartedAt());
        $this->assertSame($endDate, $sharing->getEndedAt());
        $this->assertCount(0, $sharing->getRoles());
    }
}
