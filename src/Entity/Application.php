<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ApplicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCandidature = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $messageMotivation = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CandidateProfile $candidateProfile = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    public function __construct()
    {
        $this->dateCandidature = new \DateTimeImmutable();
        $this->statut = 'En attente';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCandidature(): ?\DateTimeImmutable
    {
        return $this->dateCandidature;
    }

    public function setDateCandidature(
        \DateTimeImmutable $dateCandidature
    ): static {
        $this->dateCandidature = $dateCandidature;

        return $this;
    }

    public function getMessageMotivation(): ?string
    {
        return $this->messageMotivation;
    }

    public function setMessageMotivation(
        ?string $messageMotivation
    ): static {
        $this->messageMotivation = $messageMotivation;

        return $this;
    }

    public function getCandidateProfile(): ?CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function setCandidateProfile(
        ?CandidateProfile $candidateProfile
    ): static {
        $this->candidateProfile = $candidateProfile;

        return $this;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(
        ?JobOffer $jobOffer
    ): static {
        $this->jobOffer = $jobOffer;

        return $this;
    }

    // ✅ AJOUT POUR FLUTTER

    public function getJobTitle(): ?string
    {
        return $this->jobOffer?->getTitre();
    }

    public function getCompanyName(): ?string
{
    return $this->jobOffer?->getCompany()?->getNomEntreprise();
}

public function getCompanyDescription(): ?string
{
    return $this->jobOffer?->getCompany()?->getDescription();
}

public function getJobLocation(): ?string
{
    return $this->jobOffer?->getLocalisation();
}

public function getContractType(): ?string
{
    return $this->jobOffer?->getTypeContrat();
}

public function getSalary(): ?float
{
    return $this->jobOffer?->getSalaire();
}

public function isRemotePossible(): ?bool
{
    return $this->jobOffer?->isRemotePossible();
}

public function getCandidateProfileTitle(): ?string
{
    return $this->candidateProfile?->getTitreProfil();
}

}