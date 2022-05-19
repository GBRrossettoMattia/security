<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * PermissionExceptionListener display specific page when permission exception is throw.
 *
 * The onKernelException method must be connected to the kernel.exception event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionExceptionListener
{
    /**
     * Method for a dependency injection.
     *
     * @param GetResponseForExceptionEvent $event A event object
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $event->setException(new AccessDeniedHttpException($exception->getMessage(), $exception));
    }
}
