<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Authorization\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Define some ExpressionLanguage functions.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IsBasicAuthProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('is_basic_auth', function () {
                $class = '\\'.UsernamePasswordToken::class;

                return sprintf('$token && $token instanceof %1$s && !$trust_resolver->isAnonymous($token)', $class);
            }, function (array $variables) {
                return isset($variables['token'])
                    && $variables['token'] instanceof UsernamePasswordToken
                    && !$variables['trust_resolver']->isAnonymous($variables['token']);
            }),
        ];
    }
}
