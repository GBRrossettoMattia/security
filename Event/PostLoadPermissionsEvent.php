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

use Fxp\Component\Security\Identity\SecurityIdentityInterface;

/**
 * The post load permissions event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PostLoadPermissionsEvent extends AbstractLoadPermissionsEvent
{
    /**
     * @var array
     */
    protected $permissionMap;

    /**
     * Constructor.
     *
     * @param SecurityIdentityInterface[] $sids          The security identities
     * @param string[]                    $roles         The role names
     * @param array                       $permissionMap The map of permissions
     */
    public function __construct(array $sids, array $roles, array $permissionMap)
    {
        parent::__construct($sids, $roles);

        $this->permissionMap = $permissionMap;
    }

    /**
     * Set the map of permissions.
     *
     * @param array $permissionMap The map of permissions
     */
    public function setPermissionMap(array $permissionMap)
    {
        $this->permissionMap = $permissionMap;
    }

    /**
     * Get the map of permissions.
     *
     * @return array
     */
    public function getPermissionMap()
    {
        return $this->permissionMap;
    }
}
