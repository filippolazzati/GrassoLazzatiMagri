<?php

namespace App\Tests\DailyPlan;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DailyPlanGenerationTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
}
