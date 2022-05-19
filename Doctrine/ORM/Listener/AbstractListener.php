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

use Doctrine\Common\EventSubscriber;
use Fxp\Component\Security\Exception\SecurityException;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Abstract doctrine listener class.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractListener implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Set the token storage.
     *
     * @param TokenStorageInterface $tokenStorage The token storage
     *
     * @return self
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Gets security token storage.
     *
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        $this->init();

        return $this->tokenStorage;
    }

    /**
     * Set the permission manager.
     *
     * @param PermissionManagerInterface $permissionManager The permission manager
     *
     * @return self
     */
    public function setPermissionManager(PermissionManagerInterface $permissionManager)
    {
        $this->permissionManager = $permissionManager;

        return $this;
    }

    /**
     * Get the Permission Manager.
     *
     * @return PermissionManagerInterface
     */
    public function getPermissionManager()
    {
        $this->init();

        return $this->permissionManager;
    }

    /**
     * Init listener.
     */
    protected function init()
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the "%s" class';

            foreach ($this->getInitProperties() as $property => $setterMethod) {
                if (null === $this->$property) {
                    throw new SecurityException(sprintf($msg, $setterMethod, get_class($this)));
                }
            }

            $this->initialized = true;
        }
    }

    /**
     * Get the map of properties and methods required on the init.
     *
     * @return array
     */
    abstract protected function getInitProperties();
}
