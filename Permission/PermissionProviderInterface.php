<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Model\PermissionInterface;

/**
 * Permission provider Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionProviderInterface
{
    const CONFIG_CLASS = '_config_class';

    const CONFIG_FIELD = '_config_field';

    /**
     * Get all permissions of roles.
     *
     * @param string[] $roles The roles
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(array $roles);

    /**
     * Get the permissions by subject.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string|null $subject  The subject instance or classname
     * @param string[]|string|null                                  $contexts The permission contexts
     *
     * @return PermissionInterface[]
     */
    public function getPermissionsBySubject($subject = null, $contexts = null);

    /**
     * Get the config permissions.
     *
     * @param string[]|string|null $contexts The permission contexts
     *
     * @return PermissionInterface[]
     */
    public function getConfigPermissions($contexts = null);

    /**
     * Get the class name of association field.
     *
     * @param PermissionConfigInterface $config The permission config
     *
     * @return string|null
     */
    public function getMasterClass(PermissionConfigInterface $config);
}
