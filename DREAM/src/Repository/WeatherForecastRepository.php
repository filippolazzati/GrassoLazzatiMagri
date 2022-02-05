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
 *
 * This class represents a repository of WeatherForecast objects to interact with. It provides some methods
 * to query the repository.
 */
class WeatherForecastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherForecast::class);
    }


    /**
     * This method allows to retrieve all the weather forecasts for a certain city.
     */
    public function findAllForecasts($city): array
    {
        // to find only forecasts for next days (needed just because it is a demo, and the sample dataset may not be up-to-date)
        $current_date = date('Y-m-d');

        return $this->createQueryBuilder('w')
            ->andWhere('w.date > :current_date')
            ->setParameter('current_date', $current_date)
            ->andWhere('w.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }
}
