<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'messages')]
class Message
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private string $senderId;

    #[ODM\Field(type: 'string')]
    private string $receiverId;

    #[ODM\Field(type: 'string')]
    private string $senderEmail;

    #[ODM\Field(type: 'string')]
    private string $receiverEmail;

    #[ODM\Field(type: 'string')]
    private string $content;

    #[ODM\Field(type: 'bool')]
    private bool $isRead = false;

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isRead = false;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): self
    {
        $this->senderId = $senderId;
        return $this;
    }

    public function getReceiverId(): string
    {
        return $this->receiverId;
    }

    public function setReceiverId(string $receiverId): self
    {
        $this->receiverId = $receiverId;
        return $this;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): self
    {
        $this->senderEmail = $senderEmail;
        return $this;
    }

    public function getReceiverEmail(): string
    {
        return $this->receiverEmail;
    }

    public function setReceiverEmail(string $receiverEmail): self
    {
        $this->receiverEmail = $receiverEmail;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function markAsRead(): self
    {
        $this->isRead = true;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}