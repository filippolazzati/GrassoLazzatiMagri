<?php

namespace App\Tests\DailyPlan;

use _PHPStan_daf7d5577\Nette\Utils\DateTime;
use App\Entity\Area;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
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

        // test 1: with numVisits = 2, maxResults > tot results
        // result should contain: farmer5, two of [farmer0...farmer4]
        $result = new ArrayCollection($this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($area0, new DateTime('2021-09-09'),
            new DateTime('2022-09-09'), 2, 3, false)) ;

        $this->assertCount(6, $result, 'testGetFarmsWithNumberOfVisitLessThan.1 failed: '
        . 'result size expected = 3, actual = ' . $result->count());

        $this->assertTrue($result->exists(function ($index, $farm) {
            /** @var $farm Farm */
            return strcmp($farm->getFarmer()->getName(), 'farmerName5') == 0;
        }), 'testGetFarmsWithNumberOfVisitLessThan.1 failed: farmer 5 not in result \n
        result = ' . $result->get(0)->getFarmer()->getName() . $result->get(1)->getFarmer()->getName() . $result->get(2)->getFarmer()->getName());

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

        // test 2:  with numVisits = 5, maxResults < tot results


        // test 3:  with numVisits = 3, maxResults = tot results

        // test 4: with only worst performing = true

        // test 5: with empty result (numVisits = 0)
    }

    public function testGetDateOfLastVisitToArea(): void
    {


    }

    public function testGetMinNumberOfVisitsInPeriod() : void
    {

    }

    public function testGetNumberOfVisitsToFarmsInPeriod() : void
    {

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->em->close();
        $this->em = null;
    }
}
