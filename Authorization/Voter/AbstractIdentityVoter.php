<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Authorization\Voter;

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Organizational\OrganizationalUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * AbstractIdentityVoter to determine the identities granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractIdentityVoter extends Voter
{
    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sim;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface $sim    The security identity manager
     * @param string|null                      $prefix The attribute prefix
     */
    public function __construct(SecurityIdentityManagerInterface $sim, $prefix = null)
    {
        $this->sim = $sim;
        $this->prefix = null === $prefix ? $this->getDefaultPrefix() : $prefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return is_string($attribute) && 0 === strpos($attribute, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $sids = $this->sim->getSecurityIdentities($token);

        foreach ($sids as $sid) {
            if ($this->isValidIdentity($attribute, $sid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the security identity is valid for this voter.
     *
     * @param string                    $attribute The attribute
     * @param SecurityIdentityInterface $sid       The security identity
     *
     * @return bool
     */
    protected function isValidIdentity($attribute, SecurityIdentityInterface $sid)
    {
        return ($this->getValidType() === $sid->getType() || in_array($this->getValidType(), class_implements($sid->getType())))
            && substr($attribute, strlen($this->prefix)) === OrganizationalUtil::format($sid->getIdentifier());
    }

    /**
     * Get the valid type of identity.
     *
     * @return string
     */
    abstract protected function getValidType();

    /**
     * Get the default prefix.
     *
     * @return string
     */
    abstract protected function getDefaultPrefix();
}
