<?php

namespace App\Entity;

use App\Repository\HelpReplyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HelpReplyRepository::class)]
class HelpReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $timestamp;

    #[ORM\Column(type: 'string', length: 1500)]
    private $text;

    #[ORM\Column(type: 'string', length: 300, nullable: true)]
    private $feedback;

    public function __construct(string $text, \DateTimeInterface $timestamp)
    {
        $this->text = $text;
        $this->timestamp = $timestamp;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;

        return $this;
    }
}
