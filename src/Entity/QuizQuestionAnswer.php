<?php

namespace App\Entity;

use App\Repository\QuizQuestionAnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\ActivableEntityTrait;

#[ORM\Table(name: 'quiz_question_answers')]
#[ORM\Entity(repositoryClass: QuizQuestionAnswerRepository::class)]
class QuizQuestionAnswer
{
    use ActivableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizQuestion $quizQuestion = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valid = null;

    /**
     * @var Collection<int, QuizTestAnswers>
     */
    #[ORM\OneToMany(targetEntity: QuizTestAnswers::class, mappedBy: 'quizQuestionAnswer', cascade: ['persist', 'remove'])]
    private Collection $quizTestAnswers;

    public function __construct()
    {
        $this->quizTestAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getQuizQuestion(): ?QuizQuestion
    {
        return $this->quizQuestion;
    }

    public function setQuizQuestion(?QuizQuestion $quizQuestion): static
    {
        $this->quizQuestion = $quizQuestion;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): static
    {
        $this->valid = $valid;

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
            $quizTestAnswer->setQuizQuestionAnswer($this);
        }

        return $this;
    }

    public function removeQuizTestAnswer(QuizTestAnswers $quizTestAnswer): static
    {
        if ($this->quizTestAnswers->removeElement($quizTestAnswer)) {
            if ($quizTestAnswer->getQuizQuestionAnswer() === $this) {
                $quizTestAnswer->setQuizQuestionAnswer(null);
            }
        }

        return $this;
    }
}
