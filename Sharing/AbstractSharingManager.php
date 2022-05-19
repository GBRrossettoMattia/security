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

use Doctrine\Common\Util\ClassUtils;
use Fxp\Component\Security\Exception\AlreadyConfigurationAliasExistingException;
use Fxp\Component\Security\Exception\SharingIdentityConfigNotFoundException;
use Fxp\Component\Security\Exception\SharingSubjectConfigNotFoundException;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\SharingEvents;
use Fxp\Component\Security\SharingVisibilities;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract sharing manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractSharingManager implements SharingManagerInterface
{
    /**
     * @var SharingProviderInterface
     */
    protected $provider;

    /**
     * @var EventDispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $subjectConfigs = [];

    /**
     * @var array
     */
    protected $identityConfigs = [];

    /**
     * @var array
     */
    protected $identityAliases = [];

    /**
     * @var bool
     */
    protected $identityRoleable = false;

    /**
     * @var bool
     */
    protected $identityPermissible = false;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var array
     */
    protected $cacheSubjectVisibilities = [];

    /**
     * Constructor.
     *
     * @param SharingProviderInterface         $provider        The sharing provider
     * @param SharingSubjectConfigInterface[]  $subjectConfigs  The subject configs
     * @param SharingIdentityConfigInterface[] $identityConfigs The identity configs
     */
    public function __construct(SharingProviderInterface $provider,
                                array $subjectConfigs = [],
                                array $identityConfigs = [])
    {
        $this->provider = $provider;
        $this->provider->setSharingManager($this);

        foreach ($subjectConfigs as $config) {
            $this->addSubjectConfig($config);
        }

        foreach ($identityConfigs as $config) {
            $this->addIdentityConfig($config);
        }
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        if (null !== $this->dispatcher) {
            $name = $this->enabled ? SharingEvents::ENABLED : SharingEvents::DISABLED;
            $this->dispatcher->dispatch($name);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSubjectConfig(SharingSubjectConfigInterface $config)
    {
        $this->subjectConfigs[$config->getType()] = $config;
        unset($this->cacheSubjectVisibilities[$config->getType()]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubjectConfig($class)
    {
        return isset($this->subjectConfigs[ClassUtils::getRealClass($class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectConfig($class)
    {
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasSubjectConfig($class)) {
            throw new SharingSubjectConfigNotFoundException($class);
        }

        return $this->subjectConfigs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectConfigs()
    {
        return array_values($this->subjectConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSharingVisibility(SubjectIdentityInterface $subject)
    {
        return SharingVisibilities::TYPE_NONE !== $this->getSharingVisibility($subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingVisibility(SubjectIdentityInterface $subject)
    {
        $type = $subject->getType();

        if (!array_key_exists($type, $this->cacheSubjectVisibilities)) {
            $sharingVisibility = SharingVisibilities::TYPE_NONE;

            if ($this->hasSubjectConfig($type)) {
                $config = $this->getSubjectConfig($type);
                $sharingVisibility = $config->getVisibility();
            }

            $this->cacheSubjectVisibilities[$type] = $sharingVisibility;
        }

        return $this->cacheSubjectVisibilities[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config)
    {
        if (isset($this->identityAliases[$config->getAlias()])) {
            throw new AlreadyConfigurationAliasExistingException($config->getAlias(), $config->getType());
        }

        $this->identityConfigs[$config->getType()] = $config;
        $this->identityAliases[$config->getAlias()] = $config->getType();

        if ($config->isRoleable()) {
            $this->identityRoleable = true;
        }

        if ($config->isPermissible()) {
            $this->identityPermissible = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityConfig($class)
    {
        return isset($this->identityConfigs[ClassUtils::getRealClass($class)])
        || isset($this->identityAliases[$class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfig($class)
    {
        $class = isset($this->identityAliases[$class])
            ? $this->identityAliases[$class]
            : ClassUtils::getRealClass($class);

        if (!$this->hasIdentityConfig($class)) {
            throw new SharingIdentityConfigNotFoundException($class);
        }

        return $this->identityConfigs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfigs()
    {
        return array_values($this->identityConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityRoleable()
    {
        return $this->identityRoleable;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityPermissible()
    {
        return $this->identityPermissible;
    }
}
