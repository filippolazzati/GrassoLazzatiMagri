<?php

namespace App\Repository\ProductionData;

use App\Entity\Farm;
use App\Entity\ProductionData\HarvestingEntry;
use App\Entity\ProductionData\PlantingSeedingEntry;
use App\Entity\ProductionData\ProductionDataEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @return PlantingSeedingEntry[]
     */
    public function findOpenPlantingEntries(Farm $farm): array
    {
        return  $this->createQueryBuilder('entry')
            ->andWhere('entry INSTANCE OF '.PlantingSeedingEntry::class)
            ->addSelect('parent')
            ->join('entry.parent', 'parent')
            ->leftJoin(HarvestingEntry::class, 'harvestingEntry', Join::WITH, 'harvestingEntry.relatedEntry = entry')
            ->andWhere('parent.farm = :farm')
            ->andHaving('SUM(COALESCE(harvestingEntry.area, 0)) < entry.area')
            ->setParameter('farm', $farm)
            ->orderBy('parent.date', 'DESC')
            ->groupBy('entry.id')
            ->getQuery()->getResult();
    }

    /**
     * @return PlantingSeedingEntry[]
     */
    public function getPlantingStats(Farm $farm): array
    {
        return $this->_em->createQueryBuilder()
            ->from(PlantingSeedingEntry::class, 'entry')
            ->select('entry.crop', 'SUM(entry.area) - SUM(COALESCE(harvestingEntry.area, 0)) as area')
            ->join('entry.parent', 'parent')
            ->leftJoin(HarvestingEntry::class, 'harvestingEntry', Join::WITH, 'harvestingEntry.relatedEntry = entry')
            ->andWhere('parent.farm = :farm')
            ->having('SUM(entry.area) - SUM(COALESCE(harvestingEntry.area, 0)) > 0')
            ->setParameter('farm', $farm)
            ->groupBy('entry.crop')
            ->getQuery()->getArrayResult();
    }
}
