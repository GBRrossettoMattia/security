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

use Fxp\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Fxp\Component\Security\Event\ObjectViewGrantedEvent;
use Fxp\Component\Security\Event\PostCommitObjectFilterEvent;
use Fxp\Component\Security\Event\PreCommitObjectFilterEvent;
use Fxp\Component\Security\Event\RestoreViewGrantedEvent;
use Fxp\Component\Security\ObjectFilter\ObjectFilter;
use Fxp\Component\Security\ObjectFilter\ObjectFilterExtensionInterface;
use Fxp\Component\Security\ObjectFilter\UnitOfWorkInterface;
use Fxp\Component\Security\ObjectFilterEvents;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFilterTest extends TestCase
{
    /**
     * @var UnitOfWorkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uow;

    /**
     * @var ObjectFilterExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ofe;

    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pm;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ac;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ObjectFilter
     */
    protected $of;

    protected function setUp()
    {
        $this->uow = $this->getMockBuilder(UnitOfWorkInterface::class)->getMock();
        $this->ofe = $this->getMockBuilder(ObjectFilterExtensionInterface::class)->getMock();
        $this->pm = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->ac = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->dispatcher = new EventDispatcher();

        $this->of = new ObjectFilter($this->ofe, $this->pm, $this->ac, $this->dispatcher, $this->uow);
    }

    public function testGetUnitOfWork()
    {
        $this->assertSame($this->uow, $this->of->getUnitOfWork());
    }

    public function testCommitEvents()
    {
        $preEventAction = false;
        $postEventAction = false;
        $objects = [];

        $this->dispatcher->addListener(ObjectFilterEvents::PRE_COMMIT, function (PreCommitObjectFilterEvent $event) use (&$objects, &$preEventAction) {
            $preEventAction = true;
            $this->assertSame($objects, $event->getObjects());
        });

        $this->dispatcher->addListener(ObjectFilterEvents::POST_COMMIT, function (PostCommitObjectFilterEvent $event) use (&$objects, &$postEventAction) {
            $postEventAction = true;
            $this->assertSame($objects, $event->getObjects());
        });

        $this->pm->expects($this->once())
            ->method('preloadPermissions')
            ->with($objects);

        $this->of->commit();

        $this->assertTrue($preEventAction);
        $this->assertTrue($postEventAction);
    }

    public function testFilter()
    {
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->of->filter($object);

        $this->assertNull($object->getName());
    }

    public function testFilterTransactional()
    {
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->of->beginTransaction();
        $this->of->filter($object);
        $this->of->commit();

        $this->assertSame(42, $object->getId());
        $this->assertNull($object->getName());
    }

    public function testFilterSkipAuthorizationChecker()
    {
        $eventAction = 0;
        $object = new MockObject('foo');

        $this->prepareFilterTest($object);

        $this->ac->expects($this->never())
            ->method('isGranted');

        $this->dispatcher->addListener(ObjectFilterEvents::OBJECT_VIEW_GRANTED, function (ObjectViewGrantedEvent $event) use (&$eventAction) {
            ++$eventAction;
            $event->setGranted(true);
        });

        $this->dispatcher->addListener(ObjectFilterEvents::OBJECT_FIELD_VIEW_GRANTED, function (ObjectFieldViewGrantedEvent $event) use (&$eventAction) {
            ++$eventAction;
            $event->setGranted(false);
        });

        $this->of->filter($object);

        $this->assertSame(2, $eventAction);
        $this->assertNull($object->getName());
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object", "integer" given
     */
    public function testFilterWithInvalidType()
    {
        /* @var object $object */
        $object = 42;

        $this->of->filter($object);
    }

    public function testRestore()
    {
        $object = new MockObject('foo');

        $this->prepareRestoreTest($object);

        $this->ac->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->of->restore($object);

        $this->assertSame('bar', $object->getName());
    }

    public function testRestoreTransactional()
    {
        $object = new MockObject('foo');

        $this->uow->expects($this->once())
            ->method('attach')
            ->with($object);

        $this->uow->expects($this->once())
            ->method('getObjectChangeSet')
            ->with($object)
            ->willReturn([
                'name' => [
                    'old' => 'bar',
                    'new' => 'foo',
                ],
            ]);

        $this->pm->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);

        $this->ac->expects($this->once())
            ->method('isGranted')
            ->willReturn(false);

        $this->of->beginTransaction();
        $this->of->restore($object);
        $this->of->commit();

        $this->assertSame('bar', $object->getName());
    }

    public function testRestoreSkipAuthorizationChecker()
    {
        $eventAction = false;
        $object = new MockObject('foo');

        $this->prepareRestoreTest($object);

        $this->ac->expects($this->never())
            ->method('isGranted');

        $this->dispatcher->addListener(ObjectFilterEvents::RESTORE_VIEW_GRANTED, function (RestoreViewGrantedEvent $event) use (&$eventAction) {
            $eventAction = true;
            $event->setGranted(false);
        });

        $this->of->restore($object);

        $this->assertTrue($eventAction);
        $this->assertSame('bar', $object->getName());
    }

    public function getRestoreActions()
    {
        return [
            [false, false, null, 'foo', null],
            [false, false, 'bar', 'foo', 'bar'],
            [false, false, 'bar', null, 'bar'],

            [true, false, null, 'foo', null],
            [true, false, 'bar', 'foo', 'bar'],
            [true, false, 'bar', null, 'bar'],

            [true, true, null, 'foo', 'foo'],
            [true, true, 'bar', 'foo', 'foo'],
            [true, true, 'bar', null, null],
        ];
    }

    /**
     * @dataProvider getRestoreActions
     *
     * @param bool  $allowView  Check if the user is allowed to view the object
     * @param bool  $allowEdit  Check if the user is allowed to edit the object
     * @param mixed $oldValue   The object old value
     * @param mixed $newValue   The object new value
     * @param mixed $validValue The valid object value
     */
    public function testRestoreByAction($allowView, $allowEdit, $oldValue, $newValue, $validValue)
    {
        $object = new MockObject($newValue);
        $fv = new FieldVote($object, 'name');

        $this->prepareRestoreTest($object, [
            'name' => [
                'old' => $oldValue,
                'new' => $newValue,
            ],
        ]);

        $this->ac->expects($this->at(0))
            ->method('isGranted')
            ->with('perm_read', $fv)
            ->willReturn($allowView);

        if ($allowView) {
            $this->ac->expects($this->at(1))
                ->method('isGranted')
                ->with('perm_edit', $fv)
                ->willReturn($allowEdit);
        }

        $this->of->restore($object);

        $this->assertSame($validValue, $object->getName());
    }

    public function testExcludedClasses()
    {
        $this->of->setExcludedClasses([
            MockObject::class,
        ]);

        $object = new MockObject('foo');

        $this->uow->expects($this->never())
            ->method('attach');

        $this->ofe->expects($this->never())
            ->method('filterValue');

        $this->pm->expects($this->never())
            ->method('preloadPermissions');

        $this->ac->expects($this->never())
            ->method('isGranted');

        $this->of->filter($object);

        $this->assertNotNull($object->getName());
    }

    /**
     * Prepare the restore test.
     *
     * @param object $object The mock object
     */
    protected function prepareFilterTest($object)
    {
        $this->uow->expects($this->once())
            ->method('attach')
            ->with($object);

        $this->ofe->expects($this->once())
            ->method('filterValue')
            ->willReturn(null);

        $this->pm->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);
    }

    /**
     * Prepare the restore test.
     *
     * @param object     $object    The mock object
     * @param array|null $changeSet The field change set
     */
    protected function prepareRestoreTest($object, $changeSet = null)
    {
        if (null === $changeSet) {
            $changeSet = [
                'name' => [
                    'old' => 'bar',
                    'new' => 'foo',
                ],
            ];
        }

        $this->pm->expects($this->once())
            ->method('preloadPermissions')
            ->with([$object]);

        $this->uow->expects($this->once())
            ->method('attach')
            ->with($object);

        $this->uow->expects($this->once())
            ->method('getObjectChangeSet')
            ->with($object)
            ->willReturn($changeSet);
    }

    /**
     * @expectedException \Fxp\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object", "integer" given
     */
    public function testRestoreWithInvalidType()
    {
        /* @var object $object */
        $object = 42;

        $this->of->restore($object);
    }
}
