<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FertilizingEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $fertilizerType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFertilizerType(): ?string
    {
        return $this->fertilizerType;
    }

    public function setFertilizerType(string $fertilizerType): self
    {
        $this->fertilizerType = $fertilizerType;

        return $this;
    }
}
