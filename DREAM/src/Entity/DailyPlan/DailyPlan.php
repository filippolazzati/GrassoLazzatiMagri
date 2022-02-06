<?php

namespace App\Entity\DailyPlan;

use App\Entity\Agronomist;
use App\Repository\DailyPlan\DailyPlanRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyPlanRepository::class)]
class DailyPlan
{
    public const NEW = 'NEW';
    public const ACCEPTED = 'ACCEPTED';
    public const CONFIRMED = 'CONFIRMED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Agronomist::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $agronomist;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'string', length: 10)]
    private $state;

    #[ORM\OneToMany(mappedBy: 'dailyPlan',
        targetEntity: FarmVisit::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true)]
    private $farmVisits;

    public function __construct()
    {
        $this->farmVisits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgronomist(): ?Agronomist
    {
        return $this->agronomist;
    }

    public function setAgronomist(?Agronomist $agronomist): self
    {
        $this->agronomist = $agronomist;

        return $this;
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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection|FarmVisit[]
     */
    public function getFarmVisits(): Collection
    {
        return $this->farmVisits;
    }

    public function addFarmVisit(FarmVisit $farmVisit): self
    {
        if (!$this->farmVisits->contains($farmVisit)) {
            $this->farmVisits[] = $farmVisit;
            $farmVisit->setDailyPlan($this);
        }

        return $this;
    }

    public function removeFarmVisit(FarmVisit $farmVisit): self
    {
        if ($this->farmVisits->removeElement($farmVisit)) {
            // set the owning side to null (unless already changed)
            if ($farmVisit->getDailyPlan() === $this) {
                $farmVisit->setDailyPlan(null);
            }
        }

        return $this;
    }

    public function isNew(): bool
    {
        return $this->state === self::NEW;
    }

    public function isAccepted(): bool
    {
        return $this->state === self::ACCEPTED;
    }

    public function isConfirmed(): bool
    {
        return $this->state === self::CONFIRMED;
    }

    public function equals(DailyPlan $other): bool
    {
        return $this->id === $other->getId();
    }
}
