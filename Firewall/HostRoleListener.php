<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Inject the host role in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class HostRoleListener extends AbstractRoleListener
{
    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if ($this->isEnabled()) {
            $hostRole = $this->getHostRole($event);

            if (null !== $hostRole) {
                $this->sidManager->addSpecialRole($hostRole);
            }
        }
    }

    /**
     * Get the host role.
     *
     * @param GetResponseEvent $event The response event
     *
     * @return Role|null
     */
    protected function getHostRole(GetResponseEvent $event)
    {
        $hostRole = null;
        $hostname = $event->getRequest()->getHttpHost();

        foreach ($this->config as $hostPattern => $role) {
            if ($this->isValid($hostPattern, $hostname)) {
                $hostRole = new Role($role);
                break;
            }
        }

        return $hostRole;
    }

    /**
     * Check if the hostname matching with the host pattern.
     *
     * @param string $pattern  The shell pattern or regex pattern starting and ending with a slash
     * @param string $hostname The host name
     *
     * @return bool
     */
    private function isValid($pattern, $hostname)
    {
        return 0 === strpos($pattern, '/') && (1 + strrpos($pattern, '/')) === strlen($pattern)
            ? (bool) preg_match($pattern, $hostname)
            : fnmatch($pattern, $hostname);
    }
}
