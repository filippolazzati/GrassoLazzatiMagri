<?php

namespace App\Tests\DailyPlan;

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

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
}
