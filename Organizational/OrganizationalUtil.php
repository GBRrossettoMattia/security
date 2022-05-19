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

use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalInterface;

/**
 * Organizational Utils.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class OrganizationalUtil
{
    /**
     * Format the name with the organization name in suffix.
     *
     * @param OrganizationalInterface|object $object The organizational object
     * @param string                         $name   The name
     *
     * @return string
     */
    public static function formatName($object, $name)
    {
        return $object instanceof OrganizationalInterface
            ? static::formatNameWithOrg($name, $object->getOrganization())
            : $name;
    }

    /**
     * Format the name with the organization name in suffix.
     *
     * @param string                     $name         The name
     * @param OrganizationInterface|null $organization The organization
     *
     * @return string
     */
    public static function formatNameWithOrg($name, $organization = null)
    {
        if ($organization instanceof OrganizationInterface
                && false === strpos('__', $name)) {
            $name .= '__'.$organization->getName();
        }

        return $name;
    }

    /**
     * Format the organizational name without suffix.
     *
     * @param string $name The name
     *
     * @return string
     */
    public static function format($name)
    {
        if (false !== ($pos = strrpos($name, '__'))) {
            $name = substr($name, 0, $pos);
        }

        return $name;
    }

    /**
     * Get the organization suffix.
     *
     * @param string $name The name
     *
     * @return string
     */
    public static function getSuffix($name)
    {
        return false !== ($pos = strrpos($name, '__'))
            ? substr($name, $pos)
            : '';
    }
}
