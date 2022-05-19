<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Validator\Constraints;

use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\Permission\PermissionProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionValidator extends ConstraintValidator
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface $permissionManager The permission manager
     * @param PropertyAccessorInterface  $propertyAccessor  The property access
     */
    public function __construct(PermissionManagerInterface $permissionManager,
                                PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->permissionManager = $permissionManager;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var Permission $constraint */
        $class = $this->propertyAccessor->getValue($value, $constraint->propertyClass);
        $field = $this->propertyAccessor->getValue($value, $constraint->propertyField);

        $this->validateClass($constraint, $class);
        $this->validateField($constraint, $class, $field);
    }

    /**
     * Validate the class.
     *
     * @param Permission  $constraint The permission constraint
     * @param string|null $class      The class
     */
    private function validateClass(Permission $constraint, $class)
    {
        if (null !== $class && PermissionProviderInterface::CONFIG_CLASS !== $class) {
            if (!class_exists($class)) {
                $this->context->buildViolation($constraint->invalidClassMessage)
                    ->atPath($constraint->propertyClass)
                    ->setParameter('%class_property%', $constraint->propertyClass)
                    ->setParameter('%class%', $class)
                    ->addViolation();
            } elseif (!$this->permissionManager->hasConfig($class)) {
                $this->context->buildViolation($constraint->classNotManagedMessage)
                    ->atPath($constraint->propertyClass)
                    ->setParameter('%class_property%', $constraint->propertyClass)
                    ->setParameter('%class%', $class)
                    ->addViolation();
            }
        }
    }

    /**
     * Validate the field.
     *
     * @param Permission  $constraint The permission constraint
     * @param string|null $class      The class
     * @param string|null $field      The field
     */
    private function validateField(Permission $constraint, $class, $field)
    {
        if (null !== $field && PermissionProviderInterface::CONFIG_FIELD !== $field) {
            if (null === $class) {
                $this->context->buildViolation($constraint->requiredClassMessage)
                    ->atPath($constraint->propertyClass)
                    ->setParameter('%field_property%', $constraint->propertyField)
                    ->setParameter('%field%', $field)
                    ->addViolation();
            } elseif (!property_exists($class, $field)) {
                $this->context->buildViolation($constraint->invalidFieldMessage)
                    ->atPath($constraint->propertyField)
                    ->setParameter('%class_property%', $constraint->propertyClass)
                    ->setParameter('%field_property%', $constraint->propertyField)
                    ->setParameter('%class%', $class)
                    ->setParameter('%field%', $field)
                    ->addViolation();
            }
        }
    }
}
