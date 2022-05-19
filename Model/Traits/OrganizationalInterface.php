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
 * Interface to indicate that the model is linked with an organization.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface OrganizationalInterface
{
    /**
     * Get the organization.
     *
     * @return OrganizationInterface|null
     */
    public function getOrganization();

    /**
     * Get the organization id.
     *
     * @return int|string|null
     */
    public function getOrganizationId();
}
