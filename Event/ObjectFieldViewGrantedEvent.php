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

use Fxp\Component\Security\Permission\FieldVote;

/**
 * The object field view granted event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ObjectFieldViewGrantedEvent extends AbstractViewGrantedEvent
{
    /**
     * @var FieldVote
     */
    protected $fieldVote;

    /**
     * Constructor.
     *
     * @param FieldVote $fieldVote The permission field vote
     */
    public function __construct(FieldVote $fieldVote)
    {
        parent::__construct($this->validateFieldVoteSubject($fieldVote));

        $this->fieldVote = $fieldVote;
    }

    /**
     * Get the permission field vote.
     *
     * @return FieldVote
     */
    public function getFieldVote()
    {
        return $this->fieldVote;
    }
}
