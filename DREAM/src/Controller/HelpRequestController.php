<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\HelpRequest;
use App\Form\HelpRequests\NewHelpRequestType;
use AssertionError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/my_requests', name: 'my_requests_')]
class HelpRequestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
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
    { // cercare se si può evitare questo metodo, perché è inutile
        return $this->render('myrequests/select_expert_type.html.twig', []);
    }

    #[Route('/new_request{type}', name: 'new_request', methods: ['GET'])]
    public function getNewRequestPage(Request $request, string $type): \Symfony\Component\HttpFoundation\Response
    {
        $experts = null;
        $options = array('experts' => $experts);
        $form = $this->createForm(NewHelpRequestType::class, null, $options);
        return $this->render('myrequests/new_request.html.twig', []);
    }
}