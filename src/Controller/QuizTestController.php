<?php

namespace App\Controller;

use App\Entity\QuizTest;
use App\Entity\QuizTestAnswers;
use App\Repository\QuizRepository;
use App\Repository\QuizTestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizTestController extends AbstractController
{
    #[Route('/quiz/start/{id}', name: 'app_quiz_start')]
    public function startQuiz(int $id, QuizRepository $quizRepository, EntityManagerInterface $em): Response
    {
        $quiz = $quizRepository->find($id);
        
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz no encontrado');
        }

        $user = $this->getUser();
        
        $quizTest = new QuizTest();
        $quizTest->setQuiz($quiz);
        $quizTest->setUser($user);
        
        $em->persist($quizTest);
        
        $questions = $quiz->getQuestions()->filter(fn($q) => $q->isActive());
        $questionsArray = $questions->getValues();
        shuffle($questionsArray);
        
        $questionsJson = [];
        
        foreach ($questionsArray as $index => $question) {
            $answers = $question->getAnswers()->filter(fn($a) => $a->isActive())->getValues();
            shuffle($answers);
            
            $answersJson = [];
            foreach ($answers as $answerIndex => $answer) {
                $answersJson[] = [
                    'id' => $answer->getId(),
                    'text' => $answer->getText(),
                    'correct' => $answer->isValid(),
                ];
            }
            
            $questionsJson[] = [
                'id' => $question->getId(),
                'text' => $question->getText(),
                'order' => $index,
                'answers' => $answersJson,
            ];
        }
        
        $quizTestAnswers = new QuizTestAnswers();
        $quizTestAnswers->setQuizTest($quizTest);
        $quizTestAnswers->setQuestions($questionsJson);
        $quizTestAnswers->setAnswers([]);
        
        $em->persist($quizTestAnswers);
        $em->flush();

        return $this->redirectToRoute('app_quiz_test', ['id' => $quizTest->getId()]);
    }

    #[Route('/quiz/test/{id}', name: 'app_quiz_test')]
    public function test(int $id, QuizTestRepository $quizTestRepository): Response
    {
        $quizTest = $quizTestRepository->find($id);
        
        if (!$quizTest) {
            throw $this->createNotFoundException('Test no encontrado');
        }

        $questions = $quizTest->getQuizTestAnswers()?->getQuestions() ?? [];

        return $this->render('security/test.html.twig', [
            'quizTest' => $quizTest,
            'questions' => $questions,
        ]);
    }

    #[Route('/quiz/submit/{id}', name: 'app_quiz_submit')]
    public function submit(int $id, QuizTestRepository $quizTestRepository, Request $request, EntityManagerInterface $em): Response
    {
        $quizTest = $quizTestRepository->find($id);
        
        if (!$quizTest) {
            throw $this->createNotFoundException('Test no encontrado');
        }

        $answers = $request->request->all('answers');
        
        $quizTestAnswers = $quizTest->getQuizTestAnswers();
        if ($quizTestAnswers) {
            $quizTestAnswers->setAnswers($answers ?: []);
            $em->flush();
        }

        return $this->redirectToRoute('app_quiz_result', ['id' => $quizTest->getId()]);
    }

    #[Route('/quiz/result/{id}', name: 'app_quiz_result')]
    public function result(int $id, QuizTestRepository $quizTestRepository): Response
    {
        $quizTest = $quizTestRepository->find($id);
        
        if (!$quizTest) {
            throw $this->createNotFoundException('Test no encontrado');
        }

        $quizTestAnswers = $quizTest->getQuizTestAnswers();
        $questions = $quizTestAnswers?->getQuestions() ?? [];
        $userAnswers = $quizTestAnswers?->getAnswers() ?? [];
        
        $correctCount = 0;
        $totalQuestions = count($questions);
        
        $results = [];
        foreach ($questions as $question) {
            $userAnswerId = $userAnswers[$question['id']] ?? null;
            $correctAnswer = null;
            
            foreach ($question['answers'] as $answer) {
                if ($answer['correct']) {
                    $correctAnswer = $answer;
                    break;
                }
            }
            
            $isCorrect = $userAnswerId == $correctAnswer['id'];
            if ($isCorrect) {
                $correctCount++;
            }
            
            $results[] = [
                'question' => $question,
                'userAnswerId' => $userAnswerId,
                'correctAnswer' => $correctAnswer,
                'isCorrect' => $isCorrect,
            ];
        }
        
        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        return $this->render('security/result.html.twig', [
            'quizTest' => $quizTest,
            'results' => $results,
            'correctCount' => $correctCount,
            'totalQuestions' => $totalQuestions,
            'score' => $score,
        ]);
    }
}
