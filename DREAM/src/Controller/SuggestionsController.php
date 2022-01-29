<?php


namespace App\Controller;

use App\Controller\suggestions\SuggestionChoice;
use App\Entity\Farm;
use App\Entity\Farmer;
use App\Controller\suggestions\Suggestion;
use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use App\Entity\WeatherForecast;
use App\Entity\WeatherReport;
use App\Form\Forum\NewMessageType;
use App\Form\ProfileType;
use App\Repository\WeatherForecastRepository;
use App\Repository\WeatherReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use SuggestionsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'view', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        // create the form
        $data = new SuggestionChoice();
        $form = $this->createForm(SuggestionsType::class, $data, ['method' => 'GET', 'csrf_protection' => false]);
        $form->handleRequest($request);

        // retrieve data from the form
        $type = $data->getType();
        $crop = $data->getData();

        //if it is a fertilizer
        if ($type) {
            // if no valid crop then print error
            if ($crop != 'potatoes' && $crop != 'tomatoes' && $crop != 'salad' && $crop != 'onions' && $crop != 'radishes' && $crop != 'cucumber' && $crop != 'cauliflower') {
                $error = true;
                $error_message = 'Warning! Insert a valid crop name (one of potatoes, tomatoes, salad, onions, radishes, cucumber, cauliflower)';
            } else {
                $error = false;
                $error_message = '';
            }
        } // if it is a crop
        else {
            // if it is a positive number no error
            if (is_numeric($crop) && $crop > 0) {
                $error = false;
                $error_message = '';
            } else {
                $error = true;
                $error_message = 'Warning! Insert a valid area value (a positive number that represents the area to crop)';
            }
        }

        // if empty variable -> do not do anything
        $not_inserted_yet = false;
        if ($crop == '') {
            $not_inserted_yet = true;
        }

        // if the user has inserted something enter here
        if($not_inserted_yet == false && $error == false){
        /** @var $currentFarmer Farmer */
        $currentFarmer = $this->getUser();

        //take the city of the farm and ask for the forecasts and reports for it
        $city = $currentFarmer->getFarm()->getCity();

        if ($city == null) {
            $this->addFlash('notice', "insert a city before visualizing suggestions");
        } else {
            /** @var $reportsRepo WeatherReportRepository */
            $reportsRepo = $this->em->getRepository(WeatherReport::class);
            $reports = array();

            // get 24 reports representing the past year (2 reports per month)
            $current_date = date('Y-m-d');
            for($i=0; $i<12; $i++){
                // subtract $i months and 15 days
                $date = date('Y-m-d', strtotime($current_date. ' - '.$i.' months'));
                $date = date('Y-m-d', strtotime($date. ' - 15 days'));
                array_push($reports, $reportsRepo->findOneByMonth($date, $city));
                // subtract $i months and 30 days
                $date = date('Y-m-d', strtotime($current_date. ' - '.$i.' months'));
                $date = date('Y-m-d', strtotime($date. ' - 30 days'));
                array_push($reports, $reportsRepo->findOneByMonth($date, $city));
            }

            // write data to file to find the suggestion
            $myfile = fopen("../src/Controller/suggestions/sample.csv", "w");
            fwrite($myfile, $crop); // use the
            foreach ($reports as $report) {
                fwrite($myfile, ',');
                fwrite($myfile, strval($report[0]->getWeather()) . ',');
                fwrite($myfile, strval($report[0]->getTMax()) . ',' . strval($report[0]->getTMin()) . ',');
                fwrite($myfile, strval($report[0]->getTAvg()) . ',' . strval($report[0]->getRainMm()) . ',');
                fwrite($myfile, strval($report[0]->getWindSpeed()) . ',' . strval($report[0]->getWindDirection()) . ',');
                fwrite($myfile, strval($report[0]->getHumidity()) . ',' . strval($report[0]->getPressure()));
            }

            // if the user wants a fertilizer suggestion
            if ($type) {
                $results = shell_exec('python ../src/Controller/suggestions/make_suggestion_fertilizers.py');
            } else {
                $results = shell_exec('python ../src/Controller/suggestions/make_suggestion_crops.py');
            }
            $array_results = explode(",", $results);
            $suggestions = array();
            $number = 1;
            for ($i = 0; $i < count($array_results); $i = $i + 2) {
                array_push($suggestions, new Suggestion($array_results[$i], $number, $array_results[$i + 1]));
                $number++;
            }
            $pagination = $this->paginator->paginate($suggestions, $request->query->getInt('page', 1), 25);

            return $this->render('suggestions/view.html.twig', ['pagination' => $pagination, 'form' => $form->createView(), 'error' => $error, 'error_message' => $error_message, 'not_inserted_yet' => $not_inserted_yet]);

        }}
        return $this->render('suggestions/view.html.twig', ['form' => $form->createView(), 'error' => $error, 'error_message' => $error_message, 'not_inserted_yet' => $not_inserted_yet]);
    }

}