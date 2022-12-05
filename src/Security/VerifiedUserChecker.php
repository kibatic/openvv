<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class VerifiedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('User account is not verified.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('User account is not verified.');
        }
    }
}
