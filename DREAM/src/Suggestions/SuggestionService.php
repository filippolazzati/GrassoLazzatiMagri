<?php

namespace App\Suggestions;

use App\Repository\WeatherReportRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Service\Attribute\Required;

class SuggestionService
{
    #[Required] public KernelInterface $kernel;
    #[Required] public WeatherReportRepository $weatherReportRepository;

    private string $pythonPath;

    public function __construct(string $pythonPath)
    {
        $this->pythonPath = $pythonPath;
    }

    /**
     * @return Suggestion[]
     */
    public function getSuggestions(string $type, string $city, string $data): array
    {
        $reports = array();

        // get 24 reports representing the past year (2 reports per month)
        $current_date = date('Y-m-d');
        for ($i = 0; $i < 12; $i++) {
            // subtract $i months and 15 days
            $date = date('Y-m-d', strtotime($current_date . ' - ' . $i . ' months'));
            $date = date('Y-m-d', strtotime($date . ' - 15 days'));
            $reports[] = $this->weatherReportRepository->findOneByMonth($date, $city);
            // subtract $i months and 30 days
            $date = date('Y-m-d', strtotime($current_date . ' - ' . $i . ' months'));
            $date = date('Y-m-d', strtotime($date . ' - 30 days'));
            $reports[] = $this->weatherReportRepository->findOneByMonth($date, $city);
        }
        // write data to file sample.csv to pass the sample to the trained neural network to retrieve the suggestion
        $sampleFile = fopen($this->kernel->getProjectDir() . "/suggestions/sample.csv", "wb");
        fwrite($sampleFile, $data);
        foreach ($reports as $report) {
            fwrite($sampleFile, ',');
            fwrite($sampleFile, $report->getWeather() . ',');
            fwrite($sampleFile, $report->getTMax() . ',' . $report->getTMin() . ',');
            fwrite($sampleFile, $report->getTAvg() . ',' . $report->getRainMm() . ',');
            fwrite($sampleFile, $report->getWindSpeed() . ',' . $report->getWindDirection() . ',');
            fwrite($sampleFile, $report->getHumidity() . ',' . $report->getPressure());
        }

        // call the python code that implements the neural network
        $script = match ($type) {
            'fertilizer' => $this->kernel->getProjectDir() . '/suggestions/make_suggestion_fertilizers.py',
            'crop' => $this->kernel->getProjectDir() . '/suggestions/make_suggestion_crops.py',
            default => throw new \InvalidArgumentException(),
        };
        $process = new Process([$this->pythonPath, $script], $this->kernel->getProjectDir() . '/suggestions');
        $process->mustRun();

        // get the results of the neural network
        $results = $process->getOutput();
        $array_results = explode(",", $results);
        $suggestions = [];

        // parse the results and put them in an array to paginate them
        if (count($array_results) > 1) {
            $number = 1; // the number representing the "ranking" of the suggestion.
            for ($i = 0, $iMax = count($array_results); $i < $iMax; $i += 2) {
                $suggestions[] = new Suggestion($array_results[$i], $number, $array_results[$i + 1]);
                $number++;
            }
        }

        return $suggestions;
    }
}