<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Validator\Constraints;

use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Validator\Constraints\Permission;
use Fxp\Component\Security\Validator\Constraints\PermissionValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionValidatorTest extends TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionManager;

    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var PermissionValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $this->validator = new PermissionValidator($this->permissionManager);
    }

    public function testValidateWithEmptyClassAndField()
    {
        $constraint = new Permission();
        $perm = new MockPermission();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidateWithEmptyField()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setClass(MockObject::class);

        $this->permissionManager->expects($this->once())
            ->method('hasConfig')
            ->with(MockObject::class)
            ->willReturn(true);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidateWithInvalidClassName()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setClass('FooBar');

        $this->permissionManager->expects($this->never())
            ->method('hasConfig');

        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('permission.class.invalid')
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('atPath')
            ->with('class')
            ->willReturn($vb);

        $vb->expects($this->at(1))
            ->method('setParameter')
            ->with('%class_property%', 'class')
            ->willReturn($vb);

        $vb->expects($this->at(2))
            ->method('setParameter')
            ->with('%class%', 'FooBar')
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidateWithNonManagedClass()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setClass(MockObject::class);

        $this->permissionManager->expects($this->once())
            ->method('hasConfig')
            ->with(MockObject::class)
            ->willReturn(false);

        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('permission.class.not_managed')
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('atPath')
            ->with('class')
            ->willReturn($vb);

        $vb->expects($this->at(1))
            ->method('setParameter')
            ->with('%class_property%', 'class')
            ->willReturn($vb);

        $vb->expects($this->at(2))
            ->method('setParameter')
            ->with('%class%', MockObject::class)
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidateFieldWithEmptyClass()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setField('name');

        $this->permissionManager->expects($this->never())
            ->method('hasConfig');

        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('permission.class.required')
            ->willReturn($vb);

        $vb->expects($this->at(0))
            ->method('atPath')
            ->with('class')
            ->willReturn($vb);

        $vb->expects($this->at(1))
            ->method('setParameter')
            ->with('%field_property%', 'field')
            ->willReturn($vb);

        $vb->expects($this->at(2))
            ->method('setParameter')
            ->with('%field%', 'name')
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidateFieldWithInvalidField()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setClass(MockObject::class);
        $perm->setField('name2');

        $this->permissionManager->expects($this->once())
            ->method('hasConfig')
            ->with(MockObject::class)
            ->willReturn(true);

        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('permission.field.invalid')
            ->willReturn($vb);

        $vb->expects($this->at(0))
            ->method('atPath')
            ->with('field')
            ->willReturn($vb);

        $vb->expects($this->at(1))
            ->method('setParameter')
            ->with('%class_property%', 'class')
            ->willReturn($vb);

        $vb->expects($this->at(2))
            ->method('setParameter')
            ->with('%field_property%', 'field')
            ->willReturn($vb);

        $vb->expects($this->at(3))
            ->method('setParameter')
            ->with('%class%', MockObject::class)
            ->willReturn($vb);

        $vb->expects($this->at(4))
            ->method('setParameter')
            ->with('%field%', 'name2')
            ->willReturn($vb);

        $vb->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }

    public function testValidate()
    {
        $constraint = new Permission();
        $perm = new MockPermission();
        $perm->setClass(MockObject::class);
        $perm->setField('name');

        $this->permissionManager->expects($this->once())
            ->method('hasConfig')
            ->with(MockObject::class)
            ->willReturn(true);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->initialize($this->context);
        $this->validator->validate($perm, $constraint);
    }
}
