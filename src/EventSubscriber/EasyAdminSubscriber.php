<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private array $deletedUsers = [];

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDeleted'],
            AfterEntityDeletedEvent::class => ['afterEntityDeleted'],
        ];
    }

    public function beforeEntityDeleted(BeforeEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof User) {
            // Store user info before deletion
            $this->deletedUsers[] = [
                'username' => $entity->getUsername(),
                'email' => $entity->getEmail(),
                'id' => $entity->getId()
            ];
        }
    }

    public function afterEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof User && !empty($this->deletedUsers)) {
            $session = $this->requestStack->getSession();

            // Find the deleted user info
            $deletedUser = array_pop($this->deletedUsers);

            $session->getFlashBag()->add('success', sprintf(
                'User "%s" (%s) has been deleted successfully!',
                $deletedUser['username'],
                $deletedUser['email']
            ));
        }
    }
}
