<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\ObjectFilter;

/**
 * The Mixed Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MixedValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return is_array($value)
            ? []
            : null;
    }
}
