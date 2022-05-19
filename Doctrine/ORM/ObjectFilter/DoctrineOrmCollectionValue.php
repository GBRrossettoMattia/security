<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\ObjectFilter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fxp\Component\Security\ObjectFilter\ObjectFilterVoterInterface;

/**
 * The Doctrine Orm Collection Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DoctrineOrmCollectionValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value)
    {
        return $value instanceof Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return new ArrayCollection();
    }
}
