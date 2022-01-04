<?php


namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\Forum\Thread;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/suggestions', name: 'suggestions_')]
class SuggestionsController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;

    #[Route('/', name: 'view', methods: ['GET'])] // {id}
    public function index(Request $request): Response
    {

        /** @var $currentFarmer Farmer*/
        $currentFarmer = $this->getUser();

        //take the city of the farm and ask for the forecasts and reports for it
        $city = $currentFarmer->getFarm()->getCity();

        // if($city !== null)
        // $forecasts = getWeatherForecasts($city, ...);
        // $reports = getWeatherReports($city, ...);




       return $this->render('suggestions/view.html.twig');
    }

    public function getSuggestions(Farmer $farmer)
    {

    }
}