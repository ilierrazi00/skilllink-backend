<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\CandidateProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
#[ORM\Entity(repositoryClass: CandidateProfileRepository::class)]
class CandidateProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $titreProfil = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column]
    private ?int $experienceAnnees = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvUrl = null;

    #[ORM\Column(length: 150)]
    private ?string $localisation = null;

    #[ORM\Column]
    private ?bool $disponibilite = null;

    #[ORM\OneToOne(
        inversedBy: 'candidateProfile',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(
        targetEntity: Skill::class,
        mappedBy: 'candidateProfiles'
    )]
    private Collection $skills;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(
        targetEntity: Application::class,
        mappedBy: 'candidateProfile',
        cascade: ['remove']
    )]
    private Collection $applications;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreProfil(): ?string
    {
        return $this->titreProfil;
    }

    public function setTitreProfil(string $titreProfil): static
    {
        $this->titreProfil = $titreProfil;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getExperienceAnnees(): ?int
    {
        return $this->experienceAnnees;
    }

    public function setExperienceAnnees(
        int $experienceAnnees
    ): static {
        $this->experienceAnnees = $experienceAnnees;

        return $this;
    }

    public function getCvUrl(): ?string
    {
        return $this->cvUrl;
    }

    public function setCvUrl(?string $cvUrl): static
    {
        $this->cvUrl = $cvUrl;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function getDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addCandidateProfile($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            $skill->removeCandidateProfile($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(
        Application $application
    ): static {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setCandidateProfile($this);
        }

        return $this;
    }

    public function removeApplication(
        Application $application
    ): static {
        if ($this->applications->removeElement($application)) {
            if ($application->getCandidateProfile() === $this) {
                $application->setCandidateProfile(null);
            }
        }

        return $this;
    }
}