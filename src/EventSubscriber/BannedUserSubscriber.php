<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BannedUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface         $tokenStorage,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly RouterInterface               $router,
        RequestStack                                   $requestStack
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'], true)) {
            return;
        }
        if (!$this->authChecker->isGranted('IS_AUTHENTICATED_FULLY') && $request->get('_route') != 'app_register') {
            $event->setResponse(new RedirectResponse($this->router->generate('app_logout')));
        }
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();

        if (is_object($user) && $user->getStatus() == 'BANNED') {
            $event->setResponse(new RedirectResponse($this->router->generate('app_logout')));
        }
    }
}
