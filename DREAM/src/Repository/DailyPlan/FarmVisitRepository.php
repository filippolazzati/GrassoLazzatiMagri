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
     * @param int $numVisits the farms returned were visited a number of times (strictly) less than numVisits (numVisits should be a strictly positive number)
     * @param int $maxResults the maximum number of results obtained
     * @param bool $onlyWorstPerforming if true, only farms of worst performing farmers are considered, otherwise
     * all farms are considered
     * @return array an array of maxResults Farm objects, such that in the period between minDate and maxDate
     * the farms were visited a number of times less than numVisits
     */
    public function getFarmsWithNumberOfVisitsLessThan(
        Area $area, DateTime $minDate, DateTime $maxDate, int $numVisits, int $maxResults, bool $onlyWorstPerforming): array
    {
        // to take into consideration farms that were never visited in the period (can be done in SQL, no time to do it :( )
         $farmsWithNoVisits =  $this->_em->createQuery(
            'SELECT f
               FROM App\Entity\Farm f JOIN f.farmer fmr
               WHERE f.area = :area AND ' . ($onlyWorstPerforming ? 'fmr.worst_performing = true AND' : '') . ' NOT EXISTS (
                    SELECT fv
                    FROM App\Entity\DailyPlan\FarmVisit fv LEFT JOIN fv.dailyPlan dp
                    WHERE fv.farm = f AND (dp.date BETWEEN :minDate AND :maxDate)
               )'
        )->setParameter('minDate', $minDate->format('Y-m-d'))
            ->setParameter('maxDate', $maxDate->format('Y-m-d'))
            ->setParameter('area', $area)
             ->setMaxResults($maxResults)
            ->getResult();

         if (count($farmsWithNoVisits) != $maxResults) {
             $qb = $this->_em->createQueryBuilder()
                 ->select('f')
                 ->from('App\Entity\Farm', 'f')
                 ->leftJoin('f.farmVisits', 'fv')
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
                 ->setParameter('numVisits', $numVisits)
                 ->setMaxResults($maxResults - count($farmsWithNoVisits));

             $farmsWithVisits = $qb->getQuery()->getResult();

             foreach ($farmsWithVisits as $farm) {
                 $farmsWithNoVisits[] = $farm;
             }

         }
        return $farmsWithNoVisits;


    }

    /**
     * Returns the date of the last visit scheduled for a farm in the given area.
     * The visit can belong to a daily plan of whichever state (NEW, ACCEPTED, CONFIRMED).
     * @param Area $area an area in Telangana
     * @return DateTime|null the date of the last visit scheduled for a farm in the given area, or null if there were never
     * visits in the area
     */
    public function getDateOfLastVisitToArea(Area $area): ?DateTime
    {


        $date = $this->_em->createQuery(
            'SELECT DISTINCT MAX(dp.date)
                 FROM App\Entity\Dailyplan\DailyPlan dp JOIN dp.farmVisits fv JOIN fv.farm f
                 WHERE f.area = :area'
        )->setParameter('area', $area)
            ->getSingleScalarResult();

        if ($date != null) {
            return new DateTime($date);
        } else {
            return null;
        }

    }

    /**
     * Returns the minimum number of visits scheduled for a farm in the given area between minDate and maxDate (included).
     * The visit can belong to a daily plan of whichever state (NEW, ACCEPTED, CONFIRMED).
     * @param Area $area an area in Telangana
     * @param DateTime $minDate the lower bound of the time period to be considered (included)
     * @param DateTime $maxDate the upper bound of the time period to be considered (included)
     * @return int the minimum number of visits scheduled for a farm in the given area between minDate and maxDate (included)
     */
    public function getMinNumberOfVisitsInPeriod(Area $area, \DateTime $minDate, DateTime $maxDate) : int
    {
        // if there are farms with no visits in period, return 0
        $farmsWithNoVisits =  $this->_em->createQuery(
            'SELECT f
               FROM App\Entity\Farm f JOIN f.farmer fmr
               WHERE f.area = :area AND NOT EXISTS (
                    SELECT fv
                    FROM App\Entity\DailyPlan\FarmVisit fv LEFT JOIN fv.dailyPlan dp
                    WHERE fv.farm = f AND (dp.date BETWEEN :minDate AND :maxDate)
               )'
        )->setParameter('minDate', $minDate->format('Y-m-d'))
            ->setParameter('maxDate', $maxDate->format('Y-m-d'))
            ->setParameter('area', $area)
            ->getResult();

        if (count($farmsWithNoVisits) != 0) {
            return 0;
        }

        $numberOfVisitsInPeriod = $this->_em->createQuery(
            'SELECT COUNT(fv)
                 FROM App\Entity\DailyPlan\FarmVisit fv JOIN fv.dailyPlan dp JOIN fv.farm f
                 WHERE (dp.date BETWEEN :minDate AND :maxDate) AND (f.area = :area)
                 GROUP BY fv.farm'
        )->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('area', $area)
            ->getSingleColumnResult();

        if (count($numberOfVisitsInPeriod) == 0) {
            return 0;
        }

        return min($numberOfVisitsInPeriod);
    }

    /**
     * Returns the number of visits received by the given farm between minDate and maxDate (both included).
     * @param Farm $farm
     * @param DateTime $minDate
     * @param DateTime $maxDate
     * @return int the number of visits received by the given farm between minDate and maxDate (both included)
     */
    public function getNumberOfVisitsToFarmInPeriod(Farm $farm, \DateTime $minDate, DateTime $maxDate): int
    {
        return $this->_em->createQuery(
            'SELECT COUNT(fv)
                FROM App\Entity\DailyPlan\FarmVisit fv JOIN fv.dailyPlan dp
                WHERE (dp.date BETWEEN :minDate AND :maxDate) AND fv.farm = :farm'
        )->setParameter('farm', $farm)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->getSingleScalarResult();
    }
}
