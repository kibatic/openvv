<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

/**
 * Vérifications à la connexion (firewall main) : refuse les comptes dont
 * l'email n'est pas vérifié et les comptes désactivés par un administrateur.
 */
class VerifiedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        $this->checkAccountStatus($user);
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        $this->checkAccountStatus($user);
    }

    private function checkAccountStatus(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('User account is not verified.');
        }
        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('User account is disabled.');
        }
    }
}
