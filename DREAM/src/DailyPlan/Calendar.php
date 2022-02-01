<?php

namespace App\DailyPlan;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Helper class that manages working days and holidays in Telangana.
 * In a real project, to be substituted with external services: there are several available at a cost
 * for computing working days, and an interface to other administration systems is needed to retrieve,
 * for example, break periods, days of leaves and sick leaves of agronomists.
 * In this project, for the sake of simplicity, such administration issues are not considered.
 */
class Calendar
{
    private $nationalHolidays;

    public function __construct()
    {
        // TODO: move holidays.json to a resources directory
        $this->nationalHolidays = (new ArrayCollection(json_decode(file_get_contents(__DIR__ . '/holidays.json'))))
            ->map(function ($value) {
                return new \DateTime($value);
            });
    }

    public function getSevenWorkingDaysFrom(\DateTime $date) : ArrayCollection
    {
        $result = new ArrayCollection();
        do {
            if (!$this->isHoliday($date)) {
                $result->add(new \DateTime($date->format('Y-m-d')));
            }
            $date = $date->add(new \DateInterval('P1D'));
        } while ($result->count() < 7);
        return $result;
    }

    private function isHoliday(\DateTime $date) : bool
    {
        /**
         * A day is a holiday if it is a Saturday, a Sunday or a national holiday.
         */
        $weekDay = $date->format('w');
        return $weekDay == 6 || $weekDay == 0 || $this->nationalHolidays->exists(function ($key, $value) use ($date) {
                return strcmp($date->format('d-m'), $value->format('d-m')) == 0 ;
            });
    }
}