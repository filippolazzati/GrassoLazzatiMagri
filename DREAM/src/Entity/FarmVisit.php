<?php

namespace App\Entity;

use App\Repository\FarmVisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FarmVisitRepository::class)]
class FarmVisit
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: DailyPlan::class, inversedBy: 'farmVisits')]
    #[ORM\JoinColumn(nullable: false)]
    private $dailyPlan;

    #[ORM\Id]
    #[ORM\Column(type: 'time')]
    private $startTime;

    #[ORM\ManyToOne(targetEntity: Farm::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $farm;

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getFarm(): ?Farm
    {
        return $this->farm;
    }

    public function setFarm(?Farm $farm): self
    {
        $this->farm = $farm;

        return $this;
    }

    public function getDailyPlan(): ?DailyPlan
    {
        return $this->dailyPlan;
    }

    public function setDailyPlan(?DailyPlan $dailyPlan): self
    {
        $this->dailyPlan = $dailyPlan;

        return $this;
    }
}
