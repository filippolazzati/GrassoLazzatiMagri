<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\HelpReply;
use App\Entity\HelpRequest;
use App\Entity\User;
use App\Form\HelpRequests\InsertFeedbackType;
use App\Form\HelpRequests\NewHelpRequestType;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/my_requests', name: 'my_requests_')]
class HelpRequestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/view/{help_request?}', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, ?HelpRequest $help_request): \Symfony\Component\HttpFoundation\Response
    {
        // if the user is not a farmer, error
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            throw new AssertionError();
        }

        // retrieve help requests of the user, paginating them in group of 20
        $helpRequestsQuery = $this->em->getRepository(HelpRequest::class)->getHelpRequestsFromFarmerQuery($farmer);
        $pagination = $this->paginator->paginate($helpRequestsQuery, $request->query->getInt('page', 1), 20);

        // if no help request selected (parameter $help_request = null), show as a default the most recent one
        // if the farmer has no help requests, help_request is left to null
        if (is_null($help_request) && !$farmer->getHelpRequests()->isEmpty()) {
            $help_request = $this->em->getRepository(HelpRequest::class)->getMostRecentHelpRequestFromFarmer($farmer);
        }

        $renderParameters = ['pagination' => $pagination, 'help_request' => $help_request];

        // form for inserting the feedback for the response to the selected help request
        // (if it has already been replied and has not a feedback)
        if (!is_null($help_request) && $help_request->needsFeedback()) {
            $form = $this->createForm(InsertFeedbackType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $this->em->getRepository(HelpReply::class)->addFeedbackToReply($help_request, $formData['feedback']);
                return $this->redirectToRoute('my_requests_index', ['help_request' => $help_request->getId()]);
            }
            $renderParameters += array('form' => $form->createView());
        }

        return $this->render('myrequests/index.html.twig', $renderParameters);
    }

    #[Route('/select_expert_type', name: 'select_expert_type', methods: ['GET'])]
    public function getSelectExpertPage(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('myrequests/select_expert_type.html.twig', []);
    }


    #[Route('/new_help_request{type}', name: 'new_help_request',
        /*requirements: ['type' => 'agronomist | best_farmer'],*/ methods: ['GET', 'POST'])]
    public function newHelpRequest(Request $request, string $type): \Symfony\Component\HttpFoundation\Response
    {
        // the user must be a farmer
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            throw new AssertionError();
        }

        // create the form for creating a new help request
        if (strcmp($type, 'agronomist') == 0) { // type = agronomist
            $experts = $farmer->getFarm()->getArea()->getAgronomists()->toArray();
        } else { // type = best_farmer
            $experts = $this->em->getRepository(User::class)->getBestPerformingFarmersExcept($farmer);
        }
        $options = array('experts' => $experts);
        $form = $this->createForm(NewHelpRequestType::class, null, $options);

        $form->handleRequest($request);

        // when the user submits the form, create the new help request
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $helpRequest = $this->em->getRepository(HelpRequest::class)->createHelpRequest(
                $farmer, $formData['receiver'], $formData['title'], $formData['text']);
            return $this->redirectToRoute('my_requests_confirmation_new_help_request',
                ['help_request' => $helpRequest->getId()]);
        }
        return $this->render('myrequests/new_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation_new_help_request{help_request}', name: 'confirmation_new_help_request', methods: ['GET'])]
    public function getConfirmPageForNewHelpRequest(Request $request, HelpRequest $help_request) : \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('myrequests/confirm_insert_request.html.twig', ['help_request' => $help_request]);
    }
}