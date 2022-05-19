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
use Fxp\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Fxp\Component\Security\Identity\IdentityUtils;
use Fxp\Component\Security\Identity\OrganizationSecurityIdentity;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for add organization security identity from token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var OrganizationalContextInterface
     */
    private $context;

    /**
     * Constructor.
     *
     * @param RoleHierarchyInterface         $roleHierarchy The role hierarchy
     * @param OrganizationalContextInterface $context       The organizational context
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy,
                                OrganizationalContextInterface $context)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityIdentityEvents::RETRIEVAL_ADD => ['addOrganizationSecurityIdentities', 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        $org = $this->context->getCurrentOrganization();

        return null !== $org
            ? 'org'.$org->getId()
            : '';
    }

    /**
     * Add organization security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addOrganizationSecurityIdentities(AddSecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = IdentityUtils::merge($sids,
                OrganizationSecurityIdentity::fromToken($event->getToken(),
                    $this->context, $this->roleHierarchy)
            );
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
