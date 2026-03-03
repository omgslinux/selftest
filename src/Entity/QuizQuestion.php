<?php

namespace App\Entity;

use App\Repository\QuizQuestionRepository;
use App\Entity\Traits\ActivableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'quiz_questions')]
#[ORM\Entity(repositoryClass: QuizQuestionRepository::class)]
#[UniqueEntity(fields: 'text', message: 'Ya hay una pregunta con ese texto')]
class QuizQuestion
{
    use ActivableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $text = "";

    /**
     * @var Collection<int, QuizQuestionAnswer>
     */
    #[ORM\OneToMany(targetEntity: QuizQuestionAnswer::class, mappedBy: 'quizQuestion', cascade: ["persist", "remove"])]
    private Collection $answers;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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

    /**
     * @return Collection<int, QuizQuestionAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(QuizQuestionAnswer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuizQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(QuizQuestionAnswer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuizQuestion() === $this) {
                $answer->setQuizQuestion(null);
            }
        }

        return $this;
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
}
