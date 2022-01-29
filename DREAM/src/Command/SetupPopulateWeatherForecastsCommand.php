<?php

namespace App\Command;

use App\Entity\Area;
use App\Entity\WeatherForecast;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'app:setup:populate-forecasts',
    description: 'Populate the forecasts table with data',
)]
class SetupPopulateWeatherForecastsCommand extends Command
{
    #[Required] public EntityManagerInterface $em;

    protected function configure(): void
    {
        $this
            ->addArgument('city', InputArgument::REQUIRED, 'The city for the forecasts (one of the 12 known)')
            ->setHelp('Create weather forecasts for the next 6 days in the given city.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM Setup :: Populate Weather Forecasts');

        $city = $input->getArgument('city');

        if (!in_array($input->getArgument('city'), ['Hyderabad', 'Warangal', 'Nizamabad', 'Khammam', 'Karimnagar', 'Ramagundam', 'Mahabubnagar', 'Adilabad', 'Suryapet', 'Siddipet', 'Nalgonda', 'Jagtial'])) {
            $io->error('Invalid user type');
            return 1;
        }

        if (!$io->confirm('This will delete all existing forecasts and create new ones. Are you sure?')) {
            return 0;
        }

        $this->em->createQuery('DELETE FROM App\Entity\WeatherForecast a')->execute();


        // create an array of forecasts for the next 6 days in Adilabad
        $forecasts = [
            new WeatherForecast(new \DateTime('+1 day'), $city, "sunny", 30, 22, 27, 0, 2.0, "n", 40, 1010),
            new WeatherForecast(new \DateTime('+2 day'), $city, "sunny", 28, 22, 26, 0, 2.5, "ne", 45, 1015),
            new WeatherForecast(new \DateTime('+3 day'), $city, "partially cloudy", 28, 20, 25, 0, 4, "n", 30, 1030),
            new WeatherForecast(new \DateTime('+4 day'), $city, "sunny", 29, 22, 26, 0, 3, "ne", 42, 1020),
            new WeatherForecast(new \DateTime('+5 day'), $city, "cloudy", 25, 19, 23, 5, 5, "e", 38, 990),
            new WeatherForecast(new \DateTime('+6 day'), $city, "rainy", 25, 17, 20, 0, 7, "e", 55, 1000),
        ];

        foreach ($forecasts as $forecast) {
            $this->em->persist($forecast);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}
