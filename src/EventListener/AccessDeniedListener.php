<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    private $flashBag;
    private $router;

    public function __construct(
        FlashBagInterface $flashBag,
        RouterInterface $router
    )
    {
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            $this->flashBag->add('error', 'Access Denied. You do not have permission to access this page.');

            $url = $this->router->generate('app_conference_index');

            $event->setResponse(new RedirectResponse($url));
        }
    }
}