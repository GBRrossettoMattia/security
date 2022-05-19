<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission;

use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FieldVoteTest extends TestCase
{
    public function testFieldVote()
    {
        $object = new MockObject('foo');
        $field = 'name';

        $fv = new FieldVote($object, $field);

        $this->assertInstanceOf(SubjectIdentityInterface::class, $fv->getSubject());
        $this->assertSame($object, $fv->getSubject()->getObject());
        $this->assertSame(get_class($object), $fv->getSubject()->getType());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithSubjectIdentity()
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $field = 'name';

        $fv = new FieldVote($object, $field);

        $this->assertSame($object, $fv->getSubject());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithClassname()
    {
        $object = \stdClass::class;
        $field = 'field';

        $fv = new FieldVote($object, $field);

        $this->assertNull($fv->getSubject()->getObject());
        $this->assertSame(\stdClass::class, $fv->getSubject()->getType());
        $this->assertSame($field, $fv->getField());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "Fxp\Component\Security\Identity\SubjectIdentityInterface|object|string", "integer" given
     */
    public function testFieldVoteWithInvalidSubject()
    {
        $object = 42;
        $field = 'field';

        new FieldVote($object, $field);
    }
}
