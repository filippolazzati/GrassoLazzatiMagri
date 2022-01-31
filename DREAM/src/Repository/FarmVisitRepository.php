<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\FarmVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    /*
    public function getMinNumberOfVisitsInYearBefore(Area $area, \DateTime $date)
    {
        // TODO: to avoid inefficiency in queries, a view would be necessary
        $numberOfVisitsInLastYear = $this->_em->createQuery(
            'SELECT COUNT(fv)
                 FROM App\Entity\FarmVisit fv
                 WHERE fv.dailyPlan.date >= :date AND fv.farm.area = :area
                 GROUP BY fv.farm'
        )->setParameter('date', $date->sub(new \DateInterval('P1Y')))
            ->setParameter('area', $area)
            ->getResult();

        return min($numberOfVisitsInLastYear);
    }

    public function getFarmsWithNumberOfVisits(Area $area, int $numberOfVisits)
    {
        return $this->_em->createQuery(
            'SELECT fv.farm
                 FROM App\Entity\FarmVisit
                 WHERE fv.farm.area = :area and '
        )
    }*/
}
