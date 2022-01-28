<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class HarvestingEntry extends ProductionDataEntry
{
    use PlantingSeedingRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
