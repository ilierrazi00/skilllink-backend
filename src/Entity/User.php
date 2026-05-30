<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
        new Post(
            processor: UserPasswordHasher::class
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object == user",
            processor: UserPasswordHasher::class
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(
    fields: ['email'],
    message: 'There is already an account with this email'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $statut = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private ?bool $isVerified = null;

    /*
     |--------------------------------------------------------------------------
     | PREMIUM COMPLIANCE FIELDS
     |--------------------------------------------------------------------------
     */

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $acceptedTermsAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $acceptedPrivacyAt = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private ?CandidateProfile $candidateProfile = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private ?Company $company = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $roleType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $cvPath = null;

    public function __construct()
    {
        $this->roles = ['ROLE_CANDIDATE'];
        $this->roleType = 'candidate';
        $this->statut = 'ACTIF';
        $this->dateCreation = new \DateTimeImmutable();
        $this->isVerified = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    #[Groups(['user:read'])]
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash(
            'crc32c',
            $this->password
        );

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // Symfony future compatibility
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(
        \DateTimeImmutable $dateCreation
    ): static {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    #[Groups(['user:read'])]
    public function isVerifiedUser(): ?bool
    {
        return $this->isVerified;
    }

    /*
     |--------------------------------------------------------------------------
     | TERMS ACCEPTANCE
     |--------------------------------------------------------------------------
     */

    public function getAcceptedTermsAt(): ?\DateTimeImmutable
    {
        return $this->acceptedTermsAt;
    }

    public function setAcceptedTermsAt(
        ?\DateTimeImmutable $acceptedTermsAt
    ): static {
        $this->acceptedTermsAt = $acceptedTermsAt;

        return $this;
    }

    /*
     |--------------------------------------------------------------------------
     | PRIVACY ACCEPTANCE
     |--------------------------------------------------------------------------
     */

    public function getAcceptedPrivacyAt(): ?\DateTimeImmutable
    {
        return $this->acceptedPrivacyAt;
    }

    public function setAcceptedPrivacyAt(
        ?\DateTimeImmutable $acceptedPrivacyAt
    ): static {
        $this->acceptedPrivacyAt = $acceptedPrivacyAt;

        return $this;
    }

    public function getCandidateProfile(): ?CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function setCandidateProfile(
        CandidateProfile $candidateProfile
    ): static {
        if ($candidateProfile->getUser() !== $this) {
            $candidateProfile->setUser($this);
        }

        $this->candidateProfile = $candidateProfile;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(
        Company $company
    ): static {
        if ($company->getUser() !== $this) {
            $company->setUser($this);
        }

        $this->company = $company;

        return $this;
    }

    public function getRoleType(): ?string
    {
        return $this->roleType;
    }

    public function setRoleType(string $roleType): static
    {
        $this->roleType = $roleType;

        switch ($roleType) {
            case 'admin':
                $this->roles = ['ROLE_ADMIN'];
                break;

            case 'recruiter':
                $this->roles = ['ROLE_RECRUITER'];
                break;

            default:
                $this->roles = ['ROLE_CANDIDATE'];
                break;
        }

        return $this;
    }

    public function getCvPath(): ?string
{
    return $this->cvPath;
}

public function setCvPath(?string $cvPath): static
{
    $this->cvPath = $cvPath;

    return $this;
}

}