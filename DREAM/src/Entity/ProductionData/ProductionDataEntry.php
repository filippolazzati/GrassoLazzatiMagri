<?php

namespace App\Entity\ProductionData;

use App\Repository\ProductionData\ProductionDataEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionDataEntryRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'planting_seeding' => PlantingSeedingEntry::class,
    'fertilizing' => FertilizingEntry::class,
    'watering' => WateringEntry::class,
    'harvesting' => HarvestingEntry::class,
])]
class ProductionDataEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    private $area;

    #[ORM\ManyToOne(targetEntity: ProductionData::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private $parent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?ProductionData
    {
        return $this->parent;
    }

    public function setParent(?ProductionData $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
