<?php

namespace App\Tests\DailyPlan;

use App\Entity\Agronomist;
use App\Entity\Area;
use App\Entity\DailyPlan\DailyPlan;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use App\Entity\Farmer;
use DateTime;
use Doctrine\ORM\EntityManager;

class SampleDatabaseLoader
{
    public static function loadSampleDatabase(EntityManager $em) : void
    {
        // AREA
        $areas = [];
        for($i = 0; $i < 5; $i++) {
            $areas[$i] = new Area('Area' . $i);
        }

        // AGRONOMIST
        $agronomists = [];
        for($i = 0; $i < 8; $i++) {
            $agronomists[$i] = new Agronomist();
            $agronomists[$i]->setEmail('agronomist@email' . $i);
            $agronomists[$i]->setPassword('passwordAgronomist' . $i);
            $agronomists[$i]->setName('agronomistName' . $i);
            $agronomists[$i]->setSurname('agronomistSurname' . $i);
            $agronomists[$i]->setBirthDate(new \DateTime());
            $agronomists[$i]->setArea($areas[$i % 4]);
        }

        // FARMER AND FARM
        $farmers = [];
        $farms = [];
        for ($i = 0; $i < 30; $i++) {
            $farmers[$i] = new Farmer();
            $farmers[$i]->setEmail('farmer@email' . $i);
            $farmers[$i]->setPassword('passwordFarmer' . $i);
            $farmers[$i]->setName('farmerName' . $i);
            $farmers[$i]->setSurname('farmerSurname' . $i);
            $farmers[$i]->setBirthDate(new \DateTime());
            $farms[$i] = new Farm();
            $farms[$i]->setCity('city' . $i);
            $farms[$i]->setStreet('street' . $i);
            $farmers[$i]->setFarm($farms[$i]);
            if ($i < 8) {
                $areas[0]->addFarm($farms[$i]);
            } else if ($i < 13) {
                $areas[1]->addFarm($farms[$i]);
            } else if ($i < 23) {
                $areas[2]->addFarm($farms[$i]);
            } else {
                $areas[3]->addFarm($farms[$i]);
            }
        }

        $daily_plans = [];

        // DAILY PLAN AND FARM VISIT

        // AREA 0: 5 farmers (0...4) with 1 visit in last year (daily_plans[0]), 1 worst farmer (5) with no visits, 1 worst farmer (6) with
        //         two visits two months ago (daily_plans[1]), 1 farmer (7) with 3 visits in last year (daily_plans[0], daily_plans[1])
        $daily_plans[0] = new DailyPlan();
        $daily_plans[0]->setDate(new DateTime('2022-01-01'));
        $daily_plans[0]->setState(DailyPlan::CONFIRMED);
        $daily_plans[0]->setAgronomist($agronomists[0]);
        for ($i = 0; $i < 5; $i++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setStartTime(new DateTime('08:00'));
            $farmVisit->setFarm($farms[$i]);
            $daily_plans[0]->addFarmVisit($farmVisit);
        }

        $farmers[5]->setWorstPerforming(true);
        $farmers[6]->setWorstPerforming(true);

        $daily_plans[1] = new DailyPlan();
        $daily_plans[1]->setDate(new DateTime('2021-12-15'));
        $daily_plans[1]->setState(DailyPlan::CONFIRMED);
        $daily_plans[1]->setAgronomist($agronomists[4]);
        for ($i = 0; $i < 2; $i++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setStartTime(new DateTime('08:00'));
            $farmVisit->setFarm($farms[6]);
            $daily_plans[1]->addFarmVisit($farmVisit);
        }

        for ($i = 0; $i < 3; $i++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setStartTime(new DateTime('08:00'));
            $farmVisit->setFarm($farms[7]);
            if ($i == 0) {
                $daily_plans[0]->addFarmVisit($farmVisit);
            } else {
                $daily_plans[1]->addFarmVisit($farmVisit);
            }
        }

        // AREA 1: 3 worst farmers (8, 9, 10) with 0 visits, 2 farmers (11, 12) with 4 visits in last year (daily_plans[2], ..., daily_plans[5])
        for ($i = 8; $i < 11; $i++) {
            $farmers[$i]->setWorstPerforming(true);
        }
        for ($i = 2; $i < 6; $i++) {
            $daily_plans[$i] = new DailyPlan();
            $daily_plans[$i]->setDate(new DateTime('2022-01-0' . $i));
            $daily_plans[$i]->setState(DailyPlan::CONFIRMED);
            $daily_plans[$i]->setAgronomist($agronomists[1]);
            for ($j = 11; $j < 13; $j++) {
                $farmVisit = new FarmVisit();
                $farmVisit->setStartTime(new DateTime('08:00'));
                $farmVisit->setFarm($farms[$j]);
                $daily_plans[$i]->addFarmVisit($farmVisit);
            }
        }

        // AREA 2: 5 farmers with 3 visits in last year (18...22), 5 farmers (13...17) with 4 visits in last year(daily_plans[6] ... daily_plans[9])
        for ($i = 6; $i < 9; $i++) {
            $daily_plans[$i] = new DailyPlan();
            $daily_plans[$i]->setDate(new DateTime('2022-01-0' . $i));
            $daily_plans[$i]->setState(DailyPlan::CONFIRMED);
            $daily_plans[$i]->setAgronomist($agronomists[2]);
            for ($j = 13; $j < 23; $j++) {
                $farmVisit = new FarmVisit();
                $farmVisit->setStartTime(new DateTime('08:00'));
                $farmVisit->setFarm($farms[$j]);
                $daily_plans[$i]->addFarmVisit($farmVisit);
            }
        }

        $daily_plans[9] = new DailyPlan();
        $daily_plans[9]->setDate(new DateTime('2022-01-0' . $i));
        $daily_plans[9]->setState(DailyPlan::CONFIRMED);
        $daily_plans[9]->setAgronomist($agronomists[6]);
        for ($j = 13; $j < 18; $j++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setStartTime(new DateTime('08:00'));
            $farmVisit->setFarm($farms[$j]);
            $daily_plans[9]->addFarmVisit($farmVisit);
        }

        // AREA 3: 3 farmers (23...25) with no visits, 2 farmers (26, 27) with 1 visit in last year (daily_plans[10]),
        //         2 farmers (28,29) with 2 visits in last year (daily_plans[10], daily_plans[11])
        for ($i = 10; $i < 12; $i++) {
            $daily_plans[$i] = new DailyPlan();
            $daily_plans[$i]->setDate(new DateTime('2022-01-' . $i));
            $daily_plans[$i]->setState(DailyPlan::CONFIRMED);
            $daily_plans[$i]->setAgronomist($agronomists[3]);
            for ($j = 26; $j < 30; $j++) {
                $farmVisit = new FarmVisit();
                $farmVisit->setStartTime(new DateTime('08:00'));
                $farmVisit->setFarm($farms[$j]);
                $daily_plans[$i]->addFarmVisit($farmVisit);
            }
        }

        for ($j = 28; $j < 30; $j++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setStartTime(new DateTime('08:00'));
            $farmVisit->setFarm($farms[$j]);
            $daily_plans[11]->addFarmVisit($farmVisit);
        }

        // AREA 4: no visits

        // persist entities
        for ($i = 0; $i < 5; $i++) {
            $em->persist($areas[$i]);
        }
        for ($i = 0; $i < 8; $i++) {
            $em->persist($agronomists[$i]);
        }
        for ($i = 0; $i < 30; $i++) {
            $em->persist($farmers[$i]);
        }
        for ($i = 0; $i < 12; $i++) {
            $em->persist($daily_plans[$i]);
        }
        $em->flush();
    }
}