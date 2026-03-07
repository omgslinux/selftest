<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Level;
use App\Entity\QuizTest;
use App\Repository\CategoryRepository;
use App\Repository\LevelRepository;
use App\Repository\QuizRepository;
use App\Repository\QuizTestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        $this->requestStack->getSession()->invalidate();
        return $this->redirectToRoute('app_login');
    }

    #[Route('/', name: 'app_home')]
    public function home(
        QuizRepository $quizRepository,
        QuizTestRepository $quizTestRepository,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        LevelRepository $levelRepository,
        RequestStack $requestStack
    ): Response
    {
        $user = $this->getUser();
        
        $categoryId = $requestStack->getCurrentRequest()->query->get('category');
        $levelId = $requestStack->getCurrentRequest()->query->get('level');
        $status = $requestStack->getCurrentRequest()->query->get('status');
        
        $categories = $categoryRepository->findAll();
        $levels = $levelRepository->findAll();
        
        $criteria = ['active' => true];
        
        if ($categoryId) {
            $criteria['topic'] = $em->getRepository(\App\Entity\Topic::class)->findBy(['category' => $categoryId]);
            if (empty($criteria['topic'])) {
                $criteria['topic'] = null;
            }
        }
        
        if ($levelId) {
            $criteria['level'] = $levelId;
        }
        
        $quizzes = $quizRepository->findBy($criteria, ['name' => 'ASC']);
        
        $quizzesData = [];
        foreach ($quizzes as $quiz) {
            $questionCount = $quiz->getQuestions()->filter(fn($q) => $q->isActive())->count();
            
            if ($questionCount === 0) {
                continue;
            }

            $existingTest = $em->getRepository(QuizTest::class)->findOneBy(
                ['user' => $user, 'quiz' => $quiz],
                ['id' => 'DESC']
            );

            $completed = false;
            $score = null;
            if ($existingTest) {
                $answers = $existingTest->getQuizTestAnswers()?->getAnswers();
                $completed = !empty($answers);
                
                if ($completed) {
                    $correctCount = 0;
                    foreach ($existingTest->getQuizTestAnswers()?->getAnswers() ?? [] as $answerId) {
                        $answer = $em->getRepository(\App\Entity\QuizQuestionAnswer::class)->find($answerId);
                        if ($answer && $answer->isValid()) {
                            $correctCount++;
                        }
                    }
                    $score = $questionCount > 0 ? round(($correctCount / $questionCount) * 100) : 0;
                }
            }

            $quizzesData[] = [
                'quiz' => $quiz,
                'questionCount' => $questionCount,
                'completed' => $completed,
                'testId' => $existingTest?->getId(),
                'score' => $score,
            ];
        }

        if ($status === 'completed') {
            $quizzesData = array_filter($quizzesData, fn($item) => $item['completed']);
        } elseif ($status === 'pending') {
            $quizzesData = array_filter($quizzesData, fn($item) => !$item['completed']);
        }

        return $this->render('security/home.html.twig', [
            'quizzesData' => $quizzesData,
            'categories' => $categories,
            'levels' => $levels,
            'selectedCategory' => $categoryId,
            'selectedLevel' => $levelId,
            'selectedStatus' => $status,
        ]);
    }
}
