<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\ObjectFilter;

use Fxp\Component\Security\ObjectFilter\ObjectFilterExtension;
use Fxp\Component\Security\ObjectFilter\ObjectFilterVoterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFilterExtensionTest extends TestCase
{
    public function testFilterValue()
    {
        $voter1 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter1->expects($this->once())
            ->method('supports')
            ->willReturn(false);
        $voter1->expects($this->never())
            ->method('getValue');

        $voter2 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter2->expects($this->once())
            ->method('supports')
            ->willReturn(true);
        $voter2->expects($this->once())
            ->method('getValue')
            ->willReturn('TEST');

        $voter3 = $this->getMockBuilder(ObjectFilterVoterInterface::class)->getMock();
        $voter3->expects($this->never())
            ->method('supports');
        $voter3->expects($this->never())
            ->method('getValue');

        $ofe = new ObjectFilterExtension([
            $voter1,
            $voter2,
            $voter3,
        ]);

        $this->assertSame('TEST', $ofe->filterValue(42));
    }
}
