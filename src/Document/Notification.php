<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'notifications')]
class Notification
{
    #[ODM\Id]
    private ?string $id = null;

    /*
     |------------------------------------------------------------------
     | Utilisateur concerné
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'string')]
    private string $userId;

    /*
     |------------------------------------------------------------------
     | Type de notification
     | Exemples :
     | application_submitted
     | new_job_offer
     | profile_updated
     | security_alert
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'string')]
    private string $type;

    /*
     |------------------------------------------------------------------
     | Titre affiché
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'string')]
    private string $title;

    /*
     |------------------------------------------------------------------
     | Message détaillé
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'string')]
    private string $message;

    /*
     |------------------------------------------------------------------
     | Données complémentaires
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'hash')]
    private array $metadata = [];

    /*
     |------------------------------------------------------------------
     | Lecture
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'bool')]
    private bool $isRead = false;

    /*
     |------------------------------------------------------------------
     | Date création
     |------------------------------------------------------------------
     */
    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isRead = false;
    }

    /*
     |------------------------------------------------------------------
     | GETTERS / SETTERS
     |------------------------------------------------------------------
     */

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

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