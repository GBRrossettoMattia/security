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

/**
 * Sharing subject config Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SharingSubjectConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the sharing visibility.
     *
     * @return string
     */
    public function getVisibility();
}
