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

use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Organizational\OrganizationalUtil;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Role\Role;

/**
 * RoleSecurityIdentityVoter uses a SecurityIdentityManager to
 * determine the roles granted to the user before voting.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RoleSecurityIdentityVoter extends RoleVoter
{
    /**
     * @var SecurityIdentityManagerInterface
     */
    private $sim;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface $sim    The security identity manager
     * @param string                           $prefix The role prefix
     */
    public function __construct(SecurityIdentityManagerInterface $sim, $prefix = 'ROLE_')
    {
        $this->sim = $sim;
        $this->cacheExec = [];

        parent::__construct($prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractRoles(TokenInterface $token)
    {
        $sids = $this->sim->getSecurityIdentities($token);
        $id = sha1(implode('|', $sids));

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $roles = [];

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $role = OrganizationalUtil::format($sid->getIdentifier());
                $roles[] = new Role($role);
            }
        }

        return $this->cacheExec[$id] = $roles;
    }
}
