<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class PermissionContexts
{
    /**
     * The PermissionContexts::ROLE context check if the permission
     * can be added on a role.
     *
     * @var string
     */
    const ROLE = 'role';

    /**
     * The PermissionContexts::ORGANIZATION_ROLE context check if the permission
     * can be added on a role of organization.
     *
     * In this case, the Role model must implement Fxp\Component\Security\Model\TraitsOrganizationalInterface
     *
     * @var string
     */
    const ORGANIZATION_ROLE = 'organization_role';

    /**
     * The PermissionContexts::SHARING context check if the permission
     * can be added on a sharing entry.
     *
     * @var string
     */
    const SHARING = 'sharing';
}
