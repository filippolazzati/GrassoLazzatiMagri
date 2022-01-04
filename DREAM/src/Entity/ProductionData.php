<?php

namespace App\Entity;

use App\Repository\ProductionDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionDataRepository::class)]
class ProductionData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date')]
    private $seedingDate;

    #[ORM\Column(type: 'integer')]
    private $seededArea;

    #[ORM\Column(type: 'string', length: 50)]
    private $product;

    #[ORM\Column(type: 'string', length: 50)]
    private $fertilizer;

    #[ORM\Column(type: 'float')]
    private $harvestVolume;

    #[ORM\Column(type: 'boolean')]
    private $watering;

    #[ORM\Column(type: 'date')]
    private $reportDate;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Farmer::class, inversedBy: 'productionData')]
    #[ORM\JoinColumn(nullable: false)]
    private $farmer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeedingDate(): ?\DateTimeInterface
    {
        return $this->seedingDate;
    }

    public function setSeedingDate(\DateTimeInterface $seedingDate): self
    {
        $this->seedingDate = $seedingDate;

        return $this;
    }

    public function getSeededArea(): ?int
    {
        return $this->seededArea;
    }

    public function setSeededArea(int $seededArea): self
    {
        $this->seededArea = $seededArea;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getFertilizer(): ?string
    {
        return $this->fertilizer;
    }

    public function setFertilizer(string $fertilizer): self
    {
        $this->fertilizer = $fertilizer;

        return $this;
    }

    public function getHarvestVolume(): ?float
    {
        return $this->harvestVolume;
    }

    public function setHarvestVolume(float $harvestVolume): self
    {
        $this->harvestVolume = $harvestVolume;

        return $this;
    }

    public function getWatering(): ?bool
    {
        return $this->watering;
    }

    public function setWatering(bool $watering): self
    {
        $this->watering = $watering;

        return $this;
    }

    public function getReportDate(): ?\DateTimeInterface
    {
        return $this->reportDate;
    }

    public function setReportDate(\DateTimeInterface $reportDate): self
    {
        $this->reportDate = $reportDate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFarmer(): ?Farmer
    {
        return $this->farmer;
    }

    public function setFarmer(?Farmer $farmer): self
    {
        $this->farmer = $farmer;

        return $this;
    }
}
