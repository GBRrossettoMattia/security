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

use Fxp\Component\Security\ObjectFilter\MixedValue;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MixedValueTest extends TestCase
{
    public function getValues()
    {
        return [
            ['string', null],
            [42, null],
            [42.5, null],
            [true, null],
            [false, null],
            [null, null],
            [new \stdClass(), null],
            [['42'], []],
        ];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value    The value
     * @param mixed $expected The expected value
     */
    public function test($value, $expected)
    {
        $mv = new MixedValue();

        $this->assertTrue($mv->supports($value));
        $this->assertSame($expected, $mv->getValue($value));
    }
}
