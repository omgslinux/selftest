<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Topic $topic = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    /**
     * @var Collection<int, QuizTest>
     */
    #[ORM\OneToMany(targetEntity: QuizTest::class, mappedBy: 'quiz')]
    private Collection $quizTests;

    public function __construct()
    {
        $this->quizTests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): static
    {
        $this->topic = $topic;

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
            $quizTest->setQuiz($this);
        }

        return $this;
    }

    public function removeQuizTest(QuizTest $quizTest): static
    {
        if ($this->quizTests->removeElement($quizTest)) {
            // set the owning side to null (unless already changed)
            if ($quizTest->getQuiz() === $this) {
                $quizTest->setQuiz(null);
            }
        }

        return $this;
    }
}
