<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Identity;

use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSubjectObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SubjectIdentityTest extends TestCase
{
    public function testDebugInfo()
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame('SubjectIdentity(Fxp\Component\Security\Tests\Fixtures\Model\MockObject, 42)', (string) $si);
    }

    public function testTypeAndIdentifier()
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame($object, $si->getObject());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The type cannot be empty
     */
    public function testEmptyType()
    {
        new SubjectIdentity(null, '42');
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The identifier cannot be empty
     */
    public function testEmptyIdentifier()
    {
        new SubjectIdentity(MockObject::class, '');
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object|null", "integer" given
     */
    public function testInvalidSubject()
    {
        new SubjectIdentity(MockObject::class, '42', 42);
    }

    public function getIdentities()
    {
        return [
            [new SubjectIdentity(MockObject::class, '42'), true],
            [new SubjectIdentity(\stdClass::class, '42'), false],
            [new SubjectIdentity(MockObject::class, '42', new MockObject('foo')), true],
            [new SubjectIdentity(MockObject::class, '50', new MockObject('foo', 50)), false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result)
    {
        $object = new MockObject('foo');
        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame($result, $si->equals($value));
    }

    public function testFromClassname()
    {
        $si = SubjectIdentity::fromClassname(MockObject::class);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame('class', $si->getIdentifier());
        $this->assertNull($si->getObject());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The class "FooBar" does not exist
     */
    public function testFromClassnameWithNonExistentClass()
    {
        SubjectIdentity::fromClassname('FooBar');
    }

    public function testFromObject()
    {
        $object = new MockObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectInstance()
    {
        $object = new MockSubjectObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockSubjectObject::class, $si->getType());
        $this->assertSame((string) $object->getSubjectIdentifier(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectIdentityInstance()
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame($object, $si);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage Expected argument of type "object", "integer" given
     */
    public function testFromObjectWithNonObject()
    {
        /* @var object $object */
        $object = 42;

        SubjectIdentity::fromObject($object);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The identifier cannot be empty
     */
    public function testFromObjectWithEmptyIdentifier()
    {
        $object = new MockObject('foo', null);

        SubjectIdentity::fromObject($object);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The object must either implement the SubjectInterface, or have a method named "getId"
     */
    public function testFromObjectWithInvalidObject()
    {
        $object = new \stdClass();

        SubjectIdentity::fromObject($object);
    }
}
