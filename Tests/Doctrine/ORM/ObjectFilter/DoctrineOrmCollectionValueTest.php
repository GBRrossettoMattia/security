<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fxp\Component\Security\Doctrine\ORM\ObjectFilter\DoctrineOrmCollectionValue;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DoctrineOrmCollectionValueTest extends TestCase
{
    public function getValues()
    {
        return [
            [$this->getMockBuilder(Collection::class)->getMock(), true],
            [$this->getMockBuilder(\stdClass::class)->getMock(), false],
            ['string', false],
            [42, false],
        ];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testSupports($value, $result)
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        $this->assertSame($result, $collectionValue->supports($value));
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value The value
     */
    public function testGetValue($value)
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        $newValue = $collectionValue->getValue($value);

        $this->assertNotSame($value, $newValue);
        $this->assertInstanceOf(ArrayCollection::class, $newValue);
        $this->assertCount(0, $newValue);
    }
}
