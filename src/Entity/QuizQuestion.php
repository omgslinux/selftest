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
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ["persist"])]
    private Collection $answers;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Topic $topic = null;

    /**
     * @var Collection<int, QuizTest>
     */
    #[ORM\OneToMany(targetEntity: QuizTest::class, mappedBy: 'question')]
    private Collection $quizTests;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->quizTests = new ArrayCollection();
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
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): static
    {
        $this->topic = $topic;

        return $this;
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
            $quizTest->setQuestion($this);
        }

        return $this;
    }

    public function removeQuizTest(QuizTest $quizTest): static
    {
        if ($this->quizTests->removeElement($quizTest)) {
            // set the owning side to null (unless already changed)
            if ($quizTest->getQuestion() === $this) {
                $quizTest->setQuestion(null);
            }
        }

        return $this;
    }
}
