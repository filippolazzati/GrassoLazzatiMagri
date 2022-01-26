<?php

namespace App\Command;

use App\Entity\Area;
use App\Entity\WeatherForecast;
use App\Entity\WeatherReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:setup:populate-reports',
    description: 'Populate the reports table with data',
)]
class SetupPopulateWeatherReportsCommand extends Command
{
    #[Required] public EntityManagerInterface $em;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM Setup :: Populate Weather Reports');

        if (!$io->confirm('This will delete all existing reports and create new ones. Are you sure?')) {
            return 0;
        }

        $this->em->createQuery('DELETE FROM App\Entity\WeatherReport a')->execute();


        // create an array of reports for the previous 30 days in Adilabad
        $reports = [
            new WeatherReport(new \DateTime('-1 day'), "Adilabad", "sunny", 30, 22, 27, 0, 2.0, "n", 40, 1060),
            new WeatherReport(new \DateTime('-2 day'), "Adilabad", "sunny", 28, 22, 26, 0, 2.5, "ne", 45, 1015),
            new WeatherReport(new \DateTime('-3 day'), "Adilabad", "partially cloudy", 28, 20, 25, 0, 1.0, "n", 30, 1060),
            new WeatherReport(new \DateTime('-4 day'), "Adilabad", "partially cloudy", 29, 22, 26, 0, 3, "ne", 42, 1080),
            new WeatherReport(new \DateTime('-5 day'), "Adilabad", "cloudy", 25, 19, 23, 5, 5, "no", 38, 990),
            new WeatherReport(new \DateTime('-6 day'), "Adilabad", "rainy", 25, 17, 20, 20, 7, "o", 55, 985),
            new WeatherReport(new \DateTime('-7 day'), "Adilabad", "rainy", 30, 22, 27, 25, 2.0, "n", 40, 1010),
            new WeatherReport(new \DateTime('-8 day'), "Adilabad", "sunny", 28, 22, 26, 0, 2.5, "o", 45, 1015),
            new WeatherReport(new \DateTime('-9 day'), "Adilabad", "partially cloudy", 28, 20, 25, 0, 4, "n", 30, 1030),
            new WeatherReport(new \DateTime('-10 day'), "Adilabad", "sunny", 29, 22, 26, 0, 3, "ne", 42, 1020),
            new WeatherReport(new \DateTime('-11 day'), "Adilabad", "cloudy", 25, 19, 23, 5, 5, "e", 38, 1080),
            new WeatherReport(new \DateTime('-12 day'), "Adilabad", "rainy", 25, 17, 20, 15, 7, "e", 55, 985),
            new WeatherReport(new \DateTime('-13 day'), "Adilabad", "foggy", 30, 22, 27, 0, 2.0, "n", 40, 1010),
            new WeatherReport(new \DateTime('-14 day'), "Adilabad", "sunny", 29, 22, 26, 0, 3, "ne", 42, 1020),
            new WeatherReport(new \DateTime('-15 day'), "Adilabad", "cloudy", 25, 19, 23, 5, 5, "e", 38, 990),
            new WeatherReport(new \DateTime('-16 day'), "Adilabad", "rainy", 25, 17, 20, 30, 7, "e", 55, 985),
            new WeatherReport(new \DateTime('-17 day'), "Adilabad", "foggy", 30, 22, 27, 0, 2.0, "n", 40, 1060),
            new WeatherReport(new \DateTime('-18 day'), "Adilabad", "foggy", 28, 22, 26, 0, 2.5, "ne", 45, 1080),
            new WeatherReport(new \DateTime('-19 day'), "Adilabad", "partially cloudy", 28, 20, 25, 0, 4, "n", 30, 1060),
            new WeatherReport(new \DateTime('-20 day'), "Adilabad", "sunny", 29, 22, 26, 0, 3, "ne", 42, 1020),
            new WeatherReport(new \DateTime('-21 day'), "Adilabad", "cloudy", 25, 19, 23, 5, 5, "se", 38, 990),
            new WeatherReport(new \DateTime('-22 day'), "Adilabad", "rainy", 25, 17, 20, 35, 1.0, "se", 55, 1000),
            new WeatherReport(new \DateTime('-23 day'), "Adilabad", "rainy", 30, 22, 27, 35, 1.0, "s", 40, 1010),
            new WeatherReport(new \DateTime('-24 day'), "Adilabad", "stormy", 28, 22, 26, 50, 11, "ne", 50, 1015),
            new WeatherReport(new \DateTime('-25 day'), "Adilabad", "stormy", 28, 20, 25, 50, 10, "n", 45, 1030),
            new WeatherReport(new \DateTime('-26 day'), "Adilabad", "sunny", 29, 22, 26, 0, 3, "ne", 42, 1060),
            new WeatherReport(new \DateTime('-27 day'), "Adilabad", "cloudy", 25, 19, 23, 5, 5, "e", 38, 990),
            new WeatherReport(new \DateTime('-28 day'), "Adilabad", "rainy", 25, 17, 20, 10, 7, "se", 55, 1080),
            new WeatherReport(new \DateTime('-29 day'), "Adilabad", "sunny", 30, 22, 27, 0, 2.0, "n", 40, 1010),
            new WeatherReport(new \DateTime('-30 day'), "Adilabad", "sunny", 28, 22, 26, 0, 2.5, "ne", 45, 985),
            ];

        foreach ($reports as $report) {
            $this->em->persist($report);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}
