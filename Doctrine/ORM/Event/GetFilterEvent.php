<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Event;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Component\EventDispatcher\Event;

/**
 * The doctrine orm get filter event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GetFilterEvent extends Event
{
    /**
     * @var SQLFilter
     */
    protected $filter;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ClassMetadata
     */
    protected $targetEntity;

    /**
     * @var string
     */
    protected $targetTableAlias;

    /**
     * @var string
     */
    protected $sharingClass;

    /**
     * @var string
     */
    protected $filterConstraint = '';

    /**
     * @var \ReflectionProperty|null
     */
    private $refParameters;

    /**
     * Constructor.
     *
     * @param SQLFilter              $filter           The sql filter
     * @param EntityManagerInterface $entityManager    The entity manager
     * @param ClassMetaData          $targetEntity     The target entity
     * @param string                 $targetTableAlias The target table alias
     * @param string                 $sharingClass     The class name of the sharing model
     */
    public function __construct(SqlFilter $filter,
                                EntityManagerInterface $entityManager,
                                ClassMetadata $targetEntity,
                                $targetTableAlias,
                                $sharingClass)
    {
        $this->filter = $filter;
        $this->entityManager = $entityManager;
        $this->targetEntity = $targetEntity;
        $this->targetTableAlias = $targetTableAlias;
        $this->sharingClass = $sharingClass;
    }

    /**
     * Get the entity manager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Get the doctrine connection.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->entityManager->getConnection();
    }

    /**
     * Get the doctrine metadata of class name.
     *
     * @param string $classname The class name
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($classname)
    {
        return $this->entityManager->getClassMetadata($classname);
    }

    /**
     * Get the doctrine metadata of sharing class.
     *
     * @return ClassMetadata
     */
    public function getSharingClassMetadata()
    {
        return $this->getClassMetadata($this->sharingClass);
    }

    /**
     * Sets a parameter that can be used by the filter.
     *
     * @param string      $name  The name of the parameter
     * @param string      $value The value of the parameter
     * @param string|null $type  The parameter type
     *
     * @return self
     */
    public function setParameter($name, $value, $type = null)
    {
        $this->filter->setParameter($name, $value, $type);

        return $this;
    }

    /**
     * Check if a parameter was set for the filter.
     *
     * @param string $name The name of the parameter
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        return $this->filter->hasParameter($name);
    }

    /**
     * Get a parameter to use in a query.
     *
     * @param string $name The name of the parameter
     *
     * @return string
     */
    public function getParameter($name)
    {
        return $this->filter->getParameter($name);
    }

    /**
     * Gets a parameter to use in a query without the output escaping.
     *
     * @param string $name The name of the parameter
     *
     * @return string|string[]|bool|bool[]|int|int[]|float|float[]|null
     *
     * @throws \InvalidArgumentException
     */
    public function getRealParameter($name)
    {
        $this->getParameter($name);

        if (null === $this->refParameters) {
            $this->refParameters = new \ReflectionProperty(SQLFilter::class, 'parameters');
            $this->refParameters->setAccessible(true);
        }

        $parameters = $this->refParameters->getValue($this->filter);

        return $parameters[$name]['value'];
    }

    /**
     * Get the target entity.
     *
     * @return ClassMetadata
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Get the target table alias.
     *
     * @return string
     */
    public function getTargetTableAlias()
    {
        return $this->targetTableAlias;
    }

    /**
     * Set the filter constraint.
     *
     * @param string $filterConstraint The filter constraint
     *
     * @return self
     */
    public function setFilterConstraint($filterConstraint)
    {
        $this->filterConstraint = $filterConstraint;

        return $this;
    }

    /**
     * Get the filter constraint.
     *
     * @return string
     */
    public function getFilterConstraint()
    {
        return $this->filterConstraint;
    }
}
