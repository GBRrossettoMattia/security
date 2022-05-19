<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Organizational;

use Fxp\Component\Security\Event\SetCurrentOrganizationEvent;
use Fxp\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Fxp\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Fxp\Component\Security\Exception\RuntimeException;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\OrganizationalContextEvents;
use Fxp\Component\Security\OrganizationalTypes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Organizational Context.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalContext implements OrganizationalContextInterface
{
    /**
     * @var string
     */
    protected $optionalFilterType = OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EventDispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * @var OrganizationInterface|false|null
     */
    protected $organization;

    /**
     * @var OrganizationUserInterface|null
     */
    protected $organizationUser;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface         $tokenStorage The token storage
     * @param EventDispatcherInterface|null $dispatcher   The event dispatcher
     */
    public function __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $dispatcher = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentOrganization($organization)
    {
        $this->getToken('organization', $organization instanceof OrganizationInterface);

        if (null === $organization || false === $organization || $organization instanceof OrganizationInterface) {
            $old = $this->organization;
            $this->organization = $organization;
            $this->dispatch(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION,
                SetCurrentOrganizationEvent::class, $organization, $old
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentOrganization()
    {
        if (null === $this->organization) {
            $token = $this->tokenStorage->getToken();
            $user = null !== $token ? $token->getUser() : null;

            if ($user instanceof UserInterface && $user instanceof OrganizationalInterface) {
                $org = $user->getOrganization();

                if ($org instanceof OrganizationInterface) {
                    return $org;
                }
            }
        }

        return false !== $this->organization ? $this->organization : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentOrganizationUser($organizationUser)
    {
        $token = $this->getToken('organization user', $organizationUser instanceof OrganizationUserInterface);
        $user = null !== $token ? $token->getUser() : null;
        $this->organizationUser = null;
        $org = null;

        if ($user instanceof UserInterface && $organizationUser instanceof OrganizationUserInterface
                && $user->getUsername() === $organizationUser->getUser()->getUsername()) {
            $old = $this->organizationUser;
            $this->organizationUser = $organizationUser;
            $org = $organizationUser->getOrganization();
            $this->dispatch(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION_USER,
                SetCurrentOrganizationUserEvent::class, $organizationUser, $old
            );
        }
        $this->setCurrentOrganization($org);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentOrganizationUser()
    {
        return $this->organizationUser;
    }

    /**
     * {@inheritdoc}
     */
    public function isOrganization()
    {
        return null !== $this->getCurrentOrganization()
            && !$this->getCurrentOrganization()->isUserOrganization()
            && null !== $this->getCurrentOrganizationUser();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionalFilterType($type)
    {
        $old = $this->optionalFilterType;
        $this->optionalFilterType = $type;
        $this->dispatch(OrganizationalContextEvents::SET_OPTIONAL_FILTER_TYPE,
            SetOrganizationalOptionalFilterTypeEvent::class, $type, $old);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionalFilterType()
    {
        return $this->optionalFilterType;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptionalFilterType($type)
    {
        return is_string($this->optionalFilterType) && $type === $this->optionalFilterType;
    }

    /**
     * Get the token.
     *
     * @param string $type          The type name
     * @param bool   $tokenRequired Check if the token is required
     *
     * @return TokenInterface
     *
     * @throws
     */
    protected function getToken($type, $tokenRequired = true)
    {
        $token = $this->tokenStorage->getToken();

        if ($tokenRequired && null === $token) {
            throw new RuntimeException(sprintf('The current %s cannot be added in security token because the security token is empty', $type));
        }

        return $token;
    }

    /**
     * Dispatch the event.
     *
     * @param string                   $eventName  The event name
     * @param string                   $eventClass The class name of event
     * @param object|false|string|null $subject    The event subject
     * @param object|false|string|null $oldSubject The old event subject
     */
    protected function dispatch($eventName, $eventClass, $subject, $oldSubject)
    {
        if (null !== $this->dispatcher && $oldSubject !== $subject) {
            $this->dispatcher->dispatch($eventName, new $eventClass($subject));
        }
    }
}
