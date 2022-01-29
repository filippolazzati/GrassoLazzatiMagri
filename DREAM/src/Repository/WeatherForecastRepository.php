<?php

namespace App\Repository;

use App\Entity\WeatherForecast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WeatherForecast|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeatherForecast|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeatherForecast[]    findAll()
 * @method WeatherForecast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeatherForecastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherForecast::class);
    }


    // find all the forecasts for $city
    public function findAllForecasts($city): array
    {
        // to find only forecasts for next days
        $current_date = date('Y-m-d');
        return $this->createQueryBuilder('w')
            ->andWhere('w.date > :current_date')
            ->setParameter('current_date', $current_date)
            ->andWhere('w.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?WeatherForecast
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
