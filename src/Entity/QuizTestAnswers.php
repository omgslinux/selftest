<?php

namespace App\Entity;

use App\Repository\QuizTestAnswersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizTestAnswersRepository::class)]
class QuizTestAnswers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizTestAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizTest $test = null;

    #[ORM\Column(length: 255)]
    private ?string $user = null;

    #[ORM\ManyToOne(inversedBy: 'quizTestAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizQuestionAnswer $quizQuestionAnswer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTest(): ?QuizTest
    {
        return $this->test;
    }

    public function setTest(?QuizTest $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getQuizQuestionAnswer(): ?QuizQuestionAnswer
    {
        return $this->quizQuestionAnswer;
    }

    public function setQuizQuestionAnswer(?QuizQuestionAnswer $quizQuestionAnswer): static
    {
        $this->quizQuestionAnswer = $quizQuestionAnswer;

        return $this;
    }
}
