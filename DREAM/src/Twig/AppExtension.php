<?php

namespace App\Twig;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    #[Required] public Security $security;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_user_type', [$this, 'isUserType']),
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function isUserType(string $class): bool
    {
        $user = $this->security->getUser();
        if (null === $user) {
            return false;
        }
        return (new ReflectionClass($class))->isInstance($user);
    }
}