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
use Fxp\Component\Security\Exception\InvalidArgumentException;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class RoleSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Constructor.
     *
     * @param string $identifier The identifier
     * @param string $type       The type
     *
     * @throws InvalidArgumentException When the identifier is empty
     * @throws InvalidArgumentException When the type is empty
     */
    public function __construct($type, $identifier)
    {
        parent::__construct($type, $identifier);

        $this->type = Role::class === $this->type ? 'role' : $this->type;
    }

    /**
     * Creates a role security identity from a RoleInterface.
     *
     * @param Role $role The role
     *
     * @return self
     */
    public static function fromAccount(Role $role)
    {
        return new self(ClassUtils::getClass($role), $role->getRole());
    }

    /**
     * Creates a role security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @return self[]
     *
     * @throws InvalidArgumentException When the user class not implements "Fxp\Component\Security\Model\Traits\RoleableInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof RoleableInterface) {
            $sids = [];
            $roles = $user->getRoles();

            foreach ($roles as $role) {
                $role = $role instanceof Role ? $role : new Role($role);
                $sids[] = self::fromAccount($role);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Fxp\Component\Security\Model\Traits\RoleableInterface"');
    }
}
