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
 * Interface for events of security identities.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface CacheSecurityIdentityListenerInterface
{
    /**
     * Get the cache id for the event security identities.
     *
     * @return string
     */
    public function getCacheId();
}
