<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\HelpRequest;
use App\Entity\User;
use App\Form\HelpRequests\NewHelpRequestType;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/my_requests', name: 'my_requests_')]
class HelpRequestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Required] public EntityManagerInterface $em;

    #[Route('/{help_request?}', name: 'index', methods: ['GET'])]
    public function index(Request $request, ?HelpRequest $help_request): \Symfony\Component\HttpFoundation\Response
    {
        $farmer = $this->getUser();
        if (!($farmer instanceof Farmer)) {
            throw new AssertionError();
        }
        $helpRequests = $farmer->getHelpRequests();
        return $this->render('myrequests/index.html.twig',
            ['help_requests' => $helpRequests]);
    }

    #[Route('/select_expert_type', name: 'select_expert_type', methods: ['GET'])]
    public function getSelectExpertPage(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('myrequests/select_expert_type.html.twig', []);
    }

    #[Route('/new_help_request{type}', name: 'new_help_request',
        requirements: ['type' => '/(agronomist | best_farmer)/'], methods: ['GET', 'POST'])]
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
            $experts = $this->em->getRepository(User::class)->getBestPerformingFarmers();
        }
        $options = array('experts' => $experts);
        $form = $this->createForm(NewHelpRequestType::class, null, $options);

        $form->handleRequest($request);

        // when the user submits the form, create the new help request
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $helpRequest = $this->em->getRepository(HelpRequest::class)->createHelpRequest(
                $farmer, $formData['receiver'], $formData['title'], $formData['text']);
            return $this->redirectToRoute('/confirmation_new_help_request{help_request}',
                ['help_request' => $helpRequest]);
        }
        return $this->render('myrequests/new_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation_new_help_request{help_request}', name: 'confirmation_new_help_request', methods: ['GET'])]
    public function getConfirmPageForNewHelpRequest() {

    }
}