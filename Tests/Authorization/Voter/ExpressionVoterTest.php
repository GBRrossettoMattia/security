<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Authorization\Voter;

use Fxp\Component\Security\Authorization\Voter\ExpressionVoter;
use Fxp\Component\Security\Expression\ExpressionVariableStorage;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ExpressionVoterTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var ExpressionLanguage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $expressionLanguage;

    /**
     * @var AuthenticationTrustResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trustResolver;

    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var RoleInterface
     */
    protected $orgRole;

    /**
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var ExpressionVariableStorage
     */
    protected $variableStorage;

    /**
     * @var ExpressionVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->expressionLanguage = $this->getMockBuilder(ExpressionLanguage::class)->disableOriginalConstructor()->getMock();
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->orgRole = $this->getMockBuilder(RoleInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->variableStorage = new ExpressionVariableStorage(
            [
                'organizational_context' => $this->context,
                'organizational_role' => $this->orgRole,
            ],
            $this->sidManager
        );
        $this->variableStorage->add('trust_resolver', $this->trustResolver);

        $this->dispatcher->addSubscriber($this->variableStorage);

        $this->voter = new ExpressionVoter(
            $this->dispatcher,
            $this->expressionLanguage
        );
    }

    public function testAddExpressionLanguageProvider()
    {
        /* @var ExpressionFunctionProviderInterface $provider */
        $provider = $this->getMockBuilder(ExpressionFunctionProviderInterface::class)->getMock();

        $this->expressionLanguage->expects($this->once())
            ->method('registerProvider')
            ->with($provider);

        $this->voter->addExpressionLanguageProvider($provider);
    }

    public function testWithoutExpression()
    {
        $res = $this->voter->vote($this->token, null, [42]);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $res);
    }

    public function getExpressionResults()
    {
        return [
            [VoterInterface::ACCESS_GRANTED, true],
            [VoterInterface::ACCESS_DENIED, false],
        ];
    }

    /**
     * @dataProvider getExpressionResults
     *
     * @param int  $resultVoter      The result of voter
     * @param bool $resultExpression The result of expression
     */
    public function testWithExpression($resultVoter, $resultExpression)
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity(Role::class, AuthenticatedVoter::IS_AUTHENTICATED_FULLY),
        ];

        $this->sidManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids);

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) use ($resultExpression) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(8, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayHasKey('organizational_context', $variables);
                $this->assertArrayHasKey('organizational_role', $variables);
                $this->assertArrayNotHasKey('request', $variables);

                $this->assertEquals(['ROLE_USER'], $variables['roles']);

                return $resultExpression;
            });

        $expression = new Expression('"ROLE_USER" in roles');
        $res = $this->voter->vote($this->token, null, [$expression]);

        $this->assertSame($resultVoter, $res);
    }

    public function testWithoutSecurityIdentityManagerButWithRequestSubject()
    {
        $this->token->expects($this->once())
            ->method('getRoles')
            ->willReturn([new Role('ROLE_USER')]);

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(7, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayNotHasKey('organizational_context', $variables);
                $this->assertArrayNotHasKey('organizational_role', $variables);
                $this->assertArrayHasKey('request', $variables);

                $this->assertEquals(['ROLE_USER'], $variables['roles']);

                return true;
            });

        $variableStorage = new ExpressionVariableStorage();
        $variableStorage->add('trust_resolver', $this->trustResolver);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($variableStorage);

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $expression = new Expression('"ROLE_USER" in roles');
        $voter = new ExpressionVoter(
            $dispatcher,
            $this->expressionLanguage
        );
        $res = $voter->vote($this->token, $request, [$expression]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $res);
    }
}
