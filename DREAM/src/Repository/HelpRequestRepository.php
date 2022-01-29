<?php

namespace App\Repository;

use App\Entity\Farmer;
use App\Entity\HelpRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
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

    public function createHelpRequest(Farmer $author, User $receiver, string $title, string $text): HelpRequest
    {
        $helpRequest = new HelpRequest(new \DateTime(), $title, $text, $author, $receiver);
        $this->getEntityManager()->persist($helpRequest);
        $this->getEntityManager()->flush();
        return $helpRequest;
    }

    public function getHelpRequestsFromFarmerQuery(Farmer $author): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery(
            'SELECT req 
                 FROM App\Entity\HelpRequest req
                 WHERE req.author = :author
                 ORDER BY req.timestamp DESC '
        )->setParameter('author', $author);
    }

    public function getMostRecentHelpRequestFromFarmer(Farmer $author): HelpRequest
    {
        // TODO: change to nested query
        $maxTimestamp = $this->getEntityManager()->createQuery(
            'SELECT MAX(req.timestamp)
                 FROM App\Entity\HelpRequest req
                 WHERE req.author = :author'
        )->setParameter('author', $author)->getScalarResult();
        return $this->getEntityManager()->createQuery(
            'SELECT req
                FROM App\Entity\HelpRequest req
                WHERE (req.author = :author) AND (req.timestamp = :max_timestamp)'
        )->setParameter('author', $author)
            ->setParameter('max_timestamp', $maxTimestamp)
            ->getSingleResult();
    }

    public function getHelpRequestsToUserQuery(User $receiver): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery(
            'SELECT req 
                 FROM App\Entity\HelpRequest req
                 WHERE req.receiver = :receiver
                 ORDER BY req.timestamp DESC '
        )->setParameter('receiver', $receiver);
    }

    public function getMostRecentHelpRequestToUser(User $receiver): HelpRequest
    {
        // TODO: change to nested query
        $maxTimestamp = $this->getEntityManager()->createQuery(
            'SELECT MAX(req.timestamp)
                 FROM App\Entity\HelpRequest req
                 WHERE req.receiver = :receiver'
        )->setParameter('receiver', $receiver)->getScalarResult();
        return $this->getEntityManager()->createQuery(
            'SELECT req
                FROM App\Entity\HelpRequest req
                WHERE (req.receiver = :receiver) AND (req.timestamp = :max_timestamp)'
        )->setParameter('receiver', $receiver)
            ->setParameter('max_timestamp', $maxTimestamp)
            ->getSingleResult();
    }
}
