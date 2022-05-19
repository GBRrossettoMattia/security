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
final class SharingEvents
{
    /**
     * The SharingEvents::ENABLED event occurs when the sharing manager is enabled.
     *
     * @var string
     */
    const ENABLED = 'fxp_security.sharing.enabled';

    /**
     * The SharingEvents::ENABLED event occurs when the sharing manager is disabled.
     *
     * @var string
     */
    const DISABLED = 'fxp_security.sharing.disabled';
}
