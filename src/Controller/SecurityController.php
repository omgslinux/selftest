<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Level;
use App\Entity\QuizTest;
use App\Entity\Topic;
use App\Repository\CategoryRepository;
use App\Repository\LevelRepository;
use App\Repository\QuizRepository;
use App\Repository\QuizTestRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
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
        TopicRepository $topicRepository,
        RequestStack $requestStack
    ): Response
    {
        $user = $this->getUser();
        $session = $requestStack->getSession();
        
        $request = $requestStack->getCurrentRequest();
        $clear = $request->query->get('clear');
        
        if ($clear) {
            $session->remove('quiz_filters');
            return $this->redirectToRoute('app_home');
        }
        
$categoryId = $request->query->get('category');
        $levelId = $request->query->get('level');
        $topicId = $request->query->get('topic');
        $status = $request->query->get('status');
        
        if ($categoryId !== null || $levelId !== null || $topicId !== null || $status !== null) {
            $session->set('quiz_filters', [
                'category' => $categoryId,
                'level' => $levelId,
                'topic' => $topicId,
                'status' => $status,
            ]);
        } else {
            $filters = $session->get('quiz_filters', []);
            $categoryId = $filters['category'] ?? null;
            $levelId = $filters['level'] ?? null;
            $topicId = $filters['topic'] ?? null;
            $status = $filters['status'] ?? null;
        }
        
        $categories = $categoryRepository->findAll();
        $levels = $levelRepository->findAll();
        
        $topics = [];
        if ($categoryId) {
            $topics = $topicRepository->findBy(['category' => $categoryId, 'active' => true]);
        }
        
        $criteria = ['active' => true];
        
        if ($categoryId) {
            $criteria['topic'] = $em->getRepository(Topic::class)->findBy(['category' => $categoryId]);
            if (empty($criteria['topic'])) {
                $criteria['topic'] = null;
            }
        }
        
        if ($topicId) {
            $criteria['topic'] = $em->getRepository(Topic::class)->find($topicId);
        }
        
        if ($levelId) {
            $criteria['level'] = $levelId;
        }
        
        $categories = $categoryRepository->findAll();
        $levels = $levelRepository->findAll();
        
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
                    $questions = $existingTest->getQuizTestAnswers()?->getQuestions() ?? [];
                    $userAnswers = $existingTest->getQuizTestAnswers()?->getAnswers() ?? [];
                    $correctCount = 0;
                    
                    foreach ($questions as $question) {
                        $userAnswerId = $userAnswers[(string)$question['id']] ?? null;
                        
                        foreach ($question['answers'] as $answer) {
                            if ($answer['correct']) {
                                if ($userAnswerId !== null && (string)$userAnswerId === (string)$answer['id']) {
                                    $correctCount++;
                                }
                                break;
                            }
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

        $totalQuizzes = count($quizzesData);
        $totalQuestions = array_sum(array_column($quizzesData, 'questionCount'));

        return $this->render('security/home.html.twig', [
            'quizzesData' => $quizzesData,
            'categories' => $categories,
            'levels' => $levels,
            'topics' => $topics,
            'selectedCategory' => $categoryId,
            'selectedLevel' => $levelId,
            'selectedTopic' => $topicId,
            'selectedStatus' => $status,
            'totalQuizzes' => $totalQuizzes,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    #[Route('/api/topics', name: 'api_topics')]
    public function getTopicsByCategory(
        Request $request,
        TopicRepository $topicRepository
    ): JsonResponse {
        $categoryId = $request->query->get('category');
        
        if (!$categoryId) {
            return new JsonResponse([]);
        }
        
        $topics = $topicRepository->findBy(
            ['category' => $categoryId, 'active' => true],
            ['name' => 'ASC']
        );
        
        return new JsonResponse(
            array_map(fn($topic) => ['id' => $topic->getId(), 'name' => (string) $topic], $topics)
        );
    }

    #[Route('/export/{topicId}', name: 'app_export')]
    public function export(
        int $topicId,
        TopicRepository $topicRepository,
        QuizRepository $quizRepository,
        QuizQuestionRepository $quizQuestionRepository
    ): Response {
        $topic = $topicRepository->find($topicId);
        if (!$topic) {
            throw $this->createNotFoundException('Topic not found');
        }

        $category = $topic->getCategory()->getName();
        $topicName = $topic->getName();
        $quizzes = $quizRepository->findBy(['topic' => $topic, 'active' => true], ['name' => 'ASC']);
        
        $exportData = [
            'category' => $category,
            'name' => $topicName,
            'quizzes' => []
        ];

        foreach ($quizzes as $quiz) {
            $questions = $quizQuestionRepository->findBy(['quiz' => $quiz, 'active' => true]);
            $questionsData = [];
            
            foreach ($questions as $question) {
                $answersData = [];
                foreach ($question->getAnswers() as $answer) {
                    $answersData[] = [
                        'text' => $answer->getText(),
                        'correct' => $answer->isValid(),
                    ];
                }
                
                $questionData = [
                    'text' => $question->getText(),
                    'answers' => $answersData,
                ];
                
                if ($question->getIntro()) {
                    $questionData['intro'] = $question->getIntro();
                }
                
                $questionsData[] = $questionData;
            }
            
            if (!empty($questionsData)) {
                $exportData['quizzes'][] = [
                    'quiz' => $quiz->getName(),
                    'level' => $quiz->getLevel()->getId(),
                    'questions' => $questionsData,
                ];
            }
        }

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $filename = $category . '_' . $topicName . '.json';
        
        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
