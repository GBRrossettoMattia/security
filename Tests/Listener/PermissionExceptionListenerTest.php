<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Listener;

use Fxp\Component\Security\Listener\PermissionExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionExceptionListenerTest extends TestCase
{
    /**
     * @var HttpKernelInterface
     */
    protected $kernel;

    protected $request;

    protected function setUp()
    {
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
    }

    public function testKernelException()
    {
        $e = new \Exception('Test');
        $event = new GetResponseForExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::SUB_REQUEST,
            $e
        );
        $listener = new PermissionExceptionListener();

        $listener->onKernelException($event);

        $this->assertSame($e, $event->getException());
    }

    public function testKernelExceptionWithAccessDeniedException()
    {
        $e = new AccessDeniedException('Test');
        $event = new GetResponseForExceptionEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::SUB_REQUEST,
            $e
        );
        $listener = new PermissionExceptionListener();

        $listener->onKernelException($event);

        $this->assertNotSame($e, $event->getException());
        $this->assertInstanceOf(AccessDeniedHttpException::class, $event->getException());
        $this->assertInstanceOf(AccessDeniedException::class, $event->getException()->getPrevious());
        $this->assertSame($e, $event->getException()->getPrevious());
    }
}
