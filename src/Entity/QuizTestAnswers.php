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

    #[ORM\OneToOne(inversedBy: 'quizTestAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizTest $quizTest = null;

    #[ORM\Column(type: 'json')]
    private array $questions = [];

    #[ORM\Column(type: 'json')]
    private array $answers = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizTest(): ?QuizTest
    {
        return $this->quizTest;
    }

    public function setQuizTest(?QuizTest $quizTest): static
    {
        $this->quizTest = $quizTest;

        return $this;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): static
    {
        $this->questions = $questions;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }
}
