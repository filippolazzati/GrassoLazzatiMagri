<?php

namespace App\Controller;

use App\Entity\Agronomist;
use App\Entity\Farmer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        if ($this->getUser() instanceof Farmer) {
            return $this->render('home/farmer.html.twig');
        } else if ($this->getUser() instanceof Agronomist) {
            return $this->render('home/agronomist.html.twig');
        }
        return $this->render('base.html.twig');
    }
}