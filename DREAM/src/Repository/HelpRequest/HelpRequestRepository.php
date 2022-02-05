<?php

namespace App\Repository\HelpRequest;

use App\Entity\Farmer;
use App\Entity\HelpRequest\HelpRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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

    /**
     * Returns a Query object for retrieving from the database the HelpRequests sent by the given farmer.
     * @param Farmer $author the author of the help requests to retrieve
     * @return Query a query for retrieving from the database the HelpRequests sent by the given farmer
     */
    public function getHelpRequestsFromFarmerQuery(Farmer $author): Query
    {
        return $this->getEntityManager()->createQuery(
            'SELECT req 
                 FROM App\Entity\HelpRequest\HelpRequest req
                 WHERE req.author = :author
                 ORDER BY req.timestamp DESC '
        )->setParameter('author', $author);
    }

    /**
     * Returns the most recent help request sent by the given farmer, or null if the farmer has never sent
     * a help request.
     * @param Farmer $author the author of the help request to retrieve
     * @return HelpRequest|null the most recent help request sent by the farmer, or null if the farmeer
     * has never sent a help request
     */
    public function getMostRecentHelpRequestFromFarmer(Farmer $author): ?HelpRequest
    {
        $maxTimestamp = $this->getEntityManager()->createQuery(
            'SELECT MAX(req.timestamp)
                 FROM App\Entity\HelpRequest\HelpRequest req
                 WHERE req.author = :author'
        )->setParameter('author', $author)->getOneOrNullResult();

        if (is_null($maxTimestamp)) { // the farmer has never sent a help request
            return null;
        } else {
            return $this->getEntityManager()->createQuery(
                'SELECT req
                FROM App\Entity\HelpRequest\HelpRequest req
                WHERE (req.author = :author) AND (req.timestamp = :max_timestamp)'
            )->setParameter('author', $author)
                ->setParameter('max_timestamp', $maxTimestamp)
                ->getSingleResult();
        }
    }

    /**
     * Returns a Query object to retrieve the help requests having the provided user as the receiver.
     * @param User $receiver the receiver of the help requests to retrieve
     * @return Query a query to retrieve the help requests having the provided user as the receiver
     */
    public function getHelpRequestsToUserQuery(User $receiver): Query
    {
        return $this->getEntityManager()->createQuery(
            'SELECT req 
                 FROM App\Entity\HelpRequest\HelpRequest req
                 WHERE req.receiver = :receiver
                 ORDER BY req.timestamp DESC '
        )->setParameter('receiver', $receiver);
    }

    /**
     * Returns the most recent help request sent to the user, or null if the user never received a
     * help request.
     * @param User $receiver the receiver of the help request to retrieve
     * @return HelpRequest|null the most recent help request sent to the user, or null if the user
     * never received a help request
     */
    public function getMostRecentHelpRequestToUser(User $receiver): ?HelpRequest
    {
        $maxTimestamp = $this->getEntityManager()->createQuery(
            'SELECT MAX(req.timestamp)
                 FROM App\Entity\HelpRequest\HelpRequest req
                 WHERE req.receiver = :receiver'
        )->setParameter('receiver', $receiver)->getOneOrNullResult();
        if (is_null($maxTimestamp)) {
            return null;
        } else {
            return $this->getEntityManager()->createQuery(
                'SELECT req
                FROM App\Entity\HelpRequest\HelpRequest req
                WHERE (req.receiver = :receiver) AND (req.timestamp = :max_timestamp)'
            )->setParameter('receiver', $receiver)
                ->setParameter('max_timestamp', $maxTimestamp)
                ->getSingleResult();
        }
    }
}
