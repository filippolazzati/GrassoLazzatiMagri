<?php


namespace App\Controller;


use App\Controller\forecasts\Forecast;
use App\Controller\forecasts\ForecastsChoice;
use App\Controller\suggestions\Suggestion;
use App\Controller\suggestions\SuggestionChoice;
use App\Entity\Farmer;
use App\Entity\WeatherForecast;
use App\Entity\WeatherReport;
use App\Form\WeatherForecastsType;
use App\Repository\WeatherForecastRepository;
use App\Repository\WeatherReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/forecasts', name: 'forecasts_')]
class WeatherForecastsController extends AbstractController
{

    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'view', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        // create the form
        $data = new ForecastsChoice();
        $form = $this->createForm(WeatherForecastsType::class, $data, ['method' => 'GET', 'csrf_protection' => false]);
        $form->handleRequest($request);

        // retrieve data from the form
        $city = $data->getCity();

        // query the database to retrieve the forecasts
        /** @var $forecastsRepo WeatherForecastRepository */
        $forecastsRepo = $this->em->getRepository(WeatherForecast::class);
        $forecasts = $forecastsRepo->findAllForecasts($city);

        // rewrite the forecasts to convert datetime objects to string
        $forecasts_to_show = array();
        foreach($forecasts as $forecast){
            array_push($forecasts_to_show, new Forecast(($forecast->getDate())->format('Y-m-d'),$forecast->getWeather(), $forecast->getTMax(),$forecast->getTMin(),$forecast->getTAvg(),$forecast->getRainMm(), $forecast->getWindSpeed(), $forecast->getWindDirection(), $forecast->getHumidity(), $forecast->getPressure() ));
        }
        // paginate and show the results
        $pagination = $this->paginator->paginate($forecasts_to_show, $request->query->getInt('page', 1), 25);
        return $this->render('forecasts/view.html.twig', ['pagination' => $pagination, 'form' => $form->createView()]);

    }
}