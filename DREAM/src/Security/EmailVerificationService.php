<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Service\Attribute\Required;

class EmailVerificationService
{
    #[Required] public EntityManagerInterface $entityManager;
    #[Required] public MailerInterface $mailer;

    /**
     * @throws TransportExceptionInterface
     */
    public function sendVerificationEmail(User $user): void
    {
        $this->entityManager->wrapInTransaction(function ($em) use ($user) {
            do {
                $token = bin2hex(random_bytes(32));
            } while ($em->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]));

            $user->setEmailVerificationToken($token);
        });

        $message = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Please verify your email address')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($message);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function verifyEmail(string $token): void
    {
        $this->entityManager->wrapInTransaction(function ($em) use ($token) {
            $user = $em->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);

            if (null === $user) {
                throw new InvalidArgumentException('Invalid verification token');
            }

            $user->setEmailVerificationToken(null);
        });
    }
}