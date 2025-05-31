<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class UserExceptionSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(private RouterInterface $router, RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (str_contains($exception->getMessage(), 'You cannot refresh a user from the EntityUserProvider')) {
            $this->session->set('is_logged_in', true);
            $response = new RedirectResponse($this->router->generate('admin'));
            $event->setResponse($response);
        }
    }
}
