<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait PlantingSeedingRelationTrait
{
    #[ORM\ManyToOne(targetEntity: PlantingSeedingEntry::class)]
    #[Groups(['form'])]
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