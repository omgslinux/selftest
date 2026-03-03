<?php

namespace App\Entity;

use App\Entity\Traits\ActivableEntityTrait;
use App\Entity\Traits\TimestampableEntityTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntityTrait;
    use ActivableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    /**
     * @var Collection<int, QuizTest>
     */
    #[ORM\OneToMany(targetEntity: QuizTest::class, mappedBy: 'user')]
    private Collection $quizTests;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->active = true;
        $this->quizTests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role)];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->username ?? '';
    }

    public function __toString(): string
    {
        return $this->name ?? $this->username ?? '';
    }

    /**
     * @return Collection<int, QuizTest>
     */
    public function getQuizTests(): Collection
    {
        return $this->quizTests;
    }

    public function addQuizTest(QuizTest $quizTest): static
    {
        if (!$this->quizTests->contains($quizTest)) {
            $this->quizTests->add($quizTest);
            $quizTest->setUser($this);
        }

        return $this;
    }

    public function removeQuizTest(QuizTest $quizTest): static
    {
        if ($this->quizTests->removeElement($quizTest)) {
            if ($quizTest->getUser() === $this) {
                $quizTest->setUser(null);
            }
        }

        return $this;
    }
}
