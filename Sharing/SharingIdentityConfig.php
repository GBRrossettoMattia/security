<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

/**
 * Sharing identity config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingIdentityConfig implements SharingIdentityConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var bool
     */
    protected $roleable;

    /**
     * @var bool
     */
    protected $permissible;

    /**
     * Constructor.
     *
     * @param string      $type        The type, typically, this is the PHP class name
     * @param string|null $alias       The alias of identity type
     * @param bool        $roleable    Check if the identity can be use the roles
     * @param bool        $permissible Check if the identity can be use the permissions
     */
    public function __construct($type, $alias = null, $roleable = false, $permissible = false)
    {
        $this->type = $type;
        $this->alias = $this->buildAlias($type, $alias);
        $this->roleable = $roleable;
        $this->permissible = $permissible;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoleable()
    {
        return $this->roleable;
    }

    /**
     * {@inheritdoc}
     */
    public function isPermissible()
    {
        return $this->permissible;
    }

    /**
     * Build the alias.
     *
     * @param string      $classname The class name
     * @param string|null $alias     The alias
     *
     * @return string
     */
    private function buildAlias($classname, $alias)
    {
        return null !== $alias
            ? $alias
            : strtolower(substr(strrchr($classname, '\\'), 1));
    }
}
