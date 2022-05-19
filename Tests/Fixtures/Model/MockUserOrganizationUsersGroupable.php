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

use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockUserOrganizationUsersGroupable extends MockUserOrganizationUsers implements GroupableInterface
{
    /**
     * @var array
     */
    protected $groups = [];

    /**
     * Add a group.
     *
     * @param GroupInterface $group The group
     */
    public function addGroup(GroupInterface $group)
    {
        $this->groups[$group->getName()] = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($name)
    {
        return isset($this->groups[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return array_values($this->groups);
    }
}
