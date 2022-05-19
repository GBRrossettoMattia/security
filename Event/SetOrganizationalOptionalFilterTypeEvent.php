<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The event of set optional filter type by the organizational context.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SetOrganizationalOptionalFilterTypeEvent extends Event
{
    /**
     * @var string
     */
    protected $filterType;

    /**
     * Constructor.
     *
     * @param string $filterType The optional filter type
     */
    public function __construct($filterType)
    {
        $this->filterType = $filterType;
    }

    /**
     * Get the optional filter type.
     *
     * @return string
     */
    public function getOptionalFilterType()
    {
        return $this->filterType;
    }
}
