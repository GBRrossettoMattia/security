<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Authorization\Expression;

use Fxp\Component\Security\Authorization\Expression\IsGrantedProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IsGrantedProviderTest extends TestCase
{
    public function testIsBasicAuth()
    {
        $object = new \stdClass();
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();

        $authChecker->expects($this->once())
            ->method('isGranted')
            ->with('perm_view', $object)
            ->willReturn(true);

        $expressionLanguage = new ExpressionLanguage(null, [new IsGrantedProvider()]);
        $variables = [
            'object' => $object,
            'auth_checker' => $authChecker,
        ];

        $this->assertTrue($expressionLanguage->evaluate('is_granted("perm_view", object)', $variables));

        $compiled = '$auth_checker && $auth_checker->isGranted("perm_view", $object)';
        $this->assertEquals($compiled, $expressionLanguage->compile('is_granted("perm_view", object)', ['object']));
    }
}
