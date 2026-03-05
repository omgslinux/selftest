<?php

namespace App\Controller;

use App\Entity\QuizTest;
use App\Repository\QuizRepository;
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
    public function home(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findBy(['active' => true], ['name' => 'ASC']);

        return $this->render('security/home.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

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
        $em->flush();

        return $this->redirectToRoute('app_quiz_test', ['id' => $quizTest->getId()]);
    }

    #[Route('/quiz/test/{id}', name: 'app_quiz_test')]
    public function test(int $id): Response
    {
        return $this->render('security/test.html.twig', [
            'testId' => $id,
        ]);
    }
}
