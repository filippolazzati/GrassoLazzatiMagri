<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class FertilizingEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['form'])]
    private $fertilizerType;

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
