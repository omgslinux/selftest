<?php

namespace App\Entity;

use App\Repository\QuizTestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizTestRepository::class)]
class QuizTest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(inversedBy: 'quizTests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    /**
     * @var Collection<int, QuizTestAnswers>
     */
    #[ORM\OneToMany(targetEntity: QuizTestAnswers::class, mappedBy: 'test')]
    private Collection $quizTestAnswers;

    public function __construct()
    {
        $this->quizTestAnswers = new ArrayCollection();
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection<int, QuizTestAnswers>
     */
    public function getQuizTestAnswers(): Collection
    {
        return $this->quizTestAnswers;
    }

    public function addQuizTestAnswer(QuizTestAnswers $quizTestAnswer): static
    {
        if (!$this->quizTestAnswers->contains($quizTestAnswer)) {
            $this->quizTestAnswers->add($quizTestAnswer);
            $quizTestAnswer->setTest($this);
        }

        return $this;
    }

    public function removeQuizTestAnswer(QuizTestAnswers $quizTestAnswer): static
    {
        if ($this->quizTestAnswers->removeElement($quizTestAnswer)) {
            // set the owning side to null (unless already changed)
            if ($quizTestAnswer->getTest() === $this) {
                $quizTestAnswer->setTest(null);
            }
        }

        return $this;
    }
}
