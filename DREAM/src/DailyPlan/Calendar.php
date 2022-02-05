<?php

namespace App\DailyPlan;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Helper class that manages working days and holidays in Telangana.
 * In a real project, to be substituted with call to external service of administration, that provides working
 * days of agronomists considering also time offs and days of leave.
 * In this project, for the sake of simplicity, such administration issues are not considered.
 */
class Calendar
{
    private ArrayCollection $nationalHolidays;

    public function __construct()
    {
        $this->nationalHolidays = (new ArrayCollection(json_decode(file_get_contents(__DIR__ . '/holidays.json'))))
            ->map(function ($value) {
                return new DateTime($value);
            });
    }

    /**
     * Returns seven working days, from the provided one included.
     * A day is a working day if it is not a holiday; a day is a holiday if it is a Saturday, a Sunday or
     * a Telangana national holiday.
     * @param DateTime $date the date from which (included) the working days are computed
     * @return ArrayCollection an ArrayCollection of DateTime objects, containing seven working days, from the provided one included
     */
    public function getSevenWorkingDaysFrom(DateTime $date): ArrayCollection
    {
        $result = new ArrayCollection();
        do {
            if (!$this->isHoliday($date)) {
                $result->add(new DateTime($date->format('Y-m-d')));
            }
            $date = $date->add(new DateInterval('P1D'));
        } while ($result->count() < 7);
        return $result;
    }

    private function isHoliday(DateTime $date): bool
    {
        $weekDay = $date->format('w');
        return $weekDay == 6 || $weekDay == 0 || $this->nationalHolidays->exists(function ($key, $value) use ($date) {
                return strcmp($date->format('d-m'), $value->format('d-m')) == 0;
            });
    }
}