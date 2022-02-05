<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class PlantingSeedingEntry extends ProductionDataEntry
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['form'])]
    private $crop;

    public function getCrop(): ?string
    {
        return $this->crop;
    }

    public function setCrop(string $crop): self
    {
        $this->crop = $crop;

        return $this;
    }
}
