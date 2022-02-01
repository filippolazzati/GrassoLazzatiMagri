<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\FarmVisit;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
     * If onlyWorstPerforming = true, only farms belonging to farmers marked as worst performing are selected
     * @param Area $area an area in Telangana
     * @param DateTime $minDate
     * @param DateTime $maxDate
     * @param int $numVisits
     * @param int $maxResults
     * @param bool $onlyWorstPerforming
     * @return ArrayCollection
     */
    public function getFarmsWithNumberOfVisitsLessThan(
        Area $area, DateTime $minDate, DateTime $maxDate, int $numVisits, int $maxResults, bool $onlyWorstPerforming)
        : ArrayCollection
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('fv.farm, COUNT(fv.dailyPlan.id)')
            ->from('App\Entity\FarmVisit', 'fv')
            ->where('fv.dailyPlan.date BETWEEN :minDate AND :maxDate')
            ->andWhere('fv.farm.area = :area');

        if($onlyWorstPerforming) {
            $qb = $qb->andWhere('fv.farm.farmer.worst_performing = true');
        }

        $qb = $qb->groupBy('fv.farm')
            ->having('fv.dailyPlan.id) < :numVisits')
            ->orderBy('COUNT(fv.dailyPlan.id) ASC')
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('area', $area)
            ->setParameter('numVisits', $numVisits)
            ->setMaxResults($maxResults);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the date of the last visit scheduled for a farm in the given area.
     * The visit can belong to a daily plan of whichever state (NEW, ACCEPTED, CONFIRMED).
     * @param Area $area
     * @return DateTime
     */
    public function getDateOfLastVisit(Area $area) : DateTime
    {
        return $this->_em->createQuery(
            'SELECT MAX(fv.dailyPlan.date)
                 FROM App\Entity\FarmVisit fv
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

        $numberOfVisitsInLastYear = $this->_em->createQuery(
            'SELECT COUNT(fv)
                 FROM App\Entity\FarmVisit fv
                 WHERE (fv.dailyPlan.date BETWEEN :minDate AND :maxDate) AND (fv.farm.area = :area)
                 GROUP BY fv.farm'
        )->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->setParameter('area', $area)
            ->getResult();

        return min($numberOfVisitsInLastYear);
    }

}
