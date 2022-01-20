<?php

namespace App\Entity;

use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'area', targetEntity: Farm::class)]
    private $farms;

    #[ORM\OneToMany(mappedBy: 'area', targetEntity: Agronomist::class)]
    private $agronomists;


    public function __construct(string $name)
    {
        $this->name = $name;
        $this->farms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Farm[]
     */
    public function getFarms(): Collection
    {
        return $this->farms;
    }

    public function addFarm(Farm $farm): self
    {
        if (!$this->farms->contains($farm)) {
            $this->farms[] = $farm;
            $farm->setArea($this);
        }

        return $this;
    }

    public function removeFarm(Farm $farm): self
    {
        if ($this->farms->removeElement($farm)) {
            // set the owning side to null (unless already changed)
            if ($farm->getArea() === $this) {
                $farm->setArea(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getAgronomists() : Collection
    {
        return $this->agronomists;
    }

    public function addAgronomist(Agronomist $agronomist): self
    {
        if (!$this->agronomists->contains($agronomist)) {
            $this->agronomists[] = $agronomist;
            $agronomist->setArea($this);
        }

        return $this;
    }

    public function removeAgronomist(Agronomist $agronomist): self
    {
        if ($this->agronomists->removeElement($agronomist)) {
            // set the owning side to null (unless already changed)
            if ($agronomist->getArea() === $this) {
                $agronomist->setArea(null);
            }
        }

        return $this;
    }
}
