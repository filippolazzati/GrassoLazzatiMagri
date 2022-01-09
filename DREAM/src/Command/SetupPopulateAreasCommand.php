<?php

namespace App\Command;

use App\Entity\Area;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:setup:populate-areas',
    description: 'Populate the areas table with data',
)]
class SetupPopulateAreasCommand extends Command
{
    #[Required] public EntityManagerInterface $em;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM Setup :: Populate Areas');

        if (!$io->confirm('This will delete all existing areas and create new ones. Are you sure?')) {
            return 0;
        }

        $this->em->createQuery('DELETE FROM App\Entity\Area a')->execute();

        $areas = [
            new Area('Adilabad'),
            new Area('Hyderabad'),
            new Area('Karimnagar'),
            new Area('Khammam'),
            new Area('Mahbubnagar'),
            new Area('Medak'),
            new Area('Nalgonda'),
            new Area('Nizamabad'),
            new Area('Rangareddy'),
            new Area('Warangal'),
        ];

        foreach ($areas as $area) {
            $this->em->persist($area);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}
