<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The get expression variables event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GetExpressionVariablesEvent extends Event
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var array<string, mixed>
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param TokenInterface       $token     The token
     * @param array<string, mixed> $variables The variables
     */
    public function __construct(TokenInterface $token, array $variables = [])
    {
        $this->token = $token;
        $this->variables = $variables;
    }

    /**
     * Get the token.
     *
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Add variables in the expression language evaluate variables.
     *
     * @param array<string, mixed> $variables The variables
     */
    public function addVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->addVariable($name, $value);
        }
    }

    /**
     * Add a variable in the expression language evaluate variables.
     *
     * @param string $name  The name of expression variable
     * @param mixed  $value The value of expression variable
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Get the variables.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
