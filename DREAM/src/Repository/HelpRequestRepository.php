<?php

namespace App\Repository;

use App\Entity\Farmer;
use App\Entity\HelpRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HelpRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpRequest[]    findAll()
 * @method HelpRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpRequest::class);
    }

    public function createHelpRequest(Farmer $author, User $receiver, string $title, string $text) : HelpRequest {
        $helpRequest = new HelpRequest(new \DateTime(), $title, $text, $author, $receiver);
        $this->getEntityManager()->persist($helpRequest);
        $this->getEntityManager()->flush();
        return $helpRequest;
    }
}
