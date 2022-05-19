<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Firewall;

use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Inject the host role in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnonymousRoleListener extends AbstractRoleListener
{
    /**
     * @var AuthenticationTrustResolverInterface
     */
    protected $trustResolver;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface     $sidManager    The security identity manager
     * @param array                                $config        The config
     * @param AuthenticationTrustResolverInterface $trustResolver The authentication trust resolver
     * @param TokenStorageInterface                $tokenStorage  The token storage
     */
    public function __construct(SecurityIdentityManagerInterface $sidManager,
                                array $config,
                                AuthenticationTrustResolverInterface $trustResolver,
                                TokenStorageInterface $tokenStorage)
    {
        parent::__construct($sidManager, $config);

        $this->trustResolver = $trustResolver;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if ($this->isEnabled() && $this->hasRole() && $this->isAnonymous()) {
            $this->sidManager->addSpecialRole(new Role($this->config['role']));
        }
    }

    /**
     * Check if the anonymous role is present in config.
     *
     * @return bool
     */
    private function hasRole()
    {
        return isset($this->config['role'])
            && is_string($this->config['role'])
            && 0 === strpos($this->config['role'], 'ROLE_');
    }

    /**
     * Check if the token is a anonymous token.
     *
     * @return bool
     */
    private function isAnonymous()
    {
        $token = $this->tokenStorage->getToken();

        return null === $token
            || $this->trustResolver->isAnonymous($token);
    }
}
