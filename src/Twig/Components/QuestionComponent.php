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
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

#[AsLiveComponent]
class QuestionComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    //public ?Question $initialFormData = null;

    #[LiveProp]
    public bool $isModalOpen = false;

    #[LiveProp]
    public ?Question $id=null;

    #[LiveProp]
    public string $modalId;

    #[LiveProp]
    public string $tagPrefix;

    public $itemName = 'question';


    public function __construct(private REPO $repo)
    {}

    #[LiveAction]
    protected function instantiateForm(): FormInterface
    {
        // we can extend AbstractController to get the normal shortcuts
        return $this->createForm(QuestionType::class, $this->id);
    }

    #[LiveAction]
    public function openModal()
    {
        //$this->resetValidation();
        $this->id = new Question();
        $this->resetForm();
        //$this->instantiateForm();
        //$this->isModalOpen = true;
    }

    #[LiveAction]
    public function edit(#[LiveArg] Question $id)
    {
        $this->id = $id;
        $this->resetForm();
        //$this->form = $this->instantiateForm();
        //$this->extractFormValues($this->getFormView());
        //$this->id = $item->id;
        //dd($this->form, $this->formValues);
        //$this->name = $item->name;
        $this->isModalOpen = true;
    }

    public function delete(Model $item)
    {}

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
        dump($this->form);

        //$question = $this->getForm()->getData();

        $this->repo->add($this->id, true);

        $this->addFlash('success', 'Post saved!');
        $this->isModalOpen = false;

        $this->resetForm();
        //dd($question);sleep(100);
    }
}
