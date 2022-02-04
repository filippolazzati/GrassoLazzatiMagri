<?php

namespace App\Repository\DailyPlan;

use App\Entity\Agronomist;
use App\Entity\DailyPlan\DailyPlan;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DailyPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyPlan[]    findAll()
 * @method DailyPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyPlan::class);
    }

    /**
     * Returns the daily plan of the given agronomist for the given date, or null if the given agronomist
     * does not have a daily plan for that date.
     * @param Agronomist $agronomist the agronomist for which to search the daily plan
     * @param DateTime $date the date for which to search the daily plan
     * @return DailyPlan|null the daily plan of the given agronomist for the given date, or null if the given agronomist
     * does not have a daily plan for that date
     */
    public function findDailyPlanByAgronomistAndDate(Agronomist $agronomist, DateTime $date) : ?DailyPlan
    {
        return $this->_em->createQuery(
            'SELECT dp
                 FROM App\Entity\DailyPlan\DailyPlan dp
                 WHERE dp.agronomist = :agronomist AND dp.date = :date'
            )->setParameter('agronomist', $agronomist)
            ->setParameter('date', $date)
            ->getOneOrNullResult();
    }

    /**
     * Retrieves daily plans of the given agronomist, in state either NEW or ACCEPTED, belonging to a date
     * prior to the given one.
     * @param Agronomist $agronomist the agronomist for which to retrieve the daily plans
     * @param DateTime $date daily plans retrieved are relative to dates prior to $date
     * @return array the daily plans of the given agronomist, in state either NEW or ACCEPTED, belonging to a date
     * prior to the given one
     */
    public function findNotConfirmedPastDailyPlansOfAgronomist(Agronomist $agronomist, DateTime $date) : array
    {
        return $this->_em->createQuery(
            "SELECT dp
                 FROM App\Entity\DailyPlan\DailyPlan dp
                 WHERE dp.agronomist = :agronomist AND dp.date < :date
                  AND (dp.state = 'NEW' or dp.state = 'ACCEPTED')"
        )->setParameter('agronomist', $agronomist)
            ->setParameter('date', $date)
            ->getResult();
    }
}
