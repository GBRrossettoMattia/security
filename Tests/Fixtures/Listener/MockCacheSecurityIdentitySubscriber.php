<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Listener;

use Fxp\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockCacheSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityIdentityEvents::RETRIEVAL_ADD => ['onAddIdentity', 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return 'cache_id';
    }

    /**
     * Action on add identity.
     */
    public function onAddIdentity()
    {
        // do nothing
    }
}
