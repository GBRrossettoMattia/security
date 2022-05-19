<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Listener;

use Fxp\Component\Security\Event\AddSecurityIdentityEvent;
use Fxp\Component\Security\Identity\GroupSecurityIdentity;
use Fxp\Component\Security\Identity\IdentityUtils;
use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for add group security identity from token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GroupSecurityIdentitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityIdentityEvents::RETRIEVAL_ADD => ['addGroupSecurityIdentities', 0],
        ];
    }

    /**
     * Add group security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addGroupSecurityIdentities(AddSecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = IdentityUtils::merge($sids,
                GroupSecurityIdentity::fromToken($event->getToken())
            );
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
