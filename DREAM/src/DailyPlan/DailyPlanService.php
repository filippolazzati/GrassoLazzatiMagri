<?php

namespace App\DailyPlan;

use App\Entity\Agronomist;
use App\Entity\DailyPlan\DailyPlan;
use App\Entity\DailyPlan\FarmVisit;
use App\Entity\Farm;
use App\Repository\DailyPlan\FarmVisitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Knp\Component\Pager\Event\AfterEvent;
use Psr\Log\LoggerInterface;

class DailyPlanService
{
    const MAX_VISITS_IN_A_DAY = 8;
    const MIN_VISITS_IN_A_YEAR = 2;
    const MIN_VISITS_IN_A_MONTH_FOR_WORST_FARMERS = 1;
    const START_WORKDAY = '08:00';
    const BEGIN_LUNCH_BREAK = '12:30';
    const LAST_POSSIBLE_WORK_HOUR = '19:00';
    const WORKING_HOURS = 8;
    const MINUTES_PER_HOUR = 60;
    const MIN_VISIT_DURATION = 'PT15M';

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
            return $this->createDailyPlan($agronomist, $date, $agronomist->getArea()->getFarms());
        }

        // date of last visit scheduled in the area
        $dateOfLastVisit = $this->farmVisitRepository->getDateOfLastVisitToArea($agronomist->getArea());

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

        $farmsToVisit = $this->addFarmsToVisit($farmsToVisit, $farmsToAdd, $numberOfVisits);

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
            $farmsToVisit = $this->addFarmsToVisit($farmsToVisit, $farmsToAdd, $numberOfVisits);
        }
        return $this->createDailyPlan($agronomist, $date, $farmsToVisit);
    }

    public function moveVisit(Agronomist $agronomist, DailyPlan $dailyPlan, FarmVisit $farmVisit, \DateTime $newStartHour, LoggerInterface $logger) : void {
        if (!$dailyPlan->getAgronomist()->equals($agronomist) || $dailyPlan->isConfirmed() || !$dailyPlan->equals($farmVisit->getDailyPlan()) ||
                $this->compareTime($newStartHour, new \DateTime(self::START_WORKDAY)) ||
                $this->compareTime(new \DateTime(self::LAST_POSSIBLE_WORK_HOUR), $newStartHour)) {
            throw new \Exception('Operation "move visit" illegal');
        }

        $farmVisit->setStartTime($newStartHour);
    }

    public function addVisit(Agronomist $agronomist, DailyPlan $dailyPlan, Farm $farm, \DateTime $startHour)
    {
        if (!$dailyPlan->getAgronomist()->equals($agronomist) || $dailyPlan->isConfirmed() ||
            $this->compareTime($startHour, new \DateTime(self::START_WORKDAY)) ||
            $this->compareTime(new \DateTime(self::LAST_POSSIBLE_WORK_HOUR), $startHour) ||
            $agronomist->getArea()->equals($farm->getArea())) {
            throw new \Exception('Operation "add visit" illegal');
        }

        $farmVisit = new FarmVisit();
        $farmVisit->setStartTime($startHour);
        $farmVisit->setFarm($farm);
        $dailyPlan->addFarmVisit($farmVisit);
    }

    public function removeVisit(Agronomist $agronomist, DailyPlan $dailyPlan, FarmVisit $farmVisit) {
        if (!$dailyPlan->getAgronomist()->equals($agronomist) || $dailyPlan->isConfirmed() ||
            !$dailyPlan->equals($farmVisit->getDailyPlan()) || ($dailyPlan->isNew() && $this->isVisitNecessary($farmVisit))) {
            throw new \Exception('Operation "remove visit" illegal');
        }

        $dailyPlan->removeFarmVisit($farmVisit);
    }

    public function acceptDailyPlan(Agronomist $agronomist, DailyPlan $dailyPlan)
    {
        if ($dailyPlan->getAgronomist()->equals($agronomist) && $dailyPlan->isNew() &&
            $this->noOverlappingVisits($dailyPlan->getFarmVisits())) {
            $dailyPlan->setState(DailyPlan::ACCEPTED);
        } else {
            throw new Exception("Daily plan not acceptable");
        }
    }

    public function confirmDailyPlan(Agronomist $agronomist, DailyPlan $dailyPlan)
    {
        if ($dailyPlan->getAgronomist()->equals($agronomist) && $dailyPlan->isAccepted() &&
            $this->noOverlappingVisits($dailyPlan->getFarmVisits())) {
            $dailyPlan->setState(DailyPlan::CONFIRMED);
        } else {
            throw new Exception("Daily plan not confirmable");
        }
    }

    public function insertVisitsFeedbacks(Agronomist $agronomist, DailyPlan $dailyPlan, ArrayCollection $feedbacks)
    {
        if(!$dailyPlan->getAgronomist()->equals($agronomist)) {
            throw new Exception('Invalid access');
        }

        foreach ($feedbacks as $visitId => $feedback) {
            foreach ($dailyPlan->getFarmVisits() as $farmVisit) {
                if ($farmVisit->getId() == $visitId) {
                    $farmVisit->setFeedback($feedback);
                }
            }
        }
    }

    private function createDailyPlan(Agronomist $agronomist, \DateTime $date, Collection $farms): DailyPlan
    {
        $dailyPlan = new DailyPlan();
        $dailyPlan->setAgronomist($agronomist);
        $dailyPlan->setDate($date);
        $dailyPlan->setState(DailyPlan::NEW);
        $startingHour = new \DateTime(self::START_WORKDAY);
        // time in minutes between a visit and another one
        // visits are homogeneously distributed among the day, starting from 8:00 AM (travel time not considered)
        $offsetInMinutes = (self::WORKING_HOURS * self::MINUTES_PER_HOUR) / ($farms->count());
        for ($i = 0; $i < $farms->count(); $i++) {
            $farmVisit = new FarmVisit();
            $farmVisit->setFarm($farms->get($i));
            $startTime = $startingHour->add(new \DateInterval('PT' . ($offsetInMinutes * $i) . 'M'));
            if ($startTime >= new \DateTime(self::BEGIN_LUNCH_BREAK)){ // to take into account lunch break
                $startTime = $startTime->add(new \DateInterval('PT1H'));
            }
            $farmVisit->setStartTime($startTime);
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
    private function addFarmsToVisit(ArrayCollection $farmsToVisit, ArrayCollection $farmsToAdd, int $size) : ArrayCollection
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

    private function isVisitNecessary(FarmVisit $farmVisit): bool
    {
        // date of last visit scheduled in the area
        $dateOfLastVisitInArea = $this->farmVisitRepository->getDateOfLastVisitToArea($farmVisit->getFarm()->getArea());

        if ($farmVisit->getFarm()->getFarmer()->getWorstPerforming()) {
            return $this->farmVisitRepository->getNumberOfVisitsToFarmInPeriod($farmVisit->getFarm(),
                $dateOfLastVisitInArea->sub(new \DateInterval('P1M')), $dateOfLastVisitInArea)
                < self::MIN_VISITS_IN_A_MONTH_FOR_WORST_FARMERS;
        } else {
            return $this->farmVisitRepository->getNumberOfVisitsToFarmInPeriod($farmVisit->getFarm(),
                $dateOfLastVisitInArea->sub(new \DateInterval('P1Y')), $dateOfLastVisitInArea)
                < self::MIN_VISITS_IN_A_YEAR;
        }
    }

    private function noOverlappingVisits(Collection $farmVisits) : bool
    {
        $startingHours = $farmVisits->map(function ($value) {
            return new \DateTime($value->getStartTime()->format('H:i'));
        });
        $iterator = $startingHours->getIterator();
        $iterator->asort();
        $prec = $iterator->current();
        $iterator->next();
        while($iterator->valid()) {
            $current = $iterator->current();
            if ($this->compareTime($current->add(new \DateInterval(self::MIN_VISIT_DURATION)), $prec)) {
                return false;
            }
            $prec = $current;
            $iterator->next();
        }
        return true;
    }

    private function compareTime(\DateTime $first, \DateTime $second) : bool
    {
        // to compare only time, create two new objects DateTime with the same date and compare them
        $firstOnlyTime = new \DateTime('1970-01-01 ' . $first->format('H:i'));
        $secondOnlyTime = new \DateTime('1970-01-01 ' . $second->format('H:i'));
        return $firstOnlyTime < $secondOnlyTime;
    }
}