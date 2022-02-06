<?php

namespace App\Tests\DailyPlan;

use App\Entity\Area;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use App\Entity\Farmer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FarmVisitRepositoryTest extends KernelTestCase
{
    private $em;
    private $farmVisitRepository;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // initialize test database
        SampleDatabaseLoader::loadSampleDatabase($this->em);

        $this->farmVisitRepository = $this->em->getRepository(FarmVisit::class);
    }

    public function testGetFarmsWithNumberOfVisitLessThan() : void
    {
        $area0 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area0']);

        // test 1: with numVisits = 2, maxResults = 3 > tot results
        // result should contain: farmer5, two of [farmer0...farmer4]
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area0, new DateTime('2021-09-09'),
            new DateTime('2022-09-09'), 2, 3, false)) ;

        $this->assertCount(3, $result, 'testGetFarmsWithNumberOfVisitLessThan.1 failed: '
        . 'result size expected = 3, actual = ' . $result->count());

        $this->assertTrue($result->exists(function ($index, $farm) {
            /** @var $farm Farm */
            return strcmp($farm->getFarmer()->getName(), 'farmerName5') == 0;
        }), 'testGetFarmsWithNumberOfVisitLessThan.1 failed: farmer 5 not in result' );

        $this->assertTrue($result->exists(function ($index, $farm) {
            /** @var $farm Farm */
            return strcmp($farm->getFarmer()->getName(), 'farmerName0') == 0 ||
            strcmp($farm->getFarmer()->getName(), 'farmerName1') == 0 ||
            strcmp($farm->getFarmer()->getName(), 'farmerName2') == 0 ||
            strcmp($farm->getFarmer()->getName(), 'farmerName3') == 0 ||
            strcmp($farm->getFarmer()->getName(), 'farmerName4') == 0;
        }), 'testGetFarmsWithNumberOfVisitLessThan.1 failed: no farmers in 0..4 in result');

        $this->assertFalse($result->exists(function ($index, $farm) {
            /** @var $farm Farm */
            return strcmp($farm->getFarmer()->getName(), 'farmerName7') == 0;
        }), 'testGetFarmsWithNumberOfVisitLessThan.1 failed: farmer 7 in result');

        // test 2:  with numVisits = 2, maxResults = 10 < tot results, considering only dates between 2021-12-20 and 2022-09-09
        // all farmers in the area should be selected (farmer0 ... farmer7)
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area0, new DateTime('2021-12-25'),
            new DateTime('2022-09-09'), 2, 10, false)) ;

        $this->assertCount(8, $result, 'testGetFarmsWithNumberOfVisitLessThan.2 failed: '
            . 'result size expected = 8, actual = ' . $result->count());

        for ($i = 0; $i < 8; $i++) {
            $this->assertTrue($result->exists(function ($index, $farm) use ($i) {
                /** @var $farm Farm */
                return strcmp($farm->getFarmer()->getName(), 'farmerName' . $i) == 0;
            }), 'testGetFarmsWithNumberOfVisitLessThan.2 failed: farmer ' . $i . ' not in result' );
        }

        // test 3:  with numVisits = 3, maxResults = tot results
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area0, new DateTime('2021-12-01'),
            new DateTime('2022-09-09'), 3, 7, false)) ;

        $this->assertCount(7, $result, 'testGetFarmsWithNumberOfVisitLessThan.3 failed: '
            . 'result size expected = 7, actual = ' . $result->count());

        for ($i = 0; $i < 7; $i++) {
            $this->assertTrue($result->exists(function ($index, $farm) use ($i) {
                /** @var $farm Farm */
                return strcmp($farm->getFarmer()->getName(), 'farmerName' . $i) == 0;
            }), 'testGetFarmsWithNumberOfVisitLessThan.3 failed: farmer ' . $i . ' not in result' );
        }

        // test 4: with only worst performing = true
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area0, new DateTime('2021-12-01'),
            new DateTime('2022-09-09'), 3, 7, true)) ;

        $this->assertCount(2, $result, 'testGetFarmsWithNumberOfVisitLessThan.4 failed: '
            . 'result size expected = 2, actual = ' . $result->count());

        for ($i = 5; $i < 7; $i++) {
            $this->assertTrue($result->exists(function ($index, $farm) use ($i) {
                /** @var $farm Farm */
                return strcmp($farm->getFarmer()->getName(), 'farmerName' . $i) == 0;
            }), 'testGetFarmsWithNumberOfVisitLessThan.4 failed: farmer ' . $i . ' not in result' );
        }

        // test 5: with empty result
        $area2 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area2']);
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area2, new DateTime('2021-12-01'),
            new DateTime('2022-09-09'), 2, 7, true)) ;

        $this->assertCount(0, $result, 'testGetFarmsWithNumberOfVisitLessThan.5 failed: '
            . 'result size expected = 0, actual = ' . $result->count());
    }

    public function testGetDateOfLastVisitToArea(): void
    {
        // 1:  with visits in area
        $area0 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area0']);
        $dateOfLastVisit = $this->farmVisitRepository->getDateOfLastVisitToArea($area0);
        $this->assertEquals('2022-01-01', $dateOfLastVisit->format('Y-m-d'),
        'testGetDateOfLastVisitToArea.1 failed');

        // 2: with no visits in area
        $area4 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area4']);
        $dateOfLastVisit = $this->farmVisitRepository->getDateOfLastVisitToArea($area4);
        $this->assertNull($dateOfLastVisit,'testGetDateOfLastVisitToArea.2 failed');
    }

    public function testGetMinNumberOfVisitsInPeriod() : void
    {
        // 1:  with farmers never visited in area
        $area0 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area0']);
        $numberVisits = $this->farmVisitRepository->getMinNumberOfVisitsInPeriod($area0,
            new DateTime('2021-09-02'), new DateTime('2022-09-09'));
        $this->assertEquals(0, $numberVisits, 'testGetMinNumberOFVisitsInPeriod.1 failed');

        // 2: with all farmers visited in area
        $area2 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area2']);
        $numberVisits = $this->farmVisitRepository->getMinNumberOfVisitsInPeriod($area2,
            new DateTime('2021-09-02'), new DateTime('2022-09-09'));
        $this->assertEquals(3, $numberVisits, 'testGetMinNumberOFVisitsInPeriod.2 failed');

        // 3: with no visits
        $area4 = $this->em->getRepository(Area::class)->findOneBy(['name' => 'Area4']);
        $numberVisits = $this->farmVisitRepository->getMinNumberOfVisitsInPeriod($area4,
            new DateTime('2021-09-02'), new DateTime('2022-09-09'));
        $this->assertEquals(0, $numberVisits, 'testGetMinNumberOFVisitsInPeriod.3 failed');
    }

    public function testGetNumberOfVisitsToFarmsInPeriod() : void
    {
        // 1: with farm visited in period
        $farmer0 = $this->em->getRepository(Farmer::class)->findOneBy(['name' => 'farmerName0']);
        $this->assertEquals(1, $this->farmVisitRepository->getNumberOfVisitsToFarmInPeriod($farmer0->getFarm(),
            new DateTime('2021-02-02'), new DateTime('2022-02-02')), 'testGetNumberOfVisitsToFarmsInPeriod.1 failed');

        // 2: with farm never visited in period
        $this->assertEquals(0, $this->farmVisitRepository->getNumberOfVisitsToFarmInPeriod($farmer0->getFarm(),
            new DateTime('2022-02-02'), new DateTime('2023-02-02')), 'testGetNumberOfVisitsToFarmsInPeriod.2 failed');


        // 3: with no visits ever
        $farmer5 = $this->em->getRepository(Farmer::class)->findOneBy(['name' => 'farmerName5']);
        $this->assertEquals(0, $this->farmVisitRepository->getNumberOfVisitsToFarmInPeriod($farmer0->getFarm(),
            new DateTime('2022-02-02'), new DateTime('2023-02-02')), 'testGetNumberOfVisitsToFarmsInPeriod.1 failed');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->em->close();
        $this->em = null;
    }
}
