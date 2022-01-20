<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Agronomist extends User
{
    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'agronomists')]
    private $area;

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

        return $this;
    }
}