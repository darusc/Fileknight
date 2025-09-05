<?php

namespace Fileknight\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fileknight\Entity\Traits\TimestampTrait;
use Fileknight\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $username;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $resetRequired = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $resetTokenExp = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RefreshToken::class, cascade: ['remove'])]
    private Collection $refreshTokens;

    public function __construct()
    {
        $this->refreshTokens = new ArrayCollection();
        $this->id = str_replace('-', '', Uuid::uuid4()->toString());
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * @param string $resetToken The hashed reset token
     * @param int $lifetime Token will expire after given seconds
     * @return void
     */
    public function setResetToken(string $resetToken, int $lifetime): void
    {
        $this->resetToken = $resetToken;
        $this->resetTokenExp = time() + $lifetime;
    }

    public function invalidateToken(): void
    {
        $this->resetToken = null;
        $this->resetTokenExp = null;
    }

    /**
     * Gets the time as a unix timestamp when the reset token expires
     */
    public function getResetTokenExp(): ?int
    {
        return $this->resetTokenExp;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getResetRequired(): bool
    {
        return $this->resetRequired;
    }

    public function setResetRequired(bool $resetRequired): void
    {
        $this->resetRequired = $resetRequired;
    }

    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }
}
