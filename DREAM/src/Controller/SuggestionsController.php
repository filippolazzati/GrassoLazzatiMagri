<?php


namespace App\Controller;

use App\Entity\Farmer;
use App\Controller\suggestions\Suggestion;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use App\Entity\WeatherForecast;
use App\Entity\WeatherReport;
use App\Form\Forum\NewMessageType;
use App\Repository\WeatherForecastRepository;
use App\Repository\WeatherReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'view', methods: ['GET'])] // {id}
    public function index(Request $request): Response
    {

        /** @var $currentFarmer Farmer*/
        $currentFarmer = $this->getUser();

        //take the city of the farm and ask for the forecasts and reports for it
        $city = $currentFarmer->getFarm()->getCity();

        if($city == null) {
            $this->addFlash('notice', "insert a city before visualizing forecasts");
        }
        else {
            /** @var $forecastsRepo WeatherForecastRepository */
            $forecastsRepo = $this->em->getRepository(WeatherForecast::class);

            $forecasts = $forecastsRepo->findNextThreeForecasts(date("Y-m-d"), $city);

            /** @var $reportsRepo WeatherReportRepository */
            $reportsRepo = $this->em->getRepository(WeatherReport::class);

            $reports = $reportsRepo->findAllPastReports($city);

            $this->addFlash('notice', "These suggestions have been obtained based on an expert advice. They aim to provide an idea about what to do.");

            // write data to file to find the suggestion
            $myfile = fopen("../src/Controller/suggestions/sample.csv", "w");
            fwrite($myfile, "salad");
            foreach ($reports as $report) {
                fwrite($myfile, ',');
                fwrite($myfile, strval($report->getWeather()) . ',');
                fwrite($myfile, strval($report->getTMax()) . ',' . strval($report->getTMin()) . ',');
                fwrite($myfile, strval($report->getTAvg()) . ',' . strval($report->getRainMm()) . ',');
                fwrite($myfile, strval($report->getWindSpeed()) . ',' . strval($report->getWindDirection()) . ',');
                fwrite($myfile, strval($report->getHumidity()) . ',' . strval($report->getPressure()));
            }
            $results = shell_exec('python ../src/Controller/suggestions/make_suggestion_fertilizers.py');
            $array_results = explode(",",$results);
            $suggestions = array();
            $number = 1;
            for($i=0; $i<count($array_results); $i=$i+2){
                array_push($suggestions, new Suggestion($array_results[$i], 'Fertilizer', $number, $array_results[$i+1]));
                $number++;
            }
            // TODO farmer crop
        }
        $pagination = $this->paginator->paginate($suggestions, $request->query->getInt('page', 1), 25);

       return $this->render('suggestions/view.html.twig', ['pagination' => $pagination]);
    }
}