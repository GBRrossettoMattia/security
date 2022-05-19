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

use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Abstract security listener for role.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractRoleListener implements ListenerInterface
{
    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * @param SecurityIdentityManagerInterface $sidManager The security identity manager
     * @param array                            $config     The config
     */
    public function __construct(SecurityIdentityManagerInterface $sidManager, array $config)
    {
        $this->sidManager = $sidManager;
        $this->config = $config;
    }

    /**
     * Set if the listener is enabled.
     *
     * @param bool $enabled The value
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    /**
     * Check if the listener is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
