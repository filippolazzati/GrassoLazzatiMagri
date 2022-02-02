<?php

namespace App\Controller;

use App\DailyPlan\Calendar;
use App\DailyPlan\DailyPlanService;
use App\Entity\Agronomist;
use App\Entity\DailyPlan\DailyPlan;
use App\Entity\DailyPlan\FarmVisit;
use App\Form\DailyPlan\AddVisitType;
use App\Form\DailyPlan\CreateDailyPlanType;
use App\Form\DailyPlan\MoveVisitType;
use App\Form\DailyPlan\RemoveVisitType;
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
                    $this->em->persist($dailyPlan);
                    $this->em->flush();
                    return $this->redirectToRoute('daily_plan_date', ['daily_plan' => $dailyPlan->getId()]);
                }
            }
        }

        return $this->render('dailyplan/create_daily_plan.html.twig', ['date' => $date]);
    }

    #[Route('/daily_plan/{daily_plan}/{visit_to_move?}', name: 'date', methods: ['GET', 'POST'])]
    public function getDailyPlan(Request $request, DailyPlan $daily_plan, ?FarmVisit $visit_to_move) : \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        // if the daily plan requested does not belong to the user, error
        if (!$daily_plan->getAgronomist()->equals($agronomist)) {
            throw new AssertionError();
        }

        $dpService = new DailyPlanService($this->em->getRepository(FarmVisit::class));

        $errorMsg = null;
        $renderParameters =  ['daily_plan' => $daily_plan, 'error_msg' => $errorMsg];

        // if the user has selected a visit to move (and the selected visit actually belongs to the daily plan),
        // and the daily plan is in state NEW or ACCEPTED, show the form and process it
        if (!is_null($visit_to_move) && $daily_plan->getFarmVisits()->exists(function ($key, $value) use ($visit_to_move) {
                $value->getStartTime() == $visit_to_move;
            }) && !$daily_plan->isConfirmed() ) {
            $formToMoveVisit = $this->createForm(MoveVisitType::class);
            $renderParameters += ['form_move_visit' => $formToMoveVisit];

            $formToMoveVisit->handleRequest($request);

            if ($formToMoveVisit->isSubmitted() && $formToMoveVisit->isValid()) {
                $formData = $formToMoveVisit->getData();
                $newStartHour = $formData['newStartHour'];
                try {
                    $dpService->moveVisit($agronomist, $daily_plan, $visit_to_move, $newStartHour);
                    $this->em->persist($daily_plan);
                    $this->em->flush();

                } catch(\Exception $e) {
                    $errorMsg = 'The visit cannot be moved to the selected hour';
                    return $this->render('dailyplan/daily_plan.html.twig', $renderParameters);
                }
            }
        }

        // if the daily plan is in state NEW or ACCEPTED, show the form for adding a visit
        if (!$daily_plan->isConfirmed()) {
            $farmsInTheArea = $agronomist->getArea()->getFarms();
            $options = array('farmsInTheArea' => $farmsInTheArea);
            $formToAddVisit =  $this->createForm(AddVisitType::class, null, $options);
            $renderParameters += ['form_add_visit' => $formToAddVisit];

            $formToAddVisit->handleRequest($request);

            if($formToAddVisit->isSubmitted() && $formToAddVisit->isValid()) {
                $formData = $formToAddVisit->getData();
                try {
                    $dpService->addVisit($agronomist, $daily_plan, $formData['farm'], $formData['startingHour']);
                    $this->em->persist($daily_plan);
                    $this->em->flush();
                } catch (\Exception $e) {
                    $errorMsg = 'The visit cannot be added';
                    return $this->render('dailyplan/daily_plan.html.twig', $renderParameters);
                }
            }
        }

        // if the daily plan is in state NEW or ACCEPTED, show for each visit a form to remove it
        if (!$daily_plan->isConfirmed()) {
            $formsToRemoveVisits = array();
            $renderParameters += ['forms_remove_visits' => $formsToRemoveVisits];

            foreach ($daily_plan->getFarmVisits() as $farmVisit) {
                $formToRemoveVisit = $this->createForm(RemoveVisitType::class, null, [$farmVisit]);
                $formsToRemoveVisits += [$farmVisit => $formToRemoveVisit];

                $formToAddVisit->handleRequest($request);

                if($formToAddVisit->isSubmitted() && $formToAddVisit->isValid()) {
                    $formData = $formToAddVisit->getData();
                    try {
                        $dpService->removeVisit($agronomist, $daily_plan, $farmVisit);
                        $this->em->persist($daily_plan);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        $errorMsg = 'The visit cannot be removed';
                        return $this->render('dailyplan/daily_plan.html.twig', $renderParameters);
                    }
                }
            }
        }

        // TODO: add form to accept and confirm daily plan

        return $this->render('dailyplan/daily_plan.html.twig', $renderParameters);
    }

}

