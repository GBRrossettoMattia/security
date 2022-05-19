<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Exception;

/**
 * UnexpectedTypeException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class UnexpectedTypeException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * Constructor.
     *
     * @param mixed  $value        The value
     * @param string $expectedType The expected type
     */
    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
