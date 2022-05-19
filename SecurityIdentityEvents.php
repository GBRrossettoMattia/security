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
final class SecurityIdentityEvents
{
    /**
     * The SecurityIdentityEvents::RETRIEVAL_PRE event occurs before the retrieval of
     * all security identities.
     *
     * @Event("Fxp\Component\Security\Event\PreSecurityIdentityEvent")
     *
     * @var string
     */
    const RETRIEVAL_PRE = 'fxp_security.security_identity_retrieval.pre';

    /**
     * The SecurityIdentityEvents::RETRIEVAL_ADD event occurs when the security
     * identities are adding.
     *
     * @Event("Fxp\Component\Security\Event\AddSecurityIdentityEvent")
     *
     * @var string
     */
    const RETRIEVAL_ADD = 'fxp_security.security_identity_retrieval.add';

    /**
     * The SecurityIdentityEvents::RETRIEVAL_POST event occurs after the retrieval of
     * all security identities.
     *
     * @Event("Fxp\Component\Security\Event\PostSecurityIdentityEvent")
     *
     * @var string
     */
    const RETRIEVAL_POST = 'fxp_security.security_identity_retrieval.post';
}
