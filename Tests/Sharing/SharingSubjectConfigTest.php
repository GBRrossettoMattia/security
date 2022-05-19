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

use Fxp\Component\Security\Sharing\SharingSubjectConfig;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingSubjectConfigTest extends TestCase
{
    public function testSharingSubjectConfigByDefault()
    {
        $config = new SharingSubjectConfig(MockObject::class);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(SharingVisibilities::TYPE_NONE, $config->getVisibility());
    }

    public function testSharingSubjectConfig()
    {
        $config = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(SharingVisibilities::TYPE_PRIVATE, $config->getVisibility());
    }
}
