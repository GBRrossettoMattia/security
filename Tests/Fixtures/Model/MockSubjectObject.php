<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Identity\SubjectInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockSubjectObject implements SubjectInterface
{
    /**
     * @var int|string|null
     */
    protected $subjectIdentifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string          $name              The name
     * @param int|string|null $subjectIdentifier The subject identifier
     */
    public function __construct($name, $subjectIdentifier = 42)
    {
        $this->name = $name;
        $this->subjectIdentifier = $subjectIdentifier;
    }

    public function getSubjectIdentifier()
    {
        return $this->subjectIdentifier;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name The name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
