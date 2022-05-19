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

use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Manager to retrieving security identities with caching.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CacheSecurityIdentityManager extends SecurityIdentityManager implements CacheSecurityIdentityManagerInterface
{
    /**
     * @var CacheSecurityIdentityListenerInterface[]|null
     */
    private $cacheIdentityListeners;

    /**
     * @var array
     */
    private $cacheExec = [];

    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache()
    {
        $this->cacheExec = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token = null)
    {
        if (null === $token) {
            return [];
        }

        $id = $this->buildId($token);

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        return $this->cacheExec[$id] = parent::getSecurityIdentities($token);
    }

    /**
     * Build the unique identifier for execution cache.
     *
     * @param TokenInterface $token The token
     *
     * @return string
     */
    protected function buildId(TokenInterface $token)
    {
        $id = spl_object_hash($token);
        $listeners = $this->getCacheIdentityListeners();

        foreach ($listeners as $listener) {
            $id .= '_'.$listener->getCacheId();
        }

        return $id;
    }

    /**
     * Get the cache security identity listeners.
     *
     * @return CacheSecurityIdentityListenerInterface[]
     */
    protected function getCacheIdentityListeners()
    {
        if (null === $this->cacheIdentityListeners) {
            $this->cacheIdentityListeners = [];
            $listeners = $this->dispatcher->getListeners(SecurityIdentityEvents::RETRIEVAL_ADD);

            foreach ($listeners as $listener) {
                $listener = is_array($listener) && count($listener) > 1 ? $listener[0] : $listener;

                if ($listener instanceof CacheSecurityIdentityListenerInterface) {
                    $this->cacheIdentityListeners[] = $listener;
                }
            }
        }

        return $this->cacheIdentityListeners;
    }
}
