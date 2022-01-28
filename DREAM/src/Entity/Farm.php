<?php

namespace App\Entity;

use App\Entity\ProductionData\ProductionData;
use App\Repository\FarmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FarmRepository::class)]
class Farm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $city;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $street;

    #[ORM\OneToOne(inversedBy: 'farm', targetEntity: Farmer::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $farmer;

    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'farms')]
    #[ORM\JoinColumn(nullable: false)]
    private $area;

    #[ORM\OneToMany(mappedBy: 'farm', targetEntity: ProductionData::class, orphanRemoval: true)]
    private $productionData;

    public function __construct()
    {
        $this->productionData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getFarmer(): ?Farmer
    {
        return $this->farmer;
    }

    public function setFarmer(Farmer $farmer): self
    {
        $this->farmer = $farmer;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection|ProductionData[]
     */
    public function getProductionData(): Collection
    {
        return $this->productionData;
    }

    public function addProductionData(ProductionData $productionData): self
    {
        if (!$this->productionData->contains($productionData)) {
            $this->productionData[] = $productionData;
            $productionData->setFarm($this);
        }

        return $this;
    }

    public function removeProductionData(ProductionData $productionData): self
    {
        if ($this->productionData->removeElement($productionData)) {
            // set the owning side to null (unless already changed)
            if ($productionData->getFarm() === $this) {
                $productionData->setFarm(null);
            }
        }

        return $this;
    }
}
