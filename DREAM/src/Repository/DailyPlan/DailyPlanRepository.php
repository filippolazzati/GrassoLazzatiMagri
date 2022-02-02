<?php

namespace App\Repository\DailyPlan;

use App\Entity\DailyPlan\DailyPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findDailyPlanByAgronomistAndDate($agronomist, $date) : ?DailyPlan
    {
        return $this->_em->createQuery(
            'SELECT dp
                 FROM App\Entity\DailyPlan dp
                 WHERE dp.agronomist = :agronomist AND dp.date = :date'
            )->setParameter('agronomist', $agronomist)
            ->setParameter('date', $date)
            ->getOneOrNullResult();
    }

    public function hasDailyPlan($agronomist, $date) : bool
    {
        return !is_null($this->findDailyPlanByAgronomistAndDate($agronomist, $date));
    }

    public function createDailyPlan($agronomist, $date, $numberOfVisits)
    {
        // farm to visit in daily plan: farm with fewer visits in last year; if more farms than $numberOfVisits,
        // take the ones with the min date of last visit
    }
}
