<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Model\Traits;

use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalOptionalTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalOptionalTraitTest extends TestCase
{
    public function testModel()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        /* @var OrganizationalOptionalTrait $model */
        $model = $this->getMockForTrait(OrganizationalOptionalTrait::class);
        $model->setOrganization($org);

        $this->assertSame($org, $model->getOrganization());

        $model->setOrganization(null);
        $this->assertNull($model->getOrganization());
    }
}
