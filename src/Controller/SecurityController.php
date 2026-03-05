<?php

namespace App\Controller;

use App\Entity\QuizTest;
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
    public function home(QuizRepository $quizRepository, QuizTestRepository $quizTestRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        $quizzes = $quizRepository->findBy(['active' => true], ['name' => 'ASC']);
        
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
            if ($existingTest) {
                $answers = $existingTest->getQuizTestAnswers()?->getAnswers();
                $completed = !empty($answers);
            }

            $quizzesData[] = [
                'quiz' => $quiz,
                'questionCount' => $questionCount,
                'completed' => $completed,
                'testId' => $existingTest?->getId(),
            ];
        }

        return $this->render('security/home.html.twig', [
            'quizzesData' => $quizzesData,
        ]);
    }
}
