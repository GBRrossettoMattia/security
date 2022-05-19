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
use Fxp\Component\Security\ExpressionVariableEvents;
use Fxp\Component\Security\Identity\IdentityUtils;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Variable storage of expression.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ExpressionVariableStorage implements ExpressionVariableStorageInterface, EventSubscriberInterface
{
    /**
     * @var SecurityIdentityManagerInterface|null
     */
    private $sim;

    /**
     * @var array<string, mixed>
     */
    private $variables = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed>                  $variables The expression variables
     * @param SecurityIdentityManagerInterface|null $sim       The security identity manager
     */
    public function __construct(array $variables = [],
                                SecurityIdentityManagerInterface $sim = null)
    {
        $this->sim = $sim;

        foreach ($variables as $name => $value) {
            $this->add($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ExpressionVariableEvents::GET => ['inject', 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function add($name, $value)
    {
        $this->variables[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        unset($this->variables[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->variables[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->has($name)
            ? $this->variables[$name]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->variables;
    }

    /**
     * {@inheritdoc}
     */
    public function inject(GetExpressionVariablesEvent $event)
    {
        $token = $event->getToken();

        $event->addVariables(array_merge($this->variables, [
            'token' => $token,
            'user' => $token->getUser(),
            'roles' => $this->getAllRoles($token),
        ]));
    }

    /**
     * Get all roles.
     *
     * @param TokenInterface $token The token
     *
     * @return string[]
     */
    private function getAllRoles(TokenInterface $token)
    {
        if (null !== $this->sim) {
            $sids = $this->sim->getSecurityIdentities($token);

            return IdentityUtils::filterRolesIdentities($sids);
        }

        return array_map(function (Role $role) {
            return $role->getRole();
        }, $token->getRoles());
    }
}
