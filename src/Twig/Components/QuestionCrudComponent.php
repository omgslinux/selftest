<?php
// src/Twig/Components/Question.php

namespace App\Twig\Components;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository as REPO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Form\FormInterface;

#[AsLiveComponent]
class QuestionCrudComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public string $modalMode = 'none'; // 'none', 'show', 'edit', 'new'

    #[LiveProp(writable: true)]
    public ?Question $entity = null;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp]
    public int $itemsPerPage = 5;

    public function __construct(private REPO $repo, private EntityManagerInterface $entityManager)
    {
    }

    public function getQuestions(): array
    {
        return $this->repo
            ->findBy([], ['id' => 'DESC'], $this->itemsPerPage, ($this->page - 1) * $this->itemsPerPage);
    }

    public function getCurrentQuestion(): ?Question
    {
        return $this->entity;
    }

    public function getTotalQuestions(): int
    {
        return $this->repo
            ->count([]);
    }

    #[LiveAction]
    public function showQuestion(#[LiveArg]Question $entity)
    {
        $this->modalMode = 'show';
        $this->entity = $entity;
    }

    #[LiveAction]
    public function editQuestion(#[LiveArg]Question $entity)
    {
        $this->modalMode = 'edit';
        $this->entity = $entity;
    }

    #[LiveAction]
    public function newQuestion()
    {
        $this->modalMode = 'new';
        $this->entity = null;
    }

    #[LiveAction]
    public function deleteQuestion(#[LiveArg]Question $entity)
    {
        // $entity = $this->entityManager->getRepository(Question::class)->find($id);

        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $this->addFlash('success', 'Question deleted successfully');
        }
    }

    #[LiveAction]
    public function closeModal()
    {
        $this->modalMode = 'none';
        $this->entity = null;
    }

    #[LiveAction]
    public function saveQuestion()
    {
        $this->submitForm();

        $entity = $this->getFormInstance()->getData();
        $this->repo->add($entity, true);

        $this->modalMode = 'none';
        $this->addFlash('success', 'Question saved successfully');
    }

    protected function instantiateForm(): FormInterface
    {
        $entity = $this->entity
            ? $this->getCurrentQuestion()
            : new Question();

        return $this->createForm(QuestionType::class, $entity);
    }
}
