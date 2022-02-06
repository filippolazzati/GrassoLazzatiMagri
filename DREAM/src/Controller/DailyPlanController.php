<?php

namespace App\Controller;

use App\DailyPlan\Calendar;
use App\DailyPlan\DailyPlanService;
use App\Entity\Agronomist;
use App\Entity\DailyPlan\DailyPlan;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use App\Entity\ProductionData\ProductionData;
use App\Form\DailyPlan\AcceptDailyPlanType;
use App\Form\DailyPlan\AddVisitType;
use App\Form\DailyPlan\ConfirmDailyPlanType;
use App\Form\DailyPlan\CreateDailyPlanType;
use App\Form\DailyPlan\InsertFarmVisitsFeedbacksType;
use App\Form\DailyPlan\MoveVisitType;
use App\Form\DailyPlan\RemoveVisitType;
use AssertionError;
use Cassandra\Date;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
            // NB: you can't generate a new daily plan for the current day
            if (!(is_null($dailyPlan) && $day < new \DateTime('tomorrow'))) {
                $dailyPlans += [$day->format('Y-m-d') => ((!is_null($dailyPlan)) ? $dailyPlan->getId() : null)];
            } else {
                $workingDays->removeElement($day);
            }
        }

        // retrieve past daily plans in state new or confirmed
        $pastDailyPlans = $this->em->getRepository(DailyPlan::class)->findNotConfirmedPastDailyPlansOfAgronomist($agronomist, new \DateTime());

        return $this->render('dailyplan/index.html.twig',
            ['working_days' => $workingDays, 'daily_plans' => $dailyPlans, 'past_daily_plans' => $pastDailyPlans]);
    }

    #[Route('/daily_plan/create/{date}', name: 'create', methods: ['GET', 'POST'])]
    public function createDailyPlan(Request $request, \DateTime $date): \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        // if date is not in the future, error
        if ($date < new \DateTime('tomorrow')) {
            $this->createNotFoundException('date not in the future');
        }

        // if the agronomist already has a daily plan for the date, error
        if (!is_null($this->em->getRepository(DailyPlan::class)->findDailyPlanByAgronomistAndDate($agronomist, $date))) {
            $this->createNotFoundException('there is already a daily plan for this date');
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
            if ($numberOfVisits <= DailyPlanService::MAX_VISITS_IN_A_DAY) {
                // the daily plan can be created if the user has not already got a daily plan for that day
                    $dpService = new DailyPlanService($this->em->getRepository(FarmVisit::class));
                    $dailyPlan = $dpService->generateDailyPlan($agronomist, $date, $numberOfVisits);
                    $this->em->persist($dailyPlan);
                    $this->em->flush();
                    return $this->redirectToRoute('daily_plan_date', ['daily_plan' => $dailyPlan->getId()]);
            }
        }

        return $this->render('dailyplan/create_daily_plan.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/daily_plan/{daily_plan}', name: 'date', methods: ['GET', 'POST'])]
    public function getDailyPlan(Request $request, DailyPlan $daily_plan) : \Symfony\Component\HttpFoundation\Response
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

        // initialize to null parameters to handle to the template
        $errorMsg = null;
        $formsToMoveVisits = null;
        $formToAddVisit = null;
        $formsToRemoveVisits = null;
        $formToAcceptDailyPlan = null;
        $formToConfirmDailyPlan = null;

        if ($daily_plan->getFarmVisits()->isEmpty()) {
            $startHourOfLastVisit = new \DateTime(DailyPlanService::START_WORKDAY);
        } else {
            $startHourOfLastVisit = max($daily_plan->getFarmVisits()->map(function ($value) {
                return $value->getStartTime();
            })->toArray());
        }

        $halfHourPassed = new \DateTime() >= new \DateTime(
                $daily_plan->getDate()->format('Y-m-d') . ' ' . $startHourOfLastVisit->format('H:i:s'));



        // if the daily plan is in state NEW or ACCEPTED, show for each visit a form to move it
        if ($daily_plan->isNew() || ($daily_plan->isAccepted() && $halfHourPassed)) {

            $formsToMoveVisits = array();

            foreach ($daily_plan->getFarmVisits() as $farmVisit) {
                $formToMoveVisit = $this->createForm(MoveVisitType::class, null, ['visitToMove' => $farmVisit->getId()]);
                $formsToMoveVisits += [$farmVisit->getId() => $formToMoveVisit->createView()];

                $formToMoveVisit->handleRequest($request);

                if($formToMoveVisit->isSubmitted() && $formToMoveVisit->isValid()) {
                    $formData = $formToMoveVisit->getData();
                    $visitToMove = $this->em->getRepository(FarmVisit::class)->find($formData['visit']);
                    $newStartHour = $formData['newStartHour'];
                    try {
                        $dpService->moveVisit($agronomist, $daily_plan, $visitToMove, $newStartHour);
                        $this->em->persist($visitToMove);
                        $this->em->flush();
                        $this->redirectToRoute($request->attributes->get('_route'));
                    } catch(\Exception $e) {
                        $errorMsg = 'The visit cannot be moved to the selected hour';
                    }
                }
            }
        }

        // if the daily plan is in state NEW or ACCEPTED, show the form for adding a visit
        if ($daily_plan->isNew() || ($daily_plan->isAccepted() && $halfHourPassed)) {
            $farmsInTheArea = $agronomist->getArea()->getFarms();
            $options = array('farmsInTheArea' => $farmsInTheArea);
            $formToAddVisit =  $this->createForm(AddVisitType::class, null, $options);

            $formToAddVisit->handleRequest($request);

            if($formToAddVisit->isSubmitted() && $formToAddVisit->isValid()) {
                $formData = $formToAddVisit->getData();
                try {
                    $dpService->addVisit($agronomist, $daily_plan, $formData['farm'], $formData['startingHour']);
                    $this->em->persist($daily_plan);
                    $this->em->flush();
                    $this->redirectToRoute($request->attributes->get('_route'));
                } catch (\Exception $e) {
                    $errorMsg = 'The visit cannot be added';
                }
            }
        }

        // if the daily plan is in state NEW or ACCEPTED, show for each visit a form to remove it
        if ($daily_plan->isNew() || ($daily_plan->isAccepted() && $halfHourPassed)) {
            $formsToRemoveVisits = array();

            foreach ($daily_plan->getFarmVisits() as $farmVisit) {
                $formToRemoveVisit = $this->createForm(RemoveVisitType::class, null, ['visitToRemove' => $farmVisit->getId()]);
                $formsToRemoveVisits += [$farmVisit->getId() => $formToRemoveVisit->createView()];

                $formToRemoveVisit->handleRequest($request);

                if($formToRemoveVisit->isSubmitted() && $formToRemoveVisit->isValid()) {
                    $formData = $formToRemoveVisit->getData();
                    $visitToRemove = $this->em->getRepository(FarmVisit::class)->find($formData['visit']);
                    try {
                        $dpService->removeVisit($agronomist, $daily_plan, $visitToRemove);
                        $this->em->persist($daily_plan);
                        $this->em->flush();
                        $this->redirectToRoute($request->attributes->get('_route'));
                    } catch (\Exception $e) {
                        $errorMsg = 'The visit cannot be removed';
                    }
                }
            }
        }

        // if the daily plan is in the state NEW, show form for accepting the daily plan
        if($daily_plan->isNew()) {
            $formToAcceptDailyPlan = $this->createForm(AcceptDailyPlanType::class);

            $formToAcceptDailyPlan->handleRequest($request);

            if ($formToAcceptDailyPlan->isSubmitted() && $formToAcceptDailyPlan->isValid()) {
                try {
                    $dpService->acceptDailyPlan($agronomist, $daily_plan);
                    $this->em->persist($daily_plan);
                    $this->em->flush();
                    $this->redirectToRoute($request->attributes->get('_route'));
                } catch (\Exception $e) {
                    $errorMsg = 'Daily plan not acceptable';
                }
            }
        }

        // if the daily plan is in the state ACCEPTED and more than half an hour has passed from the
        // starting time of the last visit of the day, show form for confirming the daily plan


        if($daily_plan->isAccepted() && $halfHourPassed) {
            $formToConfirmDailyPlan = $this->createForm(ConfirmDailyPlanType::class);

            $formToConfirmDailyPlan->handleRequest($request);

            if ($formToConfirmDailyPlan->isSubmitted() && $formToConfirmDailyPlan->isValid()) {
                try {
                    $dpService->confirmDailyPlan($agronomist, $daily_plan);
                    $this->em->persist($daily_plan);
                    $this->em->flush();
                    $this->redirectToRoute('daily_plan_insert_visits_feedbacks',
                        ['daily_plan' => $daily_plan->getId()]);
                } catch (\Exception $e) {
                    $errorMsg = 'Daily plan not confirmable';
                }
            }
        }

        return $this->render('dailyplan/daily_plan.html.twig', [
            'daily_plan' => $daily_plan,
            'error_msg' => $errorMsg,
            'forms_move_visits' => $formsToMoveVisits,
            'form_add_visit' => is_null($formToAddVisit) ? null : $formToAddVisit->createView(),
            'forms_remove_visits' => $formsToRemoveVisits,
            'form_accept_daily_plan' => is_null($formToAcceptDailyPlan) ? null : $formToAcceptDailyPlan->createView(),
            'form_confirm_daily_plan' => is_null($formToConfirmDailyPlan) ? null : $formToConfirmDailyPlan->createView()
        ]);
    }

    #[Route('/daily_plan/farm_details/{daily_plan}/{farm}', name: 'farm_details', methods: ['GET'])]
    public function getFarmDetails(Request $request, DailyPlan $daily_plan, Farm $farm) : \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not an agronomist, error
        $agronomist = $this->getUser();
        if (!($agronomist instanceof Agronomist)) {
            throw new AssertionError();
        }

        $dateOfLastVisit = $this->em->getRepository(FarmVisit::class)
            ->getDateOfLastVisitToFarmByAgronomist($agronomist, $farm);
        $productionData = $this->em->getRepository(ProductionData::class)
            ->findProductionDataOfFarmInPeriod($farm, $dateOfLastVisit, new \DateTime());

        return $this->render('dailyplan/farm_details.html.twig', ['production_data' => $productionData, 'daily_plan' => $daily_plan, 'farm' => $farm]);
    }
    #[Route('/daily_plan/insert_visits_feedbacks/{daily_plan}', name: 'insert_visits_feedbacks', methods: ['GET', 'POST'])]
    public function insertFarmVisitsFeedbacks(Request $request, DailyPlan $daily_plan): \Symfony\Component\HttpFoundation\Response
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

        $visitsThatNeedFeedback = $daily_plan->getFarmVisits();

        $form = $this->createForm(InsertFarmVisitsFeedbacksType::class, null,
            ['farmVisits' => $visitsThatNeedFeedback]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $feedbacks = new ArrayCollection();
            /** @var FarmVisit $farmVisit */
            foreach ($visitsThatNeedFeedback as $farmVisit) {
                $feedbacks->set($farmVisit->getId(),
                    $formData[$farmVisit->getId()]);
            }
            try {
                (new DailyPlanService($this->em->getRepository(FarmVisit::class)))
                    ->insertVisitsFeedbacks($agronomist, $daily_plan, $feedbacks);
                $this->em->persist($daily_plan);
                $this->em->flush();
                return $this->redirectToRoute('daily_plan_date',
                    ['daily_plan' => $daily_plan->getId()]);
            } catch(\Exception $e) {
                throw new BadRequestException($e->getMessage());
            }
        }

        return $this->render('dailyplan/insert_visits_feedbacks.html.twig', ['form' => $form->createView()]);
    }
}

