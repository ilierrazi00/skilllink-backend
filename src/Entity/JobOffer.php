<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\JobOfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    normalizationContext: ['groups' => ['job_offer:read']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ]
)]
#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
class JobOffer
{
    #[Groups(['job_offer:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column(length: 150)]
    private ?string $titre = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column(length: 50)]
    private ?string $typeContrat = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $salaire = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column]
    private ?bool $remotePossible = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column(length: 150)]
    private ?string $localisation = null;

    #[Groups(['job_offer:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\ManyToOne(inversedBy: 'jobOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class, inversedBy: 'jobOffers')]
    private Collection $skills;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(
        targetEntity: Application::class,
        mappedBy: 'jobOffer',
        cascade: ['remove']
    )]
    private Collection $applications;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->datePublication = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function getTitle(): ?string
    {
        return $this->titre;
    }   

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTypeContrat(): ?string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): static
    {
        $this->typeContrat = $typeContrat;

        return $this;
    }

    public function getSalaire(): ?int
    {
        return $this->salaire;
    }

    public function setSalaire(?int $salaire): static
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function isRemotePossible(): ?bool
    {
        return $this->remotePossible;
    }

    public function setRemotePossible(bool $remotePossible): static
    {
        $this->remotePossible = $remotePossible;

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

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(
        \DateTimeImmutable $datePublication
    ): static {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setJobOffer($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            if ($application->getJobOffer() === $this) {
                $application->setJobOffer(null);
            }
        }

        return $this;
    }

    #[Groups(['job_offer:read'])]
    public function getCompanyName(): ?string
    {
        return $this->company?->getNomEntreprise();
    }
}