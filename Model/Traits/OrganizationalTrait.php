<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Fxp\Component\Security\Model\OrganizationInterface;

/**
 * Trait to indicate that the model is linked with an organization.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait OrganizationalTrait
{
    /**
     * @var OrganizationInterface|null
     */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationId()
    {
        return null !== $this->getOrganization()
            ? $this->getOrganization()->getId()
            : null;
    }
}
