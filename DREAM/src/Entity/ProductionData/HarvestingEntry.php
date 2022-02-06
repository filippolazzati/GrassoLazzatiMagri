<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class HarvestingEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;

    public function __toString(): string
    {
        return 'Harvesting ' . $this->getArea() . 'm² ' . $this->getRelatedEntry()->getCrop();
    }
}
