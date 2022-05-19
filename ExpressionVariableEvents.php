<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class ExpressionVariableEvents
{
    /**
     * The GET event occurs when a service try to get the global variables.
     *
     * @Event("Fxp\Component\Security\Event\GetExpressionVariablesEvent")
     *
     * @var string
     */
    const GET = 'fxp_security.expression.get_variables';
}
