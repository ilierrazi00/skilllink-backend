<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorie = null;

    /**
     * @var Collection<int, CandidateProfile>
     */
    #[ORM\ManyToMany(
        targetEntity: CandidateProfile::class,
        inversedBy: 'skills'
    )]
    private Collection $candidateProfiles;

    /**
     * @var Collection<int, JobOffer>
     */
    #[ORM\ManyToMany(
        targetEntity: JobOffer::class,
        mappedBy: 'skills'
    )]
    private Collection $jobOffers;

    public function __construct()
    {
        $this->candidateProfiles = new ArrayCollection();
        $this->jobOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, CandidateProfile>
     */
    public function getCandidateProfiles(): Collection
    {
        return $this->candidateProfiles;
    }

    public function addCandidateProfile(
        CandidateProfile $candidateProfile
    ): static {
        if (!$this->candidateProfiles->contains($candidateProfile)) {
            $this->candidateProfiles->add($candidateProfile);
            $candidateProfile->addSkill($this);
        }

        return $this;
    }

    public function removeCandidateProfile(
        CandidateProfile $candidateProfile
    ): static {
        if ($this->candidateProfiles->removeElement($candidateProfile)) {
            $candidateProfile->removeSkill($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, JobOffer>
     */
    public function getJobOffers(): Collection
    {
        return $this->jobOffers;
    }

    public function addJobOffer(
        JobOffer $jobOffer
    ): static {
        if (!$this->jobOffers->contains($jobOffer)) {
            $this->jobOffers->add($jobOffer);
            $jobOffer->addSkill($this);
        }

        return $this;
    }

    public function removeJobOffer(
        JobOffer $jobOffer
    ): static {
        if ($this->jobOffers->removeElement($jobOffer)) {
            $jobOffer->removeSkill($this);
        }

        return $this;
    }
}