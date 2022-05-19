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

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSecurityIdentity extends AbstractBaseIdentity implements SecurityIdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public function equals(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof self || $this->getType() !== $identity->getType()) {
            return false;
        }

        return $this->getIdentifier() === $identity->getIdentifier();
    }

    /**
     * A textual representation of this security identity.
     *
     * This is not used for equality comparison, but only for debugging.
     *
     * @return string
     */
    public function __toString()
    {
        $name = (new \ReflectionClass($this))->getShortName();

        return sprintf('%s(%s)', $name, $this->getIdentifier());
    }
}
