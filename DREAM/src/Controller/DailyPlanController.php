<?php

namespace App\Controller;

use App\DailyPlan\Calendar;
use App\DailyPlan\DailyPlanService;
use App\Entity\Agronomist;
use App\Entity\DailyPlan;
use App\Entity\FarmVisit;
use App\Form\DailyPlan\CreateDailyPlanType;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/daily_plan', name: 'daily_plan_')]
class DailyPlanController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;

    #[Route('/dates', name: 'index', methods: ['GET'])]
    public function index(Request $request) : \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        // retrieve next seven working days from this one
        $workingDays = (new Calendar())->getSevenWorkingDaysFrom(new \DateTime());


        // for each working day, retrieve the daily plan for the day if it exists
        $dailyPlans = [];

        foreach ($workingDays as $day) {
            $dailyPlan = $this->em->getRepository(DailyPlan::class)->findDailyPlanByAgronomistAndDate($agronomist, $day);
            $dailyPlans += [$day->format('Y-m-d') => ((!is_null($dailyPlan)) ? $dailyPlan->getId() : null)];
        }

        return $this->render('dailyplan/index.html.twig',
            ['working_days' => $workingDays, 'daily_plans' => $dailyPlans]);
    }

    #[Route('/daily_plan/create/{date}', name: 'create', methods: ['GET', 'POST'])]
    public function createDailyPlan(Request $request, \DateTime $date): \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        // create the form for new daily plan
        $options = ['maxVisits' => DailyPlanService::MAX_VISITS_IN_A_DAY];
        $form = $this->createForm(CreateDailyPlanType::class, null, $options);

        $form->handleRequest($request);

        // when the user submits the form, create the daily plan
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $numberOfVisits = $formData['numberOfVisits'];
            // the form is valid if the number of visits is less than or equal MAX_VISITS and if the date is not
            // in the past
            if ($numberOfVisits <= DailyPlanService::MAX_VISITS_IN_A_DAY && $date > new \DateTime('yesterday')) {
                // the daily plan can be created if the user has not already got a daily plan for that day
                if (!$this->em->getRepository(DailyPlan::class)->hasDailyPlan($agronomist, $date)) {
                    $dpService = new DailyPlanService($this->em->getRepository(FarmVisit::class));
                    $dailyPlan = $dpService->generateDailyPlan($agronomist, $date, $numberOfVisits);
                }
            }
        }

        return $this->render('dailyplan/create_daily_plan.html.twig', []);
    }

    #[Route('/daily_plan/{daily_plan}', name: 'date', methods: ['GET', 'POST'])]
    public function dailyPlan(Request $request, DailyPlan $daily_plan) : \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('dailyplan/daily_plan.html.twig', []);
    }

}

