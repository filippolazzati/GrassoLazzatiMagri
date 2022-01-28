<?php

namespace App\Repository\ProductionData;

use App\Entity\ProductionData\ProductionDataEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductionDataEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductionDataEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductionDataEntry[]    findAll()
 * @method ProductionDataEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionDataEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductionDataEntry::class);
    }

    // /**
    //  * @return ProductionDataEntry[] Returns an array of ProductionDataEntry objects
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
    public function findOneBySomeField($value): ?ProductionDataEntry
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
