<?php

namespace App\Entity\DailyPlan;

use App\Entity\Farm;
use App\Repository\DailyPlan\FarmVisitRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FarmVisitRepository::class)]
class FarmVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: DailyPlan::class, inversedBy: 'farmVisits')]
    #[ORM\JoinColumn(nullable: false)]
    private $dailyPlan;

    #[ORM\Column(type: 'time')]
    private $startTime;

    #[ORM\ManyToOne(targetEntity: Farm::class, inversedBy: 'farmVisits')]
    #[ORM\JoinColumn(nullable: false)]
    private $farm;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private $feedback;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(DateTimeInterface $startTime): self
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

    public function equals(FarmVisit $other): bool
    {
        return $this->id == $other->getId();
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;

        return $this;
    }
}
