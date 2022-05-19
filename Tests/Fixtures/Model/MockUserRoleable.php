<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\RoleableTrait;
use Fxp\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockUserRoleable implements UserInterface, RoleableInterface
{
    use RoleableTrait;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 50;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return 'salt';
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return 'user.test';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // do nothing
    }
}
