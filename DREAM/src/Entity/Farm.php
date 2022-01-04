<?php

namespace App\Entity;

use App\Repository\FarmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FarmRepository::class)]
class Farm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $city;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $street;

    #[ORM\OneToOne(inversedBy: 'farm', targetEntity: Farmer::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $farmer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getFarmer(): ?Farmer
    {
        return $this->farmer;
    }

    public function setFarmer(Farmer $farmer): self
    {
        $this->farmer = $farmer;

        return $this;
    }
}
