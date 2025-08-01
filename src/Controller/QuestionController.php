<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository as REPO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/question', name: 'app_question_')]
final class QuestionController extends AbstractController
{
    const PREFIX = 'app_question_';

    #[Route(name: 'index', methods: ['GET'])]
    public function index(REPO $repo): Response
    {
        $VARS = [
            'modalSize' => 'modal-lg',
        ];


        return $this->render('question/index.html.twig', [
            'questions' => $repo->findAll(),
            'targetPrefix' => 'questions',
            'PREFIX' => self::PREFIX,
            'VARS' => $VARS,
        ]);
    }

    #[Route('/turbo', name: 'indexTurbo', methods: ['GET'])]
    public function indexTurbo(REPO $questionRepository): Response
    {
        return $this->render('question/index.html.twig', [
            //'questions' => $questionRepository->findAll(),
            'tagPrefix' => 'edit',
            'modalId' => 'questions'
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, REPO $repo): Response
    {
        $question = new Question();
        $form = $this->createForm(
            QuestionType::class,
            $question,
            ['action' => $this->generateUrl(self::PREFIX. 'new')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($question, true);

            return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, REPO $repo): Response
    {
        $form = $this->createForm(
            QuestionType::class,
            $question,
            ['action' => $this->generateUrl(self::PREFIX. 'edit', ['id' => $question->getId()])]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($question, true);

            return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, REPO $repo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->getPayload()->getString('_token'))) {
            $repo->remove($question, true);
        }

        return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
    }
}
