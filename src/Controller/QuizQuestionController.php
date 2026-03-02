<?php

namespace App\Controller;

use App\Entity\QuizQuestion;
use App\Form\QuizQuestionType;
use App\Repository\QuizQuestionRepository as REPO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz-question', name: 'app_quiz_question_')]
final class QuizQuestionController extends AbstractController
{
    private const PREFIX = 'app_quiz_question_';
    private const TDIR = 'quiz-question';

    #[Route(name: 'index', methods: ['GET'])]
    public function index(REPO $repo): Response
    {
        $VARS = [
            'modalSize' => 'modal-lg',
        ];


        return $this->render(self::TDIR . '/index.html.twig', [
            'questions' => $repo->findAll(),
            'targetPrefix' => 'questions',
            'PREFIX' => self::PREFIX,
            'VARS' => $VARS,
        ]);
    }

    #[Route('/turbo', name: 'indexTurbo', methods: ['GET'])]
    public function indexTurbo(REPO $questionRepository): Response
    {
        return $this->render(self::TDIR . '/index.html.twig', [
            //'questions' => $questionRepository->findAll(),
            'tagPrefix' => 'edit',
            'modalId' => 'questions'
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, REPO $repo): Response
    {
        $question = new QuizQuestion();
        $form = $this->createForm(
            QuizQuestionType::class,
            $question,
            ['action' => $this->generateUrl(self::PREFIX. 'new')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($question, true);

            return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(self::TDIR . '/new.html.twig', [
            'question' => $question,
            'form' => $form,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(QuizQuestion $question): Response
    {
        return $this->render(self::TDIR . '/show.html.twig', [
            'question' => $question,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuizQuestion $question, REPO $repo): Response
    {
        $form = $this->createForm(
            QuizQuestionType::class,
            $question,
            ['action' => $this->generateUrl(self::PREFIX. 'edit', ['id' => $question->getId()])]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($question, true);

            return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(self::TDIR . '/edit.html.twig', [
            'question' => $question,
            'form' => $form,
            'PREFIX' => self::PREFIX,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, QuizQuestion $question, REPO $repo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->getPayload()->getString('_token'))) {
            $repo->remove($question, true);
        }

        return $this->redirectToRoute(self::PREFIX . 'index', [], Response::HTTP_SEE_OTHER);
    }
}
