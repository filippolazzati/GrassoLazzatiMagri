<?php

namespace App\Entity;

use App\Entity\Forum\Message;
use App\Entity\Forum\Thread;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Farmer extends User
{
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Thread::class, orphanRemoval: true)]
    private $forumThreads;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class, orphanRemoval: true)]
    private $forumMessages;

    #[ORM\OneToOne(mappedBy: 'farmer', targetEntity: Farm::class, cascade: ['persist', 'remove'])]
    private $farm;

    public function __construct()
    {
        $this->forumThreads = new ArrayCollection();
        $this->forumMessages = new ArrayCollection();
        $this->setFarm(new Farm());
    }

    /**
     * @return Collection|Thread[]
     */
    public function getForumThreads(): Collection
    {
        return $this->forumThreads;
    }

    public function addForumThread(Thread $forumThread): self
    {
        if (!$this->forumThreads->contains($forumThread)) {
            $this->forumThreads[] = $forumThread;
            $forumThread->setAuthor($this);
        }

        return $this;
    }

    public function removeForumThread(Thread $forumThread): self
    {
        if ($this->forumThreads->removeElement($forumThread)) {
            // set the owning side to null (unless already changed)
            if ($forumThread->getAuthor() === $this) {
                $forumThread->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getForumMessages(): Collection
    {
        return $this->forumMessages;
    }

    public function addForumMessage(Message $forumMessage): self
    {
        if (!$this->forumMessages->contains($forumMessage)) {
            $this->forumMessages[] = $forumMessage;
            $forumMessage->setAuthor($this);
        }

        return $this;
    }

    public function removeForumMessage(Message $forumMessage): self
    {
        if ($this->forumMessages->removeElement($forumMessage)) {
            // set the owning side to null (unless already changed)
            if ($forumMessage->getAuthor() === $this) {
                $forumMessage->setAuthor(null);
            }
        }

        return $this;
    }

    public function getFarm(): ?Farm
    {
        return $this->farm;
    }

    public function setFarm(Farm $farm): self
    {
        // set the owning side of the relation if necessary
        if ($farm->getFarmer() !== $this) {
            $farm->setFarmer($this);
        }

        $this->farm = $farm;

        return $this;
    }
}