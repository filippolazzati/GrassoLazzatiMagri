<?php


namespace App\Controller;

use App\Entity\Farmer;
use App\Controller\suggestions\Suggestion;
use App\Entity\WeatherReport;
use App\Form\SuggestionsType;
use App\Repository\WeatherReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Class SuggestionsController
 * @package App\Controller
 *
 * This controller receives the request of the user to receive a suggestion, parse it, creates
 * a suggestion and returns it to the user. It renders the template suggestions/view.html.twig.
 */
#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends AbstractController
{

    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'view', methods: ['GET'])]
    public function view(Request $request): Response
    {
        // create the form on top of the page
        $form = $this->createForm(SuggestionsType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $pagination = null;

        // handle the request and check if the form has been submitted and is valid
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // retrieve data from the form
            $type = $form->getData()['type'];
            $crop = $form->getData()['crop'];
            $area = $form->getData()['area'];

            //get the current farmer object and city
            /** @var $currentFarmer Farmer */
            $currentFarmer = $this->getUser();
            $city = $currentFarmer->getFarm()->getCity();

            // get the WeatherReportsRepository object to perform queries
            /** @var $reportsRepo WeatherReportRepository */
            $reportsRepo = $this->em->getRepository(WeatherReport::class);
            $reports = array();

            // get 24 reports representing the past year (2 reports per month)
            $current_date = date('Y-m-d');
            for ($i = 0; $i < 12; $i++) {
                // subtract $i months and 15 days
                $date = date('Y-m-d', strtotime($current_date . ' - ' . $i . ' months'));
                $date = date('Y-m-d', strtotime($date . ' - 15 days'));
                $reports[] = $reportsRepo->findOneByMonth($date, $city);
                // subtract $i months and 30 days
                $date = date('Y-m-d', strtotime($current_date . ' - ' . $i . ' months'));
                $date = date('Y-m-d', strtotime($date . ' - 30 days'));
                $reports[] = $reportsRepo->findOneByMonth($date, $city);
            }
            // write data to file sample.csv to pass the sample to the trained neural network to retrieve the suggestion
            $sampleFile = fopen("../src/Controller/suggestions/sample.csv", "wb");
            fwrite($sampleFile, $type === 'fertilizer' ? $crop : $area);
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
                'fertilizer' => '../src/Controller/suggestions/make_suggestion_fertilizers.py',
                'crop' => '../src/Controller/suggestions/make_suggestion_crops.py',
            };
            $process = new Process(['python3', $script]);
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

            // fill the paginator object with the results of the neural network
            $pagination = $this->paginator->paginate($suggestions, $request->query->getInt('page', 1), 25);
        }

        // render the view passing the pagination object and the form object
        return $this->render('suggestions/view.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }
}