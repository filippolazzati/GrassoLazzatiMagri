<?php

namespace App\Entity\ProductionData;

use App\Repository\ProductionData\ProductionDataEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductionDataEntryRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'planting_seeding' => PlantingSeedingEntry::class,
    'fertilizing' => FertilizingEntry::class,
    'watering' => WateringEntry::class,
    'harvesting' => HarvestingEntry::class,
])]
#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        'planting_seeding' => PlantingSeedingEntry::class,
        'fertilizing' => FertilizingEntry::class,
        'watering' => WateringEntry::class,
        'harvesting' => HarvestingEntry::class,
    ]
)]
abstract class ProductionDataEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['form'])]
    private ?int $id;

    #[ORM\Column(type: 'float')]
    #[Groups(['form'])]
    private $area;

    #[ORM\ManyToOne(targetEntity: ProductionData::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private $parent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(?float $area): self
    {
        $this->area = $area;

        return $this;
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
