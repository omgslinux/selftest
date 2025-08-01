<?php

namespace App\Twig\Components;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\Url;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class QuestionCrudGPTComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[Url]
    public ?int $questionId = null;

    private ?Question $question = null;

    public array $questions = [];

    public function __construct(
        private QuestionRepository $questionRepository
    ) {}

    public function mount(): void
    {
        $this->loadQuestions();
    }

    public function loadQuestions(): void
    {
        $this->questions = $this->questionRepository->findAll();
    }

    public function openModal(?int $questionId = null): void
    {
        // Si se pasa un ID, cargamos la pregunta para editarla.
        $this->questionId = $questionId;

        if ($this->questionId) {
            $this->question = $this->questionRepository->find($this->questionId);
        } else {
            $this->question = new Question(); // Si es un nuevo registro, inicializamos una nueva pregunta.
        }
    }

    public function instantiateForm(): FormInterface
    {
        // Creamos el formulario para la pregunta
        return $this->createForm(QuestionType::class, $this->question);
    }

    public function save(): void
    {
        if ($this->isValid()) {
            $this->questionRepository->save($this->question, true);
            $this->addFlash('success', 'Pregunta guardada correctamente.');

            // Recargamos las preguntas después de guardar
            $this->loadQuestions();
            $this->questionId = null; // Reseteamos el ID de la pregunta, cerrando el modal
        }
    }

    public function delete(int $id): void
    {
        $q = $this->questionRepository->find($id);
        if ($q) {
            $this->questionRepository->remove($q, true);
            $this->addFlash('danger', 'Pregunta eliminada.');
        }

        // Recargamos las preguntas después de eliminar
        $this->loadQuestions();
    }
}
