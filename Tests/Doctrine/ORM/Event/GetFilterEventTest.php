<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Event;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Fxp\Component\Security\Model\Sharing;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GetFilterEventTest extends TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetEntity;

    /**
     * @var SQLFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var GetFilterEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->connection = $this->getMockBuilder(Connection::class)->getMock();
        $this->targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->filter = $this->getMockForAbstractClass(SQLFilter::class, [$this->entityManager]);

        $this->entityManager->expects($this->any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($this->entityManager));

        $this->entityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->connection->expects($this->any())
            ->method('quote')
            ->willReturnCallback(function ($v) {
                return '\''.$v.'\'';
            });

        $this->event = new GetFilterEvent(
            $this->filter,
            $this->entityManager,
            $this->targetEntity,
            't0',
            Sharing::class
        );
    }

    public function testGetters()
    {
        $this->assertSame($this->entityManager, $this->event->getEntityManager());
        $this->assertSame($this->entityManager->getConnection(), $this->event->getConnection());
        $this->assertSame($this->entityManager->getClassMetadata(Sharing::class), $this->event->getClassMetadata(Sharing::class));
        $this->assertSame($this->entityManager->getClassMetadata(Sharing::class), $this->event->getSharingClassMetadata());
        $this->assertSame($this->targetEntity, $this->event->getTargetEntity());
        $this->assertSame('t0', $this->event->getTargetTableAlias());
    }

    public function testSetParameter()
    {
        $this->assertFalse($this->event->hasParameter('foo'));
        $this->event->setParameter('foo', true, 'boolean');
        $this->assertSame('\'1\'', $this->event->getParameter('foo'));
        $this->assertTrue($this->event->getRealParameter('foo'));
    }

    public function testSetFilterConstraint()
    {
        $this->assertSame('', $this->event->getFilterConstraint());

        $this->event->setFilterConstraint('TEST_FILTER');

        $this->assertSame('TEST_FILTER', $this->event->getFilterConstraint());
    }
}
