<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\HelpRequest\HelpRequest;
use App\Entity\User;
use App\Form\HelpRequests\InsertFeedbackType;
use App\Form\HelpRequests\NewHelpRequestType;
use App\HelpRequests\HelpRequestsService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/my_requests', name: 'my_requests_')]
class HelpRequestController extends AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;
    #[Required] public HelpRequestsService $helpRequestsService;

    #[Route('/view/{help_request?}', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request, ?HelpRequest $help_request): Response
    {
        /** @var Farmer $farmer */
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            $this->createNotFoundException();
        }

        // retrieve help requests of the user, paginating them in group of 20
        $helpRequestsQuery = $this->em->getRepository(HelpRequest::class)->getHelpRequestsFromFarmerQuery($farmer);
        $pagination = $this->paginator->paginate($helpRequestsQuery, $request->query->getInt('page', 1), 20);

        // if a help_request not belonging to the farmer has been selected, error
        if (!is_null($help_request) && !$help_request->getAuthor()->equals($farmer)) {
            $this->createNotFoundException();
        }

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
                $this->helpRequestsService->addFeedbackToReply($help_request, $formData['feedback']);
                $this->em->persist($help_request);
                $this->em->flush();
                return $this->redirectToRoute('my_requests_index', ['help_request' => $help_request->getId()]);
            }
            $renderParameters += array('form' => $form->createView());
        }

        return $this->render('myrequests/index.html.twig', $renderParameters);
    }

    #[Route('/select_expert_type', name: 'select_expert_type', methods: ['GET'])]
    public function getSelectExpertPage(Request $request): Response
    {
        return $this->render('myrequests/select_expert_type.html.twig', []);
    }


    #[Route('/new_help_request{type}', name: 'new_help_request', methods: ['GET', 'POST'])]
    public function newHelpRequest(Request $request, string $type): Response
    {
        // the user must be a farmer
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            $this->createNotFoundException();
        }

        $errorMessage = null;

        // create the form for creating a new help request
        if (strcmp($type, 'agronomist') == 0) { // type = agronomist
            $experts = $farmer->getFarm()->getArea()->getAgronomists()->toArray();
            if (empty($experts)) {
                $errorMessage = 'Sorry, there are not agronomists in your area. Try a request to a best-performing farmer.';
            }
        } else { // type = best_farmer
            $experts = $this->em->getRepository(User::class)->getBestPerformingFarmersExcept($farmer);
            $errorMessage = 'Sorry, there are not any best-performing farmers at the moment. Try a request to an agronomist';
        }

        $options = array('experts' => $experts);
        $form = $this->createForm(NewHelpRequestType::class, null, $options);

        $form->handleRequest($request);

        // when the user submits the form, create the new help request
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            try {
                $helpRequest = $this->helpRequestsService->createHelpRequest(
                    $farmer, $formData['receiver'], $formData['title'], $formData['text']);
                $this->em->persist($helpRequest);
                $this->em->flush();
                return $this->redirectToRoute('my_requests_confirmation_new_help_request',
                    ['help_request' => $helpRequest->getId()]);
            } catch (Exception $e) {
                throw new BadRequestException($e->getMessage());
            }
        }
        return $this->render('myrequests/new_request.html.twig', [
            'form' => $form->createView(),
            'error_message' => $errorMessage
        ]);
    }

    #[Route('/confirmation_new_help_request{help_request}', name: 'confirmation_new_help_request', methods: ['GET'])]
    public function getConfirmPageForNewHelpRequest(Request $request, HelpRequest $help_request): Response
    {
        // the user must be a farmer
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            $this->createNotFoundException();
        }
        // if the help_request does not to the farmer, error
        if (!$help_request->getAuthor()->equals($farmer)) {
            $this->createNotFoundException();
        }
        return $this->render('myrequests/confirm_insert_request.html.twig', ['help_request' => $help_request]);
    }
}