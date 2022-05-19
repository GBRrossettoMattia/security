<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Expression;

use Fxp\Component\Security\Event\GetExpressionVariablesEvent;

/**
 * Expression variable storage interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface ExpressionVariableStorageInterface
{
    /**
     * Add a variable in the expression language evaluate variables.
     *
     * @param string $name  The name of expression variable
     * @param mixed  $value The value of expression variable
     *
     * @return self
     */
    public function add($name, $value);

    /**
     * Remove a variable.
     *
     * @param string $name The variable name
     *
     * @return self
     */
    public function remove($name);

    /**
     * Check if the variable is defined.
     *
     * @param string $name The variable name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Get the value of variable.
     *
     * @param string $name The variable name
     *
     * @return mixed|null
     */
    public function get($name);

    /**
     * Get all variables.
     *
     * @return array
     */
    public function getAll();

    /**
     * Inject the expression variables in event.
     *
     * @param GetExpressionVariablesEvent $event The event
     */
    public function inject(GetExpressionVariablesEvent $event);
}
