<?php

namespace App\Tests\ProductionData;

use App\Entity\Area;
use App\Entity\Farm;
use App\Entity\Farmer;
use App\Entity\ProductionData\HarvestingEntry;
use App\Entity\ProductionData\PlantingSeedingEntry;
use App\Entity\ProductionData\ProductionData;
use App\Entity\ProductionData\ProductionDataEntry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductionDataTest extends KernelTestCase
{
    public function testOpenPlantingEntries(): void
    {
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine')->getManager();

        $area = new Area('Area 1');
        $em->persist($area);

        $farmer = new Farmer();
        $farmer->setName('John');
        $farmer->setSurname('Doe');
        $farmer->setEmail('example@example.com');
        $farmer->setBirthDate(new \DateTime());
        $farmer->setPassword('password');

        $farm = (new Farm())->setArea($area)->setCity('City1')->setStreet('Street1');
        $farmer->setFarm($farm);

        $em->persist($farmer);

        $data = new ProductionData();
        $data->setFarm($farm);
        $data->setDate(new \DateTime());
        $data->setComment('Comment');
        $em->persist($data);

        // Add planting entries
        $p1 = (new PlantingSeedingEntry())->setArea(100)->setCrop('tomatoes');
        $p2 = (new PlantingSeedingEntry())->setArea(200)->setCrop('potatoes');
        $data->addEntry($p1);
        $data->addEntry($p2);

        $em->flush();

        $openPlantingEntries = $em->getRepository(ProductionDataEntry::class)->findOpenPlantingEntries($farm);
        $this->assertCount(2, $openPlantingEntries);

        // Add a partial harvesting entry for the first planting entry
        $h1 = (new HarvestingEntry())->setArea(50)->setRelatedEntry($p1);
        $data->addEntry($h1);
        $em->flush();

        $openPlantingEntries = $em->getRepository(ProductionDataEntry::class)->findOpenPlantingEntries($farm);
        $this->assertCount(2, $openPlantingEntries);

        // Add another production data with an harvesting entry closing the first planting
        $data2 = (new ProductionData())->setFarm($farm)->setDate(new \DateTime());
        $h2 = (new HarvestingEntry())->setArea(50)->setRelatedEntry($p1);
        $data2->addEntry($h2);
        $em->persist($data2);
        $em->flush();

        $openPlantingEntries = $em->getRepository(ProductionDataEntry::class)->findOpenPlantingEntries($farm);
        $this->assertCount(1, $openPlantingEntries);
    }
}
