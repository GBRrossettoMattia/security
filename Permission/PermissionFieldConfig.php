<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

/**
 * Permission field config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionFieldConfig implements PermissionFieldConfigInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string[]
     */
    protected $operations;

    /**
     * @var string[]
     */
    protected $mappingPermissions;

    /**
     * Constructor.
     *
     * @param string   $field              The field name
     * @param string[] $operations         The permission operations of this field
     * @param string[] $mappingPermissions The map of alias permission and real permission
     */
    public function __construct($field,
                                array $operations = [],
                                array $mappingPermissions = [])
    {
        $this->field = $field;
        $this->operations = array_values($operations);
        $this->mappingPermissions = $mappingPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOperation($operation)
    {
        return in_array($this->getMappingPermission($operation), $this->operations);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermission($aliasPermission)
    {
        return isset($this->mappingPermissions[$aliasPermission])
            ? $this->mappingPermissions[$aliasPermission]
            : $aliasPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermissions()
    {
        return $this->mappingPermissions;
    }
}
