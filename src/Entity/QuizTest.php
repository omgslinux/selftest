<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntityTrait;
use App\Repository\QuizTestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizTestRepository::class)]
class QuizTest
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(inversedBy: 'quizTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'quizTest', cascade: ['persist', 'remove'])]
    private ?QuizTestAnswers $quizTestAnswers = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getQuizTestAnswers(): ?QuizTestAnswers
    {
        return $this->quizTestAnswers;
    }

    public function setQuizTestAnswers(?QuizTestAnswers $quizTestAnswers): static
    {
        $this->quizTestAnswers = $quizTestAnswers;

        return $this;
    }
}
