<?php

namespace App\Command;

use App\Entity\Area;
use App\Entity\WeatherForecast;
use App\Entity\WeatherReport;
use DateTime;
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

        // read file
        $CSVfp = fopen("src/Command/sample_datasets/weather_reports.csv", "r");
        $reports = array();
        // remove header
        $row = fgetcsv($CSVfp, 1000, ",");
        // read rest of the file
        if ($CSVfp !== FALSE) {
            while (!feof($CSVfp)) {
                $row = fgetcsv($CSVfp, 1000, ",");
                if($row != ''){
                $date = DateTime::createFromFormat('Y-m-d', $row[0]);
                array_push($reports, new WeatherReport($date,$row[1],$row[2],(int) $row[3],(int) $row[4],(int) $row[5],(int) $row[6],(float) $row[7], $row[8],(int) $row[9],(int) $row[10]));
            }
            }
            }
        fclose($CSVfp);

        foreach ($reports as $report) {
            $this->em->persist($report);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}
