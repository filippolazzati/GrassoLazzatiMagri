<?php

namespace App\Entity\ProductionData;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlantingSeedingEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $crop;

    public function getId(): ?int
    {
        return $this->id;
    }

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
