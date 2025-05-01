<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    private RouterInterface $router;
    private RequestStack $requestStack;

    public function __construct(
        RouterInterface   $router,
        RequestStack $requestStack
    )
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            $request = $event->getRequest();

            if (!str_starts_with($request->getPathInfo(), '/api')) {
                $this->requestStack->getSession()
                    ->getFlashBag()
                    ->add('error', 'Access Denied. You do not have permission to access that page.');

                $url = $this->router->generate('app_conference_index');

                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
