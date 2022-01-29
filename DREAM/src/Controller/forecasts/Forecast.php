<?php


namespace App\Controller\forecasts;

/**
 * Class Forecast
 * @package App\Controller\forecasts
 *
 * This class represents an object Forecast to show in the Weather Forecasts section. It is needed
 * to convert Datetime objects to strings.
 */
class Forecast
{
    public $date;
    public $weather;
    public $t_max;
    public $t_min;
    public $t_avg;
    public $rain_mm;
    public $windSpeed;
    public $windDirection;
    public $humidity;
    public $pressure;

    /**
     * Forecast constructor.
     * @param $date
     * @param $weather
     * @param $t_max
     * @param $t_min
     * @param $t_avg
     * @param $rain_mm
     * @param $windSpeed
     * @param $windDirection
     * @param $humidity
     * @param $pressure
     */
    public function __construct($date, $weather, $t_max, $t_min, $t_avg, $rain_mm, $windSpeed, $windDirection, $humidity, $pressure)
    {
        $this->date = $date;
        $this->weather = $weather;
        $this->t_max = $t_max;
        $this->t_min = $t_min;
        $this->t_avg = $t_avg;
        $this->rain_mm = $rain_mm;
        $this->windSpeed = $windSpeed;
        $this->windDirection = $windDirection;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
    }
}