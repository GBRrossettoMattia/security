<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException as BaseAccessDeniedException;

/**
 * Base AccessDeniedException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AccessDeniedException extends BaseAccessDeniedException implements ExceptionInterface
{
}
