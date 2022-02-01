<?php


namespace App\Controller\forecasts;

/**
 * Class ForecastsChoice
 * @package App\Controller\forecasts
 *
 * This class is used to retrieve the city data from the form.
 */
class ForecastsChoice
{
    /**
     * The city of the weather forecast to visualize.
     */
    private $city;

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }
}