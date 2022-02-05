<?php

namespace App\Controller;

use App\Entity\Farmer;
use App\Entity\ProductionData\HarvestingEntry;
use App\Entity\ProductionData\PlantingSeedingEntry;
use App\Entity\ProductionData\ProductionData;
use App\Entity\ProductionData\ProductionDataEntry;
use App\Form\ProductionDataType;
use AssertionError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/production', name: 'production_data_')]
class ProductionDataController extends AbstractController
{
    #[Required] public EntityManagerInterface $em;
    #[Required] public PaginatorInterface $paginator;

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        /** @var Farmer $farmer */
        $farmer = $this->getUser();

        $query = $this->em->getRepository(ProductionData::class)
            ->createQueryBuilder('data')
            ->addSelect('entry')
            ->join('data.entries', 'entry')
            ->andWhere('data.farm = :farm')
            ->setParameter('farm', $farmer->getFarm())
            ->orderBy('data.createdAt', 'DESC')
            ->getQuery();
        $pagination = $this->paginator->paginate($query, $request->query->getInt('page', 1), 25);

        $plantingStats = $this->em->getRepository(ProductionDataEntry::class)
            ->getPlantingStats($farmer->getFarm());

        return $this->render('production/index.html.twig', [
            'pagination' => $pagination,
            'plantingStats' => $plantingStats,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $user = $this->getUser();
        if (!($user instanceof Farmer)) {
            throw new AssertionError();
        }

        $form = $this->createForm(ProductionDataType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ProductionData $data */
            $data = $form->getData();
            $data->setFarm($user->getFarm());

            $this->em->persist($data);
            $this->em->flush();

            return $this->redirectToRoute('production_data_index');
        }

        $plantingEntries = $this->em->getRepository(ProductionDataEntry::class)
            ->findOpenPlantingEntries($user->getFarm());

        return $this->render('production/form.html.twig', [
            'form' => $form->createView(),
            'plantingEntries' => $plantingEntries,
        ]);
    }
}