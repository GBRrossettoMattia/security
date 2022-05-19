<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Fxp\Component\Security\Event\Traits\SecurityIdentityEventTrait;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The pre security identity event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PreSecurityIdentityEvent extends AbstractEditableSecurityEvent
{
    use SecurityIdentityEventTrait;

    /**
     * Constructor.
     *
     * @param TokenInterface              $token              The token
     * @param SecurityIdentityInterface[] $securityIdentities The security identities
     */
    public function __construct(TokenInterface $token, array $securityIdentities = [])
    {
        $this->token = $token;
        $this->securityIdentities = $securityIdentities;
    }
}
