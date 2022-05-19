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
final class OrganizationalContextEvents
{
    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the current organization
     * is added in the organizational context.
     *
     * @Event("Fxp\Component\Security\Event\SetCurrentOrganizationEvent")
     *
     * @var string
     */
    const SET_CURRENT_ORGANIZATION = 'fxp_security.organizational_event.set_current_organization';

    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the current organization user
     * is added in the organizational context.
     *
     * @Event("Fxp\Component\Security\Event\SetCurrentOrganizationUserEvent")
     *
     * @var string
     */
    const SET_CURRENT_ORGANIZATION_USER = 'fxp_security.organizational_event.set_current_organization_user';

    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the optional filter type
     * is changed in the organizational context.
     *
     * @Event("Fxp\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent")
     *
     * @var string
     */
    const SET_OPTIONAL_FILTER_TYPE = 'fxp_security.organizational_event.set_optional_filter_type';
}
