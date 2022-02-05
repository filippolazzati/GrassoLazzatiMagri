<?php

namespace App\Tests\DailyPlan;

use App\DailyPlan\DailyPlanService;
use App\Entity\Agronomist;
use App\Entity\Area;
use App\Entity\DailyPlan\FarmVisit;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class tests the functionality "Generate Daily Plan", provided by DailyPlanService::generateDailyPlan.
 * DailyPlanService::generateDailyPlan uses the FarmVisitRepository, so this test can be seen as a unit test
 * of the unit <DailyPlanService, FarmVisitRepository>. I would not consider it an integration test, as in my
 * opinion the classes <DailyPlanService, FarmVisitRepository, DailyPlanRepository> constitute a unique unit.
 */
class DailyPlanGenerationTest extends KernelTestCase
{
    private EntityManager $em;
    private DailyPlanService $dpService;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // initialize test database
        SampleDatabaseLoader::loadSampleDatabase($this->em);

        $this->dpService = new DailyPlanService($this->em->getRepository(FarmVisit::class));
    }

    public function testNumberOfVisitsGreaterThanNumberOfFarms(): void

    {
        // number of visits = 8, number of farms in area1 = 5
        $agronomist1 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName1']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist1, new \DateTime('2022-03-03'), 8);

        self::assertCount(5, $dailyPlan->getFarmVisits());

        foreach ($agronomist1->getArea()->getFarms() as $farm) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farm) {
                return $farmVisit->getFarm()->getId() == $farm->getId();
            }));
        }

        $startingHours = ['08:00', '09:36', '11:12', '13:48', '15:24'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }), 'missing ' . $startingHour);
        }

    }

    public function testAllFarmersWithLessThanTwoVisitsInYear()
    {
        // area0, numberOfVisits = 5, farmers selected = farmer0...farmer4 (all with less than two visits in a year)
        $agronomist0 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName0']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist0, new \DateTime('2022-03-03'), 5);

        self::assertCount(5, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName0', 'farmerName1', 'farmerName2', 'farmerName3', 'farmerName4'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        $startingHours = ['08:00', '09:36', '11:12', '13:48', '15:24'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testAllWorstFarmersWithNoVisitsInLastMonth()
    {
        // area1, numVisits = 3, farmers selected = [farmer8, farmer9, farmer10]
        $agronomist1 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName1']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist1, new \DateTime('2022-03-03'), 3);

        self::assertCount(3, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName8', 'farmerName9', 'farmerName10'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        $startingHours = ['08:00', '10:40', '14:20'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testAllFarmersWithThreeVisitsInLastYear()
    {
        // area2, numVisits = 5, farmers selected = [farmer13, farmer14, farmer15, farmer16, farmer17]
        $agronomist2 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName2']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist2, new \DateTime('2022-03-03'), 5);

        self::assertCount(5, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName13', 'farmerName14', 'farmerName15', 'farmerName16', 'farmerName17'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }), 'missing: ' . $farmer);
        }

        $startingHours = ['08:00', '09:36', '11:12', '13:48', '15:24'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testSomeFarmersWithThreeAndSomeWithFourVisitsInAYear()
    {
        // area2, numVisits = 6, farmers selected = [farmer13, farmer14, farmer15, farmer16, farmer17] + one in [farmer18...farmer22]
        $agronomist2 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName2']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist2, new \DateTime('2022-03-03'), 6);

        self::assertCount(6, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName13', 'farmerName14', 'farmerName15', 'farmerName16', 'farmerName17'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) {
            return strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName18') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName19') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName20') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName21') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName22') == 0;
        }));

        $startingHours = ['08:00', '09:20', '10:40', '12:00', '14:20', '15:40'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testSomeFarmersWithNoVisitsInLastYearAndSomeWorstWithNoVisitsInLastMonth()
    {
        // area0, numberOfVisits = 7, farmers selected = farmer0...farmer6
        $agronomist0 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName0']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist0, new \DateTime('2022-03-03'), 7);

        self::assertCount(7, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName0', 'farmerName1', 'farmerName2', 'farmerName3', 'farmerName4', 'farmerName5', 'farmerName6'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        $startingHours = ['08:00', '09:08', '10:16', '11:24', '13:32', '14:40', '15:48'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testSomeFarmersWithLessThanTwoVisitsInLastYearAndSomeWithMore()
    {
        // area3, numberOfVisits = 6, farmers selected = farmer23...farmer27 + one in [farmer28, farmer29]
        $agronomist3 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName3']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist3, new \DateTime('2022-03-03'), 6);

        self::assertCount(6, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName23', 'farmerName24', 'farmerName25', 'farmerName26', 'farmerName27'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) {
            return strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName28') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName29') == 0;
        }));

        $startingHours = ['08:00', '09:20', '10:40', '12:00', '14:20', '15:40'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testSomeWorstFarmerWithNoVisitsinLastMonthAndSomeFarmersWithFourVisitsInLastYear()
    {
        // area1, numberOfVisits = 4, farmers selected = farmer8...farmer10 + one in [farmer11, farmer12]
        $agronomist1 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName1']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist1, new \DateTime('2022-03-03'), 4);

        self::assertCount(4, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName8', 'farmerName9', 'farmerName10'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) {
            return strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName11') == 0 ||
                strcmp($farmVisit->getFarm()->getFarmer()->getName(), 'farmerName12') == 0;
        }));

        $startingHours = ['08:00', '10:00', '12:00', '15:00'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }

    public function testAllBranchesTrue()
    {
        // area0, numberOfVisits = 7, farmers selected = farmer0...farmer6
        $agronomist0 = $this->em->getRepository(Agronomist::class)->findOneBy(['name' => 'agronomistName0']);
        $dailyPlan = $this->dpService->generateDailyPlan($agronomist0, new \DateTime('2022-01-10'), 7);

        self::assertCount(7, $dailyPlan->getFarmVisits());

        $farmers = ['farmerName0', 'farmerName1', 'farmerName2', 'farmerName3', 'farmerName4', 'farmerName5', 'farmerName6'];

        foreach ($farmers as $farmer) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($farmer) {
                return strcmp($farmVisit->getFarm()->getFarmer()->getName(), $farmer) == 0;
            }));
        }

        $startingHours = ['08:00', '09:08', '10:16', '11:24', '13:32', '14:40', '15:48'];
        foreach ($startingHours as $startingHour) {
            self::assertTrue($dailyPlan->getFarmVisits()->exists(function ($idx, $farmVisit) use ($startingHour) {
                return strcmp($farmVisit->getStartTime()->format('H:i'), $startingHour) == 0;
            }));
        }
    }
}
