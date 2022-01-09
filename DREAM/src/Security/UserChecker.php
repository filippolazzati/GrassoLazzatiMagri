<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!($user instanceof User)) {
            return;
        }

        if (null !== $user->getEmailVerificationToken()) {
            throw new CustomUserMessageAccountStatusException('Your email address is not verified.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // Do nothing
    }
}