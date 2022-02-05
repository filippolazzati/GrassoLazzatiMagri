<?php

namespace App\Command;

use App\Entity\Agronomist;
use App\Entity\Farm;
use App\Entity\Farmer;
use App\Entity\PolicyMaker;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:user:create',
    description: 'Add a short description for your command',
)]
class UserCreateCommand extends Command
{
    #[Required] public EntityManagerInterface $entityManager;
    #[Required] public UserPasswordHasherInterface $passwordHasher;

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('type', InputArgument::REQUIRED, 'User type (farmer/agronomist/policy_maker)')
            ->setHelp('Create user with chosen username, password and type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM: Create new user');

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        if (!in_array($input->getArgument('type'), ['farmer', 'agronomist', 'policy_maker'])) {
            $io->error('Invalid user type');
            return 1;
        }

        if (null !== $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
            $io->error('The specified email address is already in use.');
            return 1;
        }

        $user = match ($input->getArgument('type')) {
            'farmer' => new Farmer(),
            'agronomist' => new Agronomist(),
            'policy_maker' => new PolicyMaker(),
        };

        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setName('');
        $user->setSurname('');
        $user->setBirthDate(new DateTime());

        if ($user instanceof Farmer) {
            $user->setFarm(new Farm());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User created successfully!');
        $io->table(['key', 'value'], [
            ['Email', $user->getEmail()],
            ['Roles', implode(', ', $user->getRoles())],
            ['Password', '*****'],
        ]);

        return Command::SUCCESS;
    }
}
