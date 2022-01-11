<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/my_replies', name: 'my_replies_')]
class HelpRequestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request) {

    }
}