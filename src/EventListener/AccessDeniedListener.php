<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    private FlashBagInterface $flashBag;
    private RouterInterface $router;

    public function __construct(
        FlashBagInterface $flashBag,
        RouterInterface   $router
    )
    {
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            $request = $event->getRequest();

            if (str_starts_with($request->getPathInfo(), '/api')) {
                $event->setResponse(new JsonResponse(null, 403));
            } else {
                $this->flashBag->add('error', 'Access Denied. You do not have permission to access that page.');

                $url = $this->router->generate('app_conference_index');

                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
