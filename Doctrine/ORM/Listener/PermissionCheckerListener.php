<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Fxp\Component\Security\Exception\AccessDeniedException;
use Fxp\Component\Security\Token\ConsoleToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionCheckerListener extends AbstractPermissionListener
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * Specifies the list of listened events.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * This method is executed each time doctrine does a flush on an entity manager.
     *
     * @param OnFlushEventArgs $args The event
     *
     * @throws AccessDeniedException When insufficient privilege for called action
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $token = $this->getTokenStorage()->getToken();

        if (!$this->getPermissionManager()->isEnabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();
        $createEntities = $uow->getScheduledEntityInsertions();
        $updateEntities = $uow->getScheduledEntityUpdates();
        $deleteEntities = $uow->getScheduledEntityDeletions();

        $this->postResetPermissions = array_merge($createEntities, $updateEntities, $deleteEntities);
        $this->getPermissionManager()->preloadPermissions($this->postResetPermissions);

        $this->checkAllScheduledByAction($createEntities, 'create');
        $this->checkAllScheduledByAction($updateEntities, 'update');
        $this->checkAllScheduledByAction($deleteEntities, 'delete');
    }

    /**
     * Set the authorization checker.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker
     *
     * @return self
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authChecker = $authorizationChecker;

        return $this;
    }

    /**
     * Gets security authorization checker.
     *
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        $this->init();

        return $this->authChecker;
    }

    /**
     * Check all scheduled objects by action type.
     *
     * @param object[] $objects The objects
     * @param string   $action  The action name
     */
    protected function checkAllScheduledByAction(array $objects, $action)
    {
        foreach ($objects as $object) {
            if (!$this->getAuthorizationChecker()->isGranted('perm_'.$action, $object)) {
                throw new AccessDeniedException('Insufficient privilege to '.$action.' the entity');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getInitProperties()
    {
        return [
            'tokenStorage' => 'setTokenStorage',
            'authChecker' => 'setAuthorizationChecker',
            'permissionManager' => 'setPermissionManager',
        ];
    }
}
