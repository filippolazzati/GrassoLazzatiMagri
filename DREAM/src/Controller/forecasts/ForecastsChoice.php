<?php


namespace App\Controller\forecasts;


class ForecastsChoice
{
    /**
     * the city of the weather forecast to visualize
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