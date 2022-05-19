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

use Fxp\Component\Security\Event\AddSecurityIdentityEvent;
use Fxp\Component\Security\Event\PostSecurityIdentityEvent;
use Fxp\Component\Security\Event\PreSecurityIdentityEvent;
use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Manager to retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SecurityIdentityManager implements SecurityIdentityManagerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @var AuthenticationTrustResolverInterface
     */
    protected $authenticationTrustResolver;

    /**
     * @var Role[]
     */
    protected $roles = [];

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface             $dispatcher                  The event dispatcher
     * @param RoleHierarchyInterface               $roleHierarchy               The role hierarchy
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver The authentication trust resolver
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                RoleHierarchyInterface $roleHierarchy,
                                AuthenticationTrustResolverInterface $authenticationTrustResolver)
    {
        $this->dispatcher = $dispatcher;
        $this->roleHierarchy = $roleHierarchy;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function addSpecialRole(Role $role)
    {
        if (!isset($this->roles[$role->getRole()])) {
            $this->roles[$role->getRole()] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token = null)
    {
        $sids = [];

        if (null === $token) {
            return $sids;
        }

        // dispatch pre event
        $eventPre = new PreSecurityIdentityEvent($token, $sids);
        $this->dispatcher->dispatch(SecurityIdentityEvents::RETRIEVAL_PRE, $eventPre);

        // add current user and reachable roles
        $sids = $this->addCurrentUser($token, $sids);
        $sids = $this->addReachableRoles($token, $sids);

        // dispatch add event to add custom security identities
        $eventAdd = new AddSecurityIdentityEvent($token, $sids);
        $this->dispatcher->dispatch(SecurityIdentityEvents::RETRIEVAL_ADD, $eventAdd);
        $sids = $eventAdd->getSecurityIdentities();

        // add special roles
        $sids = $this->addSpecialRoles($token, $sids);

        // dispatch post event
        $eventPost = new PostSecurityIdentityEvent($token, $sids, $eventPre->isPermissionEnabled());
        $this->dispatcher->dispatch(SecurityIdentityEvents::RETRIEVAL_POST, $eventPost);

        return $sids;
    }

    /**
     * Add the security identity of current user.
     *
     * @param TokenInterface              $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addCurrentUser(TokenInterface $token, array $sids)
    {
        if (!$token instanceof AnonymousToken) {
            try {
                $sids[] = UserSecurityIdentity::fromToken($token);
            } catch (\InvalidArgumentException $e) {
                // ignore, user has no user security identity
            }
        }

        return $sids;
    }

    /**
     * Add the security identities of reachable roles.
     *
     * @param TokenInterface              $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addReachableRoles(TokenInterface $token, array $sids)
    {
        foreach ($this->roleHierarchy->getReachableRoles($token->getRoles()) as $role) {
            /* @var Role $role */
            $sids[] = RoleSecurityIdentity::fromAccount($role);
        }

        return $sids;
    }

    /**
     * Add the security identities of special roles.
     *
     * @param TokenInterface              $token The token
     * @param SecurityIdentityInterface[] $sids  The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    protected function addSpecialRoles(TokenInterface $token, array $sids)
    {
        $sids = $this->injectSpecialRoles($sids);

        if ($this->authenticationTrustResolver->isFullFledged($token)) {
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_FULLY);
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        } elseif ($this->authenticationTrustResolver->isRememberMe($token)) {
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        } elseif ($this->authenticationTrustResolver->isAnonymous($token)) {
            $sids[] = new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY);
        }

        return $sids;
    }

    /**
     * Inject the special roles.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return SecurityIdentityInterface[]
     */
    private function injectSpecialRoles(array $sids)
    {
        $roles = $this->getRoleNames($sids);

        foreach ($this->roles as $role) {
            if (!in_array($role->getRole(), $roles)) {
                $sids[] = RoleSecurityIdentity::fromAccount($role);
            }
        }

        return $sids;
    }

    /**
     * Get the role names of security identities.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string[]
     */
    private function getRoleNames(array $sids)
    {
        $roles = [];

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $roles[] = $sid->getIdentifier();
            }
        }

        return $roles;
    }
}
