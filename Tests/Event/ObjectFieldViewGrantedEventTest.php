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

use Fxp\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFieldViewGrantedEventTest extends TestCase
{
    public function testEvent()
    {
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');

        $event = new ObjectFieldViewGrantedEvent($fieldVote);

        $this->assertSame($fieldVote, $event->getFieldVote());
        $this->assertSame($fieldVote->getSubject()->getObject(), $event->getObject());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object", "NULL" given
     */
    public function testEventWithInvalidFieldVote()
    {
        $object = MockObject::class;
        $fieldVote = new FieldVote($object, 'foo');

        new ObjectFieldViewGrantedEvent($fieldVote);
    }
}
