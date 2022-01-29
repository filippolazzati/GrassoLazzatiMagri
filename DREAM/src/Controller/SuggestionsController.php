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

#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $form = $this->createForm(SuggestionsType::class, null, [
            'action' => $this->generateUrl('suggestions_view'),
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        return $this->render('suggestions/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/view', name: 'view', methods: ['GET'])]
    public function view(Request $request): Response
    {
        $form = $this->createForm(SuggestionsType::class, null, ['method' => 'GET', 'csrf_protection' => false]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectToRoute('suggestions_index');
        }

        $type = $form->getData()['type'];
        $crop = $form->getData()['crop'];
        $area = $form->getData()['area'];

        /** @var $currentFarmer Farmer */
        $currentFarmer = $this->getUser();
        $city = $currentFarmer->getFarm()->getCity();

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

        // write data to file to find the suggestion
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

        $script = match ($type) {
            'fertilizer' => '../src/Controller/suggestions/make_suggestion_fertilizers.py',
            'crop' => '../src/Controller/suggestions/make_suggestion_crops.py',
        };
        $process = new Process(['python3', $script]);
        $process->mustRun();
        $results = $process->getOutput();
        $array_results = explode(",", $results);
        $suggestions = [];
        if (count($array_results) > 1) {
            for ($i = 0, $iMax = count($array_results); $i < $iMax; $i += 2) {
                $suggestions[] = new Suggestion($array_results[$i], $i + 1, $array_results[$i + 1]);
            }
        }
        $pagination = $this->paginator->paginate($suggestions, $request->query->getInt('page', 1), 25);

        return $this->render('suggestions/view.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }
}