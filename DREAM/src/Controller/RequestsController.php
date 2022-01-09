<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\HelpRequests\HelpRequest;

#[Route('/my_requests', name: 'my_requests_')]
class RequestsController extends AbstractController{

    #[Route('/{helpRequest<\d+>?}', name: 'index', methods: ['GET'])]
    // returns MyRequests page, req is the selected request for which details should be shown
    public function index(Request $request, ?HelpRequest $helpRequest): Response {
        return true;
    }
}