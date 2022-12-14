<?php

namespace App\Repository\ProductionData;

use App\Entity\Farm;
use App\Entity\ProductionData\ProductionData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductionData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductionData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductionData[]    findAll()
 * @method ProductionData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionData::class);
    }

    public function findProductionDataOfFarmInPeriod(Farm $farm, \DateTime $minDate, \DateTime $maxDate)
    {
        return $this->_em->createQuery(
            'SELECT p
                 FROM App\Entity\ProductionData\ProductionData p 
                 WHERE p.farm = :farm AND (p.date BETWEEN :minDate AND :maxDate)'
        )->setParameter('farm', $farm)
            ->setParameter('minDate', $minDate)
            ->setParameter('maxDate', $maxDate)
            ->getResult();
    }

    // /**
    //  * @return ProductionData[] Returns an array of ProductionData objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProductionData
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
