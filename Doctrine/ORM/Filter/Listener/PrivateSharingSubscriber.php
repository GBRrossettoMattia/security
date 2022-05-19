<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Filter\Listener;

use Doctrine\ORM\Mapping\ClassMetadata;
use Fxp\Component\Security\Doctrine\DoctrineUtils;
use Fxp\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Fxp\Component\Security\Model\Traits\OwnerableInterface;
use Fxp\Component\Security\Model\Traits\OwnerableOptionalInterface;
use Fxp\Component\Security\SharingFilterEvents;
use Fxp\Component\Security\SharingVisibilities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sharing filter subscriber of Doctrine ORM SQL Filter to filter
 * the private sharing records.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PrivateSharingSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $privateFilter = SharingFilterEvents::getName(
            SharingFilterEvents::DOCTRINE_ORM_FILTER,
            SharingVisibilities::TYPE_PRIVATE
        );

        return [
            $privateFilter => ['getFilter', 0],
        ];
    }

    /**
     * Get the sharing filter.
     *
     * @param GetFilterEvent $event The event
     */
    public function getFilter(GetFilterEvent $event)
    {
        $filter = $this->buildSharingFilter($event);
        $filter = $this->buildOwnerFilter($event, $filter);

        $event->setFilterConstraint($filter);
    }

    /**
     * Build the query filter with sharing entries.
     *
     * @param GetFilterEvent $event The event
     *
     * @return string
     */
    private function buildSharingFilter(GetFilterEvent $event)
    {
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();
        $connection = $event->getConnection();
        $classname = $connection->quote($targetEntity->getName());
        $meta = $event->getSharingClassMetadata();
        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);

        $filter = <<<SELECTCLAUSE
{$targetTableAlias}.{$meta->getColumnName('id')} IN (SELECT
    s.{$meta->getColumnName('subjectId')}{$identifier}
FROM
    {$meta->getTableName()} s
WHERE
    s.{$meta->getColumnName('subjectClass')} = {$classname}
    AND s.{$meta->getColumnName('enabled')} IS TRUE
    AND (s.{$meta->getColumnName('startedAt')} IS NULL OR s.{$meta->getColumnName('startedAt')} <= CURRENT_TIMESTAMP)
    AND (s.{$meta->getColumnName('endedAt')} IS NULL OR s.{$meta->getColumnName('endedAt')} >= CURRENT_TIMESTAMP)
    AND ({$this->addWhereSecurityIdentitiesForSharing($event, $meta)})
GROUP BY
    s.{$meta->getColumnName('subjectId')})
SELECTCLAUSE;

        return $filter;
    }

    /**
     * Add the where condition of security identities.
     *
     * @param GetFilterEvent $event The event
     * @param ClassMetadata  $meta  The class metadata of sharing entity
     *
     * @return string
     */
    private function addWhereSecurityIdentitiesForSharing(GetFilterEvent $event, ClassMetadata $meta)
    {
        $where = '';
        $mapSids = (array) $event->getRealParameter('map_security_identities');
        $mapSids = !empty($mapSids) ? $mapSids : ['_without_security_identity' => 'null'];
        $connection = $event->getConnection();

        foreach ($mapSids as $type => $stringIds) {
            $where .= '' === $where ? '' : ' OR ';
            $where .= sprintf('(s.%s = %s AND s.%s IN (%s))',
                $meta->getColumnName('identityClass'),
                $connection->quote($type),
                $meta->getColumnName('identityName'),
                $stringIds);
        }

        return $where;
    }

    /**
     * Build the query filter with owner.
     *
     * @param GetFilterEvent $event  The event
     * @param string         $filter The previous filter
     *
     * @return string
     */
    private function buildOwnerFilter(GetFilterEvent $event, $filter)
    {
        $class = $event->getTargetEntity()->getName();
        $interfaces = class_implements($class);

        if (in_array(OwnerableInterface::class, $interfaces)) {
            $filter = $this->buildRequiredOwnerFilter($event, $filter);
        } elseif (in_array(OwnerableOptionalInterface::class, $interfaces)) {
            $filter = $this->buildOptionalOwnerFilter($event, $filter);
        }

        return $filter;
    }

    /**
     * Build the query filter with required owner.
     *
     * @param GetFilterEvent $event  The event
     * @param string         $filter The previous filter
     *
     * @return string
     */
    private function buildRequiredOwnerFilter(GetFilterEvent $event, $filter)
    {
        $connection = $event->getConnection();
        $platform = $connection->getDatabasePlatform();
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();

        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);
        $ownerId = $event->getRealParameter('user_id');
        $ownerColumn = $this->getAssociationColumnName($targetEntity, 'owner');
        $ownerFilter = null !== $ownerId
            ? "{$targetTableAlias}.{$ownerColumn}{$identifier} = {$connection->quote($ownerId)}"
            : "{$platform->getIsNullExpression($targetTableAlias.'.'.$ownerColumn)}";

        $filter = <<<SELECTCLAUSE
{$ownerFilter}
    OR
({$filter})
SELECTCLAUSE;

        return $filter;
    }

    /**
     * Build the query filter with optional owner.
     *
     * @param GetFilterEvent $event  The event
     * @param string         $filter The previous filter
     *
     * @return string
     */
    private function buildOptionalOwnerFilter(GetFilterEvent $event, $filter)
    {
        $targetEntity = $event->getTargetEntity();
        $targetTableAlias = $event->getTargetTableAlias();
        $connection = $event->getConnection();
        $platform = $connection->getDatabasePlatform();
        $identifier = DoctrineUtils::castIdentifier($targetEntity, $connection);
        $ownerId = $event->getRealParameter('user_id');
        $ownerColumn = $this->getAssociationColumnName($targetEntity, 'owner');
        $ownerFilter = null !== $ownerId
            ? "{$targetTableAlias}.{$ownerColumn}{$identifier} = {$connection->quote($ownerId)} OR "
            : '';

        $filter = <<<SELECTCLAUSE
{$ownerFilter}{$platform->getIsNullExpression($targetTableAlias.'.'.$ownerColumn)}
    OR
({$filter})
SELECTCLAUSE;

        return $filter;
    }

    /**
     * Get the column name of association field name.
     *
     * @param ClassMetadata $meta      The class metadata
     * @param string        $fieldName The field name
     *
     * @return string
     */
    private function getAssociationColumnName(ClassMetadata $meta, $fieldName)
    {
        $mapping = $meta->getAssociationMapping($fieldName);

        return current($mapping['joinColumnFieldNames']);
    }
}
