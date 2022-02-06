<?php

namespace App\Entity;

use App\Repository\WeatherForecastRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeatherForecastRepository::class)]
class WeatherForecast
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'string', length: 50)]
    private $city;

    // sunny/foggy/cloudy/...
    #[ORM\Column(type: 'string', length: 50)]
    private $weather;

    #[ORM\Column(type: 'integer')]
    private $tMax;

    #[ORM\Column(type: 'integer')]
    private $tMin;

    #[ORM\Column(type: 'integer')]
    private $tAvg;

    #[ORM\Column(type: 'integer')]
    private $rainMm;

    // km/h
    #[ORM\Column(type: 'float')]
    private $windSpeed;

    // n/s/e/o/ne/no/se/so
    #[ORM\Column(type: 'string', length: 3)]
    private $windDirection;

    // 40 stands for 40%
    #[ORM\Column(type: 'integer')]
    private $humidity;

    // in millibar (mbar)
    #[ORM\Column(type: 'integer')]
    private $pressure;


    public function __construct($date, $city, $weather, $t_max, $t_min, $t_avg, $rain_mm, $windSpeed, $windDirection, $humidity, $pressure)
    {
        $this->date = $date;
        $this->city = $city;
        $this->weather = $weather;
        $this->tMax = $t_max;
        $this->tMin = $t_min;
        $this->tAvg = $t_avg;
        $this->rainMm = $rain_mm;
        $this->windSpeed = $windSpeed;
        $this->windDirection = $windDirection;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getWeather(): ?string
    {
        return $this->weather;
    }

    public function setWeather(string $weather): self
    {
        $this->weather = $weather;

        return $this;
    }

    public function getTMax(): ?int
    {
        return $this->tMax;
    }

    public function setTMax(int $tMax): self
    {
        $this->tMax = $tMax;

        return $this;
    }

    public function getTMin(): ?int
    {
        return $this->tMin;
    }

    public function setTMin(int $tMin): self
    {
        $this->tMin = $tMin;

        return $this;
    }

    public function getTAvg(): ?int
    {
        return $this->tAvg;
    }

    public function setTAvg(int $tAvg): self
    {
        $this->tAvg = $tAvg;

        return $this;
    }

    public function getRainMm(): ?int
    {
        return $this->rainMm;
    }

    public function setRainMm(int $rainMm): self
    {
        $this->rainMm = $rainMm;

        return $this;
    }

    public function getWindSpeed(): ?float
    {
        return $this->windSpeed;
    }

    public function setWindSpeed(float $windSpeed): self
    {
        $this->windSpeed = $windSpeed;

        return $this;
    }

    public function getWindDirection(): ?string
    {
        return $this->windDirection;
    }

    public function setWindDirection(string $windDirection): self
    {
        $this->windDirection = $windDirection;

        return $this;
    }

    public function getHumidity(): ?int
    {
        return $this->humidity;
    }

    public function setHumidity(int $humidity): self
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function getPressure(): ?int
    {
        return $this->pressure;
    }

    public function setPressure(int $pressure): self
    {
        $this->pressure = $pressure;

        return $this;
    }
}
