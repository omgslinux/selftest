<?php

namespace App\Twig\Components;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository as REPO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

#[AsLiveComponent]
class QuestionComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true)]
    public bool $isModalOpen = false;

    #[LiveProp(writable: true)]
    public ?Question $id=null;

    #[LiveProp]
    public string $modalId;

    #[LiveProp]
    public string $tagPrefix;

    public $itemName = 'question';


    public function __construct(private REPO $repo)
    {
    }

    #[LiveAction]
    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(QuestionType::class, $this->id);
    }

    #[LiveAction]
    public function new()
    {
        $this->id = new Question();
        $this->id->setActive(false);
        $this->resetForm();
        $this->isModalOpen = true;
    }

    #[LiveAction]
    public function edit(#[LiveArg] Question $id)
    {
        $this->id = $id;
        $this->resetForm();
        $this->isModalOpen = true;
    }

    #[LiveListener('deleteConfirmed')]
    public function delete(#[LiveArg] Question $id)
    {
        $this->repo->remove($id, true);
        $this->addFlash('success', $this->itemName .' deleted!');
    }

    public function getAll()
    {
        return $this->repo->findAll();
    }

    #[LiveAction]
    public function save()
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->repo->add($this->id, true);

            $this->addFlash('success', $this->itemName .' saved!');

            $this->resetForm();

            $this->isModalOpen = false;
        }
    }
}
