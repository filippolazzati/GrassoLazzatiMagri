<?php

namespace App\Repository\DailyPlan;

use App\Entity\Area;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FarmVisit|null find($id, $lockMode = null, $lockVersion = null)
 * @method FarmVisit|null findOneBy(array $criteria, array $orderBy = null)
 * @method FarmVisit[]    findAll()
 * @method FarmVisit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FarmVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FarmVisit::class);
    }

    /**
     * Returns the farms in the given area that received in the period between minDate and maxDate (both included)
     * a number of visits (strictly) less than $numVisits.
     * A number maxResults of farms is returned, or less  if there are not maxResults farms satisfying the condition.
     * If there are more than maxResults farms satisfying the condition, the farms returned are the maxResults
     * ones with the smaller number of visits among the other.
     * If onlyWorstPerforming = true, only farms belonging to farmers marked as worst performing are selected.
     * @param Area $area an area in Telangana
     * @param DateTime $minDate only visits in date greater than or equal minDate are considered
     * @param DateTime $maxDate only visits in date less than or equal than minDate are considered
     * @param int $numVisits the farms returned were visited a number of times (strictly) less than numVisits
     * @param int $maxResults the maximum number of results obtained
     * @param bool $onlyWorstPerforming if true, only farms of worst performing farmers are considered, otherwise
     * all farms are considered
     * @return array an array of maxResults Farm objects, such that in the period between minDate and maxDate
     * the farms were visited a number of times less than numVisits
     */
    public function getFarmsWithNumberOfVisitsLessThan(
        Area $area, DateTime $minDate, DateTime $maxDate, int $numVisits, int $maxResults, bool $onlyWorstPerforming): array
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('f')
            ->from('App\Entity\Farm', 'f')
            ->leftJoin('f.farmVisits', 'fv' )
            ->join('fv.dailyPlan', 'dp')
            ->join('f.farmer', 'fmr')
            ->where('dp.date BETWEEN :minDate AND :maxDate')
            ->andWhere('f.area = :area');

        if ($onlyWorstPerforming) {
            $qb = $qb->andWhere('fmr.worst_performing = true');
        }

        $qb = $qb->groupBy('fv.farm')
            ->having('COUNT(fv.dailyPlan)< :numVisits')
            ->orderBy('COUNT(fv.dailyPlan)', 'ASC')
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('area', $area)
            ->setParameter('numVisits', $numVisits);
            //->setMaxResults($maxResults);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the date of the last visit scheduled for a farm in the given area.
     * The visit can belong to a daily plan of whichever state (NEW, ACCEPTED, CONFIRMED).
     * @param Area $area
     * @return DateTime
     */
    public function getDateOfLastVisitToArea(Area $area): DateTime
    {
        return $this->_em->createQuery(
            'SELECT MAX(fv.dailyPlan.date)
                 FROM App\Entity\Dailyplan\FarmVisit fv
                 WHERE fv.farm.area = :area'
        )->setParameter('area', $area)
            ->getOneOrNullResult();
    }

    /**
     * Returns the minimun number of visits scheduled for a farm in the given area between minDate and maxDate.
     * The visit can belong to a daily plan of whichever state (NEW, ACCEPTED, CONFIRMED).
     * @param Area $area
     * @param DateTime $minDate
     * @param DateTime $maxDate
     * @return mixed
     */
    public function getMinNumberOfVisitsInPeriod(Area $area, \DateTime $minDate, DateTime $maxDate)
    {
        // TODO :CHANGE TO NESTED QUERY

        $numberOfVisitsInPeriod = $this->_em->createQuery(
            'SELECT COUNT(fv)
                 FROM App\Entity\DailyPlan\FarmVisit fv
                 WHERE (fv.dailyPlan.date BETWEEN :minDate AND :maxDate) AND (fv.farm.area = :area)
                 GROUP BY fv.farm'
        )->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('area', $area)
            ->getResult();

        return min($numberOfVisitsInPeriod);
    }

    public function getNumberOfVisitsToFarmInPeriod(Farm $farm, \DateTime $minDate, DateTime $maxDate): int
    {
        return $this->_em->createQuery(
            'SELECT COUNT(fv)
                FROM App\Entity\DailyPlan\FarmVisit fv
                WHERE (fv.dailyPlan.date BETWEEN :minDate AND :maxDate) AND fv.farm = :farm'
        )->setParameter('farm', $farm)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->getScalarResult();
    }
}
