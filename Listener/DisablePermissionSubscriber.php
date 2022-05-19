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

use Fxp\Component\Security\Event\AbstractEditableSecurityEvent;
use Fxp\Component\Security\Event\AbstractSecurityEvent;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\ReachableRoleEvents;
use Fxp\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for disable/re-enable the permission manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DisablePermissionSubscriber implements EventSubscriberInterface
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permManager;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface $permManager The permission manager
     */
    public function __construct(PermissionManagerInterface $permManager)
    {
        $this->permManager = $permManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityIdentityEvents::RETRIEVAL_PRE => ['disablePermissionManager', -255],
            ReachableRoleEvents::PRE => ['disablePermissionManager', -255],
            SecurityIdentityEvents::RETRIEVAL_POST => ['enablePermissionManager', 255],
            ReachableRoleEvents::POST => ['enablePermissionManager', 255],
        ];
    }

    /**
     * Disable the permission manager.
     *
     * @param AbstractEditableSecurityEvent $event The event
     */
    public function disablePermissionManager(AbstractEditableSecurityEvent $event)
    {
        $event->setPermissionEnabled($this->permManager->isEnabled());
        $this->permManager->setEnabled(false);
    }

    /**
     * Enable the permission manager.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enablePermissionManager(AbstractSecurityEvent $event)
    {
        $this->permManager->setEnabled($event->isPermissionEnabled());
    }
}
