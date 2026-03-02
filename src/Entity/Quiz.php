<?php

namespace App\Entity;

use App\Entity\Traits\ActivableEntityTrait;
use App\Entity\Traits\TimestampableEntityTrait;
use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[UniqueEntity(fields: ['name', 'topic', 'level'], message: 'Ya existe un cuestionario con ese nombre para este tema y nivel')]
class Quiz
{
    use TimestampableEntityTrait;
    use ActivableEntityTrait;

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
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
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
