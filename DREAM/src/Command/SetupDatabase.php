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
    name: 'app:setup:populate-database',
    description: 'Populate the database with sample data',
)]
class SetupDatabase extends Command
{
    #[Required] public EntityManagerInterface $em;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM Setup :: Populate Database');

        // generate the datasets updated to the current date
        shell_exec('python src/Command/sample_datasets/generate_sample_datasets.py');

        // setup weather reports
        $CSVfp = fopen("src/Command/sample_datasets/weather_reports.csv", "r");
        $reports = array();
        // remove header
        $row = fgetcsv($CSVfp, 1000, ",");
        // read the rest of the file
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

        // persist reports
        foreach ($reports as $report) {
            $this->em->persist($report);
        }

        // setup weather forecasts
        $CSVfp = fopen("src/Command/sample_datasets/weather_forecasts.csv", "r");
        $forecasts = array();
        // remove header
        $row = fgetcsv($CSVfp, 1000, ",");
        // read the rest of the file
        if ($CSVfp !== FALSE) {
            while (!feof($CSVfp)) {
                $row = fgetcsv($CSVfp, 1000, ",");
                if($row != ''){
                    $date = DateTime::createFromFormat('Y-m-d', $row[0]);
                    array_push($forecasts, new WeatherForecast($date,$row[1],$row[2],(int) $row[3],(int) $row[4],(int) $row[5],(int) $row[6],(float) $row[7], $row[8],(int) $row[9],(int) $row[10]));
                }
            }
        }
        fclose($CSVfp);

        // persist forecasts
        foreach ($forecasts as $forecast) {
            $this->em->persist($forecast);
        }

        // insert areas
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

        // persist areas
        foreach ($areas as $area) {
            $this->em->persist($area);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}