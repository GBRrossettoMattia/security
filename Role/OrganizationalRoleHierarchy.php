<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Role;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\Organizational\OrganizationalUtil;
use Psr\Cache\CacheItemPoolInterface;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalRoleHierarchy extends RoleHierarchy
{
    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param array                               $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface            $registry      The doctrine registry
     * @param string                              $roleClassname The classname of role
     * @param CacheItemPoolInterface|null         $cache         The cache
     * @param OrganizationalContextInterface|null $context       The organizational context
     */
    public function __construct(array $hierarchy,
                                ManagerRegistryInterface $registry,
                                $roleClassname,
                                CacheItemPoolInterface $cache = null,
                                OrganizationalContextInterface $context = null)
    {
        parent::__construct($hierarchy, $registry, $roleClassname, $cache);
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUniqueId(array $roleNames)
    {
        $id = parent::getUniqueId($roleNames);

        if (null !== $this->context && null !== ($org = $this->context->getCurrentOrganization())) {
            $id = ($org->isUserOrganization() ? 'user' : $org->getId()).'__'.$id;
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRoleName($role)
    {
        $list = parent::formatRoleName($role);
        $list[0] = OrganizationalUtil::format($role->getRole());

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoleSuffix($role)
    {
        return null !== $role
            ? OrganizationalUtil::getSuffix($role->getRole())
            : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanRoleNames(array $roles)
    {
        foreach ($roles as &$role) {
            $role = OrganizationalUtil::format($role);
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatCleanedRoleName($name)
    {
        return OrganizationalUtil::format($name);
    }
}
