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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Class SetupDatabase
 * @package App\Command
 *
 * This command allows to clean all the Areas, WeatherReports and WeatherForecasts from the database
 * and to add new ones.
 */
#[AsCommand(
    name: 'app:setup:populate-database',
    description: 'Populate the database with sample data',
)]
class SetupDatabase extends Command
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public KernelInterface $kernel;

    private string $pythonPath;

    public function __construct(string $pythonPath)
    {
        parent::__construct();
        $this->pythonPath = $pythonPath;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Initialize the database with sample data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('DREAM Setup :: Populate Database');

        if (!$io->confirm('This will delete all existing forecasts and reports and areas and create new ones. Are you sure?')) {
            return 0;
        }

        $this->em->createQuery('DELETE FROM App\Entity\WeatherForecast a')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\WeatherReport a')->execute();
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

        // generate the datasets updated to the current date
        $process = new Process([
            $this->pythonPath,
            $this->kernel->getProjectDir() . '/sample_datasets/generate_sample_datasets.py'
        ], $this->kernel->getProjectDir() . '/sample_datasets');
        $process->run();

        // setup weather reports
        $CSVfp = fopen($this->kernel->getProjectDir() . "/sample_datasets/weather_reports.csv", "rb");
        $reports = array();
        // remove header
        $row = fgetcsv($CSVfp, 1000, ",");
        // read the rest of the file
        if ($CSVfp !== FALSE) {
            while (!feof($CSVfp)) {
                $row = fgetcsv($CSVfp, 1000, ",");
                if (!empty($row)) {
                    $date = DateTime::createFromFormat('Y-m-d', $row[0]);
                    $reports[] = new WeatherReport($date, $row[1], $row[2], (int)$row[3], (int)$row[4], (int)$row[5], (int)$row[6], (float)$row[7], $row[8], (int)$row[9], (int)$row[10]);
                }
            }
        }
        fclose($CSVfp);

        // persist reports
        foreach ($reports as $report) {
            $this->em->persist($report);
        }

        // setup weather forecasts
        $CSVfp = fopen($this->kernel->getProjectDir() . "/sample_datasets/weather_forecasts.csv", "rb");
        $forecasts = array();
        // remove header
        $row = fgetcsv($CSVfp, 1000, ",");
        // read the rest of the file
        if ($CSVfp !== FALSE) {
            while (!feof($CSVfp)) {
                $row = fgetcsv($CSVfp, 1000, ",");
                if (!empty($row)) {
                    $date = DateTime::createFromFormat('Y-m-d', $row[0]);
                    $forecasts[] = new WeatherForecast($date, $row[1], $row[2], (int)$row[3], (int)$row[4], (int)$row[5], (int)$row[6], (float)$row[7], $row[8], (int)$row[9], (int)$row[10]);
                }
            }
        }
        fclose($CSVfp);

        // persist forecasts
        foreach ($forecasts as $forecast) {
            $this->em->persist($forecast);
        }

        $this->em->flush();

        $io->success('Done!');
        return Command::SUCCESS;
    }
}