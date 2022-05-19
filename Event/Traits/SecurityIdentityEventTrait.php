<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event\Traits;

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The security identity event trait.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait SecurityIdentityEventTrait
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var SecurityIdentityInterface[]
     */
    protected $securityIdentities;

    /**
     * Get the token.
     *
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->securityIdentities;
    }
}
