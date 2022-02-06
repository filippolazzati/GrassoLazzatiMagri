<?php

namespace App\Entity;

use App\Entity\HelpRequest\HelpRequest;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'farmer' => Farmer::class,
    'agronomist' => Agronomist::class,
    'policy_maker' => PolicyMaker::class,
])]
#[UniqueEntity(fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $surname;

    #[ORM\Column(type: 'date')]
    private $birthDate;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $emailVerificationToken;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: HelpRequest::class, orphanRemoval: true)]
    private $receivedRequests;

    public function __construct()
    {
        $this->receivedRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if ($this instanceof Farmer) {
            $roles[] = 'ROLE_FARMER';
        } elseif ($this instanceof Agronomist) {
            $roles[] = 'ROLE_AGRONOMIST';
        } elseif ($this instanceof PolicyMaker) {
            $roles[] = 'ROLE_POLICY_MAKER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    public function getAvatarUrl(): string
    {
        return 'https://eu.ui-avatars.com/api/?name=' . urlencode($this->getFullName());
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    /**
     * @return Collection|HelpRequest[]
     */
    public function getReceivedRequests(): Collection
    {
        return $this->receivedRequests;
    }

    public function addReceivedRequest(HelpRequest $receivedRequest): self
    {
        if (!$this->receivedRequests->contains($receivedRequest)) {
            $this->receivedRequests[] = $receivedRequest;
            $receivedRequest->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedRequest(HelpRequest $receivedRequest): self
    {
        if ($this->receivedRequests->removeElement($receivedRequest)) {
            // set the owning side to null (unless already changed)
            if ($receivedRequest->getReceiver() === $this) {
                $receivedRequest->setReceiver(null);
            }
        }

        return $this;
    }

    public function isFarmer(): bool
    {
        return $this instanceof Farmer;
    }

    public function isAgronomist(): bool
    {
        return $this instanceof Agronomist;
    }

    public function equals(User $other): bool
    {
        return $this->id == $other->getId();
    }
}
