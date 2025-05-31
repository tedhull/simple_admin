<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Entity\User;


class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatus() == 'BANNED') {
            throw new CustomUserMessageAuthenticationException('Your account has been banned.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Optional: checks after credentials are verified
    }
}
