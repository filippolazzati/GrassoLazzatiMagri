<?php

namespace App\Controller\DailyPlan;

use App\Entity\Agronomist;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/daily_plan', name: 'daily_plan_')]
class DailyPlanController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;

    #[Route('/view', name: 'index', methods: ['GET'])]
    public function index(Request $request) : \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        // retrieve next seven working days from this one
        // in a real project, to be substituted with a call to an external API
        $workingDays = (new Calendar())->getSevenWorkingDaysFrom(new \DateTime());

        return $this->render('dailyplan/index.html.twig', ['working_days' => $workingDays]);
    }
}

