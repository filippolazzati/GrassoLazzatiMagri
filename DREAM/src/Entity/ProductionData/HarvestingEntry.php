<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class HarvestingEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;
}
