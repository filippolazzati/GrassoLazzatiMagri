<?php

namespace App\Entity;

use App\Repository\WeatherReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeatherReportRepository::class)]
class WeatherReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'string', length: 50)]
    private $city;

    #[ORM\Column(type: 'string', length: 50)]
    private $weather;

    #[ORM\Column(type: 'integer')]
    private $t_max;

    #[ORM\Column(type: 'integer')]
    private $t_min;

    #[ORM\Column(type: 'integer')]
    private $t_avg;

    #[ORM\Column(type: 'integer')]
    private $rain_mm;

    #[ORM\Column(type: 'float')]
    private $windSpeed;

    #[ORM\Column(type: 'string', length: 10)]
    private $windDirection;

    #[ORM\Column(type: 'integer')]
    private $humidity;

    #[ORM\Column(type: 'integer')]
    private $pressure;

    /**
     * WeatherReport constructor.
     * @param $date
     * @param $city
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
    public function __construct($date, $city, $weather, $t_max, $t_min, $t_avg, $rain_mm, $windSpeed, $windDirection, $humidity, $pressure)
    {
        $this->date = $date;
        $this->city = $city;
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
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
        return $this->t_max;
    }

    public function setTMax(int $t_max): self
    {
        $this->t_max = $t_max;

        return $this;
    }

    public function getTMin(): ?int
    {
        return $this->t_min;
    }

    public function setTMin(int $t_min): self
    {
        $this->t_min = $t_min;

        return $this;
    }

    public function getTAvg(): ?int
    {
        return $this->t_avg;
    }

    public function setTAvg(int $t_avg): self
    {
        $this->t_avg = $t_avg;

        return $this;
    }

    public function getRainMm(): ?int
    {
        return $this->rain_mm;
    }

    public function setRainMm(int $rain_mm): self
    {
        $this->rain_mm = $rain_mm;

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
