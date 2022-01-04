<?php

namespace App\Repository;

use App\Entity\ProductionData;
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
