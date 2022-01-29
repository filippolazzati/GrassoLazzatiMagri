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

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: HelpRequest::class, orphanRemoval: true)]
    private $helpRequests;

    #[ORM\Column(type: 'boolean')]
    private $best_performing;

    #[ORM\Column(type: 'boolean')]
    private $worst_performing;

    public function __construct()
    {
        parent::__construct();
        $this->forumThreads = new ArrayCollection();
        $this->forumMessages = new ArrayCollection();
        $this->helpRequests = new ArrayCollection();
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

    /**
     * @return Collection|HelpRequest[]
     */
    public function getHelpRequests(): Collection
    {
        return $this->helpRequests;
    }

    public function addHelpRequest(HelpRequest $helpRequest): self
    {
        if (!$this->helpRequests->contains($helpRequest)) {
            $this->helpRequests[] = $helpRequest;
            $helpRequest->setAuthor($this);
        }

        return $this;
    }

    public function removeHelpRequest(HelpRequest $helpRequest): self
    {
        if ($this->helpRequests->removeElement($helpRequest)) {
            // set the owning side to null (unless already changed)
            if ($helpRequest->getAuthor() === $this) {
                $helpRequest->setAuthor(null);
            }
        }

        return $this;
    }

    public function getBestPerforming(): ?bool
    {
        return $this->best_performing;
    }

    public function setBestPerforming(bool $best_performing): self
    {
        $this->best_performing = $best_performing;

        return $this;
    }

    public function getWorstPerforming(): ?bool
    {
        return $this->worst_performing;
    }

    public function setWorstPerforming(bool $worst_performing): self
    {
        $this->worst_performing = $worst_performing;

        return $this;
    }

    public function hasMyReplies(): bool
    {
        // the farmer has the "my replies" section if he/she is best-performing or if he/she has ever
        // received a help_request (that is, he/she was best performing in the past)
        // in this way, a farmer who was best performing and now is not can visualize his/her old
        // replies, if there were any
        return $this->best_performing || !($this->getReceivedRequests()->isEmpty());
    }
}