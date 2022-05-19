<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Authorization\Voter;

use Fxp\Component\Security\Model\OrganizationInterface;

/**
 * OrganizationVoter to determine the organization granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationVoter extends AbstractIdentityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getValidType()
    {
        return OrganizationInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPrefix()
    {
        return 'ORG_';
    }
}
