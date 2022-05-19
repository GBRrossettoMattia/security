<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Fxp\Component\Security\Exception\InvalidArgumentException;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use Fxp\Component\Security\Permission\PermissionProviderInterface;
use Fxp\Component\Security\Permission\PermissionUtils;

/**
 * The Doctrine Orm Permission Provider.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionProvider implements PermissionProviderInterface
{
    /**
     * @var EntityRepository
     */
    protected $permissionRepo;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * Constructor.
     *
     * @param EntityRepository $permissionRepository The permission repository
     * @param ManagerRegistry  $registry             The doctrine registry
     */
    public function __construct(EntityRepository $permissionRepository,
                                ManagerRegistry $registry)
    {
        $this->permissionRepo = $permissionRepository;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(array $roles)
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->permissionRepo->createQueryBuilder('p')
            ->leftJoin('p.roles', 'r')
            ->where('UPPER(r.name) IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc');

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsBySubject($subject = null, $contexts = null)
    {
        /* @var SubjectIdentityInterface|null $subject */
        list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);

        $qb = $this->permissionRepo->createQueryBuilder('p')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc');

        $this->addWhereContexts($qb, $contexts);
        $this->addWhereOptionalField($qb, 'class', null !== $subject ? $subject->getType() : null);
        $this->addWhereOptionalField($qb, 'field', $field);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPermissions($contexts = null)
    {
        $qb = $this->permissionRepo->createQueryBuilder('p')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc');

        $this->addWhereContexts($qb, $contexts);
        $this->addWhereOptionalField($qb, 'class', PermissionProviderInterface::CONFIG_CLASS);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterClass(PermissionConfigInterface $config)
    {
        $type = $config->getType();
        $om = $this->registry->getManagerForClass($type);
        $this->validateMaster($config, $om);
        $masterClass = $type;

        foreach (explode('.', $config->getMaster()) as $master) {
            $meta = $om->getClassMetadata($masterClass);
            $masterClass = $meta->getAssociationTargetClass($master);
        }

        return $masterClass;
    }

    /**
     * Validate the master config.
     *
     * @param PermissionConfigInterface $config The permission config
     * @param ObjectManager|null        $om     The doctrine object manager
     */
    private function validateMaster(PermissionConfigInterface $config, $om)
    {
        if (null === $om) {
            $msg = 'The doctrine object manager is not found for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }

        if (null === $config->getMaster()) {
            $msg = 'The permission master association is not configured for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }
    }

    /**
     * Add the optional field condition.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string       $field The field name
     * @param mixed|null   $value The value
     */
    private function addWhereOptionalField(QueryBuilder $qb, $field, $value)
    {
        if (null === $value) {
            $qb->andWhere('p.'.$field.' IS NULL');
        } else {
            $qb->andWhere('p.'.$field.' = :'.$field)->setParameter($field, $value);
        }
    }

    /**
     * Add the permission contexts condition.
     *
     * @param QueryBuilder         $qb       The query builder
     * @param string[]|string|null $contexts The contexts
     */
    private function addWhereContexts(QueryBuilder $qb, $contexts = null)
    {
        if (null !== $contexts) {
            $contexts = (array) $contexts;
            $where = 'p.contexts IS NULL';

            foreach ($contexts as $context) {
                $key = 'context_'.$context;
                $where .= sprintf(' OR p.contexts LIKE :%s', $key);
                $qb->setParameter($key, '%"'.$context.'"%');
            }

            $qb->andWhere($where);
        }
    }
}
