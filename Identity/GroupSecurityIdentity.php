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
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class GroupSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a group security identity from a GroupInterface.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public static function fromAccount(GroupInterface $group)
    {
        return new self(ClassUtils::getClass($group), $group->getGroup());
    }

    /**
     * Creates a group security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @return self[]
     *
     * @throws InvalidArgumentException When the user class not implements "Fxp\Component\Security\Model\Traits\GroupableInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof GroupableInterface) {
            $sids = [];
            $groups = $user->getGroups();

            foreach ($groups as $group) {
                $sids[] = self::fromAccount($group);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Fxp\Component\Security\Model\Traits\GroupableInterface"');
    }
}
