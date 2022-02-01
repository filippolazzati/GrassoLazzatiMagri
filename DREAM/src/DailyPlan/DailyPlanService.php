<?php

namespace App\DailyPlan;

use App\Entity\Agronomist;
use App\Entity\DailyPlan;
use App\Entity\FarmVisit;
use App\Repository\FarmVisitRepository;
use Doctrine\Common\Collections\ArrayCollection;

class DailyPlanService
{
    const MAX_VISITS_IN_A_DAY = 8;
    const MIN_VISITS_IN_A_YEAR = 2;
    const MIN_VISITS_IN_A_MONTH_FOR_WORST_FARMERS = 1;
    const START_WORKDAY = '08:00';
    const END_WORKDAY = '17:00';
    const WORKING_HOURS = 8;
    const MINUTES_PER_HOUR = 60;

    private FarmVisitRepository $farmVisitRepository;

    public function __construct(FarmVisitRepository $farmVisitRepository)
    {
        $this->farmVisitRepository = $farmVisitRepository;
    }

    public function generateDailyPlan(Agronomist $agronomist, \DateTime $date, int $numberOfVisits) : DailyPlan
    {
        // if there are less than $numberOfVisits farms in the area, simply all farms should be visited
        // (very particular case)
        if ($numberOfVisits <= $agronomist->getArea()->getFarms()->count()) {
            return $this->createDailyPlan($agronomist, $date, new ArrayCollection($agronomist->getArea()->getFarms()));
        }

        // date of last visit scheduled in the area
        $dateOfLastVisit = $this->farmVisitRepository->getDateOfLastVisit($agronomist->getArea());

        // farms with less than MIN_VISITS_IN_A_YEAR in the year before $dateOfLastVisit need to be visited
        $farmsToVisit = $this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($agronomist->getArea(),
            $dateOfLastVisit->sub(new \DateInterval('P1Y')), $dateOfLastVisit,
            self::MIN_VISITS_IN_A_YEAR, $numberOfVisits, false);

        // if the farms to visit are $numberOfVisits, create daily plan
        if ($farmsToVisit->count() == $numberOfVisits) {
            return $this->createDailyPlan($agronomist, $date, $farmsToVisit);
        }

        // otherwise, take into consideration farms of worst-performing farmers with less than
        // MIN_VISITS_IN_A_MONTH_FOR_WORST_FARMERS visits in the month before $dateOfLastVisit

        // we select again numberOfVisits farms instead of (numberOfVisits - farmsToVisit->count) because
        // some farms in farmsToAdd can already be in farmsToVisit
        $farmsToAdd = $this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($agronomist->getArea(),
            $dateOfLastVisit->sub(new \DateInterval('P1M')), $dateOfLastVisit,
            self::MIN_VISITS_IN_A_MONTH_FOR_WORST_FARMERS, $numberOfVisits, true);

        $farmsToVisit = $this->mergeCollections($farmsToVisit, $farmsToAdd, $numberOfVisits);

        // if the farms to visit are $numberOfVisits, create daily plan
        if ($farmsToVisit->count() == $numberOfVisits) {
            return $this->createDailyPlan($agronomist, $date, $farmsToVisit);
        }

        // otherwise, take into consideration farms with fewer visits than any other in the area in the last year
        $i = $this->farmVisitRepository->getMinNumberOfVisitsInPeriod($agronomist->getArea(),
            $dateOfLastVisit->sub(new \DateInterval('P1Y')), $dateOfLastVisit);

        while($farmsToVisit->count() <= $numberOfVisits) {
            $i++; // now and not at the end of the while, because FarmVisitRepository::getFarmsWithNumberOfVisitsLessThan
                  // uses < and not <=
            $farmsToAdd = $this->farmVisitRepository->getFarmsWithNumberOfVisitsLessThan($agronomist->getArea(),
                $dateOfLastVisit->sub(new \DateInterval('P1Y')), $dateOfLastVisit, $i, $numberOfVisits, false);
            $farmsToVisit = $this->mergeCollections($farmsToVisit, $farmsToAdd, $numberOfVisits);
        }
        return $this->createDailyPlan($agronomist, $date, $farmsToVisit);
    }

    private function createDailyPlan(Agronomist $agronomist, \DateTime $date, ArrayCollection $farms): DailyPlan
    {
        $dailyPlan = new DailyPlan();
        $dailyPlan->setAgronomist($agronomist);
        $dailyPlan->setDate($date);
        $dailyPlan->setState('NEW');
        $startingHour = new \DateTime(self::START_WORKDAY);
        // time in minutes between a visit and another one
        // visits are homogeneously distributed among the day, starting from 8:00 AM (travel time not considered)
        $offsetInMinutes = (self::WORKING_HOURS * self::MINUTES_PER_HOUR) / ($farms->count());
        for ($i = 0; $i < $farms->count(); $i++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setFarm($farms->get($i));
            $farmVisit->setStartTime($startingHour->add(new \DateInterval('PT' . ($offsetInMinutes * $i) . 'M')));
            $dailyPlan->addFarmVisit($farmVisit);
        }
        return $dailyPlan;
    }

    /**
     * Adds the farms in $farmsToAdd to $farmsToVisit until the size of $farmsToVisit is less than or equal $size
     * @param ArrayCollection $farmsToVisit
     * @param ArrayCollection $farmsToAdd
     * @param int $size
     * @return ArrayCollection
     */
    private function mergeCollections(ArrayCollection $farmsToVisit, ArrayCollection $farmsToAdd, int $size) : ArrayCollection
    {
        $i = 0;
        while ($i < $farmsToAdd->count() && $farmsToVisit->count() <= $size) {
            if (!$farmsToVisit->contains($farmsToAdd->get($i))) {
                $farmsToVisit->add($farmsToAdd->get($i));
            }
            $i++;
        }

        return $farmsToVisit;
    }
}