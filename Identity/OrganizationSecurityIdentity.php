<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Identity;

use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\UserOrganizationUsersInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class OrganizationSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a organization security identity from a OrganizationInterface.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return self
     */
    public static function fromAccount(OrganizationInterface $organization)
    {
        return new self(ClassUtils::getClass($organization), $organization->getName());
    }

    /**
     * Creates a organization security identity from a TokenInterface.
     *
     * @param TokenInterface                      $token         The token
     * @param OrganizationalContextInterface|null $context       The organizational context
     * @param RoleHierarchyInterface|null         $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    public static function fromToken(TokenInterface $token,
                                     OrganizationalContextInterface $context = null,
                                     RoleHierarchyInterface $roleHierarchy = null)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return [];
        }

        return null !== $context
            ? static::getSecurityIdentityForCurrentOrganization($context, $roleHierarchy)
            : static::getSecurityIdentityForAllOrganizations($user, $roleHierarchy);
    }

    /**
     * Get the security identities for all organizations of user.
     *
     * @param UserInterface               $user          The user
     * @param RoleHierarchyInterface|null $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    protected static function getSecurityIdentityForAllOrganizations(UserInterface $user, $roleHierarchy = null)
    {
        $sids = [];

        if ($user instanceof UserOrganizationUsersInterface) {
            foreach ($user->getUserOrganizations() as $userOrg) {
                $sids[] = self::fromAccount($userOrg->getOrganization());
                $sids = array_merge($sids, static::getOrganizationGroups($userOrg));
                $roles = static::getOrganizationUserRoles($userOrg, $roleHierarchy);

                foreach ($roles as $role) {
                    $sids[] = RoleSecurityIdentity::fromAccount($role);
                }
            }
        }

        return $sids;
    }

    /**
     * Get the security identities for the current organization of user.
     *
     * @param OrganizationalContextInterface $context       The organizational context
     * @param RoleHierarchyInterface|null    $roleHierarchy The role hierarchy
     *
     * @return SecurityIdentityInterface[]
     */
    protected static function getSecurityIdentityForCurrentOrganization(OrganizationalContextInterface $context,
                                                                        $roleHierarchy = null)
    {
        $sids = [];
        $org = $context->getCurrentOrganization();
        $userOrg = $context->getCurrentOrganizationUser();
        $orgRoles = [];

        if ($org) {
            $sids[] = self::fromAccount($org);
        }

        if (null !== $userOrg) {
            $sids = array_merge($sids, static::getOrganizationGroups($userOrg));
            $orgRoles = static::getOrganizationUserRoles($userOrg, $roleHierarchy);
        } elseif ($org && $org->isUserOrganization()) {
            $orgRoles = static::getOrganizationRoles($org, $roleHierarchy);
        }

        foreach ($orgRoles as $role) {
            $sids[] = RoleSecurityIdentity::fromAccount($role);
        }

        return $sids;
    }

    /**
     * Get the security identities for organization groups of user.
     *
     * @param OrganizationUserInterface $user The organization user
     *
     * @return GroupSecurityIdentity[]
     */
    protected static function getOrganizationGroups(OrganizationUserInterface $user)
    {
        $sids = [];
        $orgName = $user->getOrganization()->getName();

        if ($user instanceof GroupableInterface) {
            foreach ($user->getGroups() as $group) {
                if ($group instanceof GroupInterface) {
                    $sids[] = new GroupSecurityIdentity(ClassUtils::getClass($group), $group->getGroup().'__'.$orgName);
                }
            }
        }

        return $sids;
    }

    /**
     * Get the organization roles.
     *
     * @param OrganizationInterface       $organization  The organization
     * @param RoleHierarchyInterface|null $roleHierarchy The role hierarchy
     *
     * @return Role[]
     */
    protected static function getOrganizationRoles(OrganizationInterface $organization, $roleHierarchy = null)
    {
        $roles = [];

        if ($organization instanceof RoleableInterface && $organization instanceof OrganizationInterface) {
            $roles = self::buildOrganizationRoles([], $organization);

            if ($roleHierarchy instanceof RoleHierarchyInterface) {
                $roles = $roleHierarchy->getReachableRoles($roles);
            }
        }

        return $roles;
    }

    /**
     * Get the organization roles of user.
     *
     * @param OrganizationUserInterface   $user          The organization user
     * @param RoleHierarchyInterface|null $roleHierarchy The role hierarchy
     *
     * @return Role[]
     */
    protected static function getOrganizationUserRoles(OrganizationUserInterface $user, $roleHierarchy = null)
    {
        $roles = [];

        if ($user instanceof RoleableInterface && $user instanceof OrganizationUserInterface) {
            $org = $user->getOrganization();
            $roles = self::buildOrganizationUserRoles($roles, $user, $org->getName());
            $roles = self::buildOrganizationRoles($roles, $org);

            if ($roleHierarchy instanceof RoleHierarchyInterface) {
                $roles = $roleHierarchy->getReachableRoles($roles);
            }
        }

        return $roles;
    }

    /**
     * Build the organization user roles.
     *
     * @param Role[]            $roles   The roles
     * @param RoleableInterface $user    The organization user
     * @param string            $orgName The organization name
     *
     * @return Role[]
     */
    private static function buildOrganizationUserRoles(array $roles, RoleableInterface $user, $orgName)
    {
        foreach ($user->getRoles() as $role) {
            $roleName = $role instanceof Role ? $role->getRole() : $role;
            $roles[] = new Role($roleName.'__'.$orgName);
        }

        return $roles;
    }

    /**
     * Build the user organization roles.
     *
     * @param Role[]                $roles The roles
     * @param OrganizationInterface $org   The organization of user
     *
     * @return Role[]
     */
    private static function buildOrganizationRoles(array $roles, OrganizationInterface $org)
    {
        $orgName = $org->getName();

        if ($org instanceof RoleableInterface) {
            $existingRoles = [];

            foreach ($roles as $role) {
                $existingRoles[] = $role->getRole();
            }

            foreach ($org->getRoles() as $orgRole) {
                $roleName = $orgRole instanceof Role ? $orgRole->getRole() : $orgRole;

                if (!in_array($roleName, $existingRoles)) {
                    $roles[] = new Role($roleName.'__'.$orgName);
                    $existingRoles[] = $roleName;
                }
            }
        }

        return $roles;
    }
}
