<?php

namespace App\Repository;

use App\Entity\WeatherReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WeatherReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeatherReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeatherReport[]    findAll()
 * @method WeatherReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * This class represents a repository of WeatherReport objects to interact with. It provides some methods
 * to query the repository.
 */
class WeatherReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherReport::class);
    }

    /**
     * It returns the WeatherReport that matches the date passed in input and
     * that is relative to the city passed in input.
     */
    public function findOneByMonth($date, $city)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date = :date')
            ->setParameter('date', $date)
            ->andWhere('r.city = :city')
            ->setParameter('city', $city)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
