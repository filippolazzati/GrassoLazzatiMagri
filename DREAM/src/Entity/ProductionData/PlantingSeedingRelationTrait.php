<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

trait PlantingSeedingRelationTrait
{
    #[ORM\ManyToOne(targetEntity: PlantingSeedingEntry::class)]
    private $relatedEntry;

    public function getRelatedEntry(): ?PlantingSeedingEntry
    {
        return $this->relatedEntry;
    }

    public function setRelatedEntry(?PlantingSeedingEntry $relatedEntry): self
    {
        $this->relatedEntry = $relatedEntry;

        return $this;
    }
}