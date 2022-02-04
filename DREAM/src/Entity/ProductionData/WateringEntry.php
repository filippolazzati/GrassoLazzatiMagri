<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class WateringEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;
}
