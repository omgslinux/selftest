<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Level;
use App\Entity\Quiz;
use App\Entity\QuizQuestion;
use App\Entity\QuizQuestionAnswer;
use App\Entity\Topic;
use App\Repository\CategoryRepository;
use App\Repository\LevelRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QuestionImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private TopicRepository $topicRepository,
        private LevelRepository $levelRepository,
        private QuizRepository $quizRepository,
        private QuizQuestionRepository $quizQuestionRepository
    ) {}

    public function importJson(string $content, bool $replace = false): array
    {
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al parsear JSON: ' . json_last_error_msg());
        }

        $stats = ['categories' => 0, 'topics' => 0, 'quizzes' => 0, 'questions' => 0, 'answers' => 0];

        $categoryName = trim($json['category'] ?? '');
        if (empty($categoryName)) {
            throw new \Exception('Categoría no definida en el JSON');
        }

        $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $this->em->persist($category);
            $this->em->flush($category);
            $stats['categories']++;
        }

        $topicName = trim($json['name'] ?? '');
        if (empty($topicName)) {
            throw new \Exception('Topic name no definido en el JSON');
        }

        $topic = $this->topicRepository->findOneBy([
            'name' => $topicName,
            'category' => $category
        ]);
        if (!$topic) {
            $topic = new Topic();
            $topic->setName($topicName);
            $topic->setCategory($category);
            $this->em->persist($topic);
            $stats['topics']++;
        }

        $quizzesData = $json['quizzes'] ?? [$json];
        foreach ($quizzesData as $quizData) {
            $quizName = trim($quizData['quiz'] ?? $quizData['name'] ?? '');
            $levelNum = (int) ($quizData['level'] ?? 1);

            if (empty($quizName)) {
                continue;
            }

            $level = $this->levelRepository->find($levelNum);
            if (!$level) {
                continue;
            }

            $quiz = $this->quizRepository->findOneBy([
                'name' => $quizName,
                'topic' => $topic,
                'level' => $level
            ]);
            if (!$quiz) {
                $quiz = new Quiz();
                $quiz->setName($quizName);
                $quiz->setTopic($topic);
                $quiz->setLevel($level);
                $quiz->setActive(true);
                $this->em->persist($quiz);
                $stats['quizzes']++;
            }

            if ($replace) {
                $existingQuestions = $this->quizQuestionRepository->findBy(['quiz' => $quiz]);
                foreach ($existingQuestions as $eq) {
                    $this->em->remove($eq);
                }
                $this->em->flush();
            }

            foreach ($quizData['questions'] ?? [] as $qData) {
                $questionText = trim($qData['text'] ?? $qData['question'] ?? '');
                if (empty($questionText)) {
                    continue;
                }

                $currentQuestion = $this->quizQuestionRepository->findOneBy([
                    'text' => $questionText,
                    'quiz' => $quiz
                ]);

                if (null == $currentQuestion) {
                    $currentQuestion = new QuizQuestion();
                    $currentQuestion->setText($questionText);
                    $currentQuestion->setQuiz($quiz);
                    $currentQuestion->setActive(true);

                    $intro = trim($qData['intro'] ?? $qData['introduction'] ?? '');
                    if (!empty($intro)) {
                        $currentQuestion->setIntro($intro);
                    }

                    $this->em->persist($currentQuestion);
                    $stats['questions']++;
                }

                $answersData = $qData['answers'] ?? $qData['opciones'] ?? [];
                foreach ($answersData as $aData) {
                    $answerText = trim($aData['text'] ?? $aData['answer'] ?? $aData['opcion'] ?? '');
                    $isCorrect = (bool) ($aData['correct'] ?? $aData['correcta'] ?? $aData['isCorrect'] ?? false);
                    $explanation = isset($aData['explanation']) ? trim($aData['explanation']) : null;

                    if (empty($answerText)) {
                        continue;
                    }

                    $answer = null;
                    foreach ($currentQuestion->getAnswers() as $qa) {
                        if ($qa->getText() === $answerText) {
                            $answer = $qa;
                            break;
                        }
                    }

                    if (null == $answer) {
                        $answer = new QuizQuestionAnswer();
                        $answer->setText($answerText);
                        $answer->setQuizQuestion($currentQuestion);
                        $answer->setActive(true);
                        $stats['answers']++;
                    }
                    $answer->setValid($isCorrect);
                    $answer->setExplanation($explanation);
                    $this->em->persist($answer);
                }
            }
        }

        $this->em->flush();

        return $stats;
    }

    public function importCsv(string $content, string $delimiter = ';', bool $replace = false): array
    {
        $lines = array_filter(explode("\n", trim($content)));
        if (empty($lines)) {
            throw new \Exception('El archivo está vacío');
        }

        $header = str_getcsv(array_shift($lines), $delimiter);
        $header = array_map('trim', $header);

        $categoryIdx = array_search('category', $header);
        $topicIdx = array_search('topic', $header);
        $quizIdx = array_search('quiz', $header);
        $levelIdx = array_search('level', $header);
        $questionIdx = array_search('question', $header);
        $answerIdx = array_search('answer', $header);
        $correctIdx = array_search('correct', $header);
        $explanationIdx = array_search('explanation', $header);

        if (false === $questionIdx || false === $answerIdx) {
            throw new \Exception('Cabecera inválida. Se requieren: question, answer');
        }

        $stats = ['categories' => 0, 'topics' => 0, 'quizzes' => 0, 'questions' => 0, 'answers' => 0];

        $currentCategory = null;
        $currentTopic = null;
        $currentQuiz = null;
        $currentQuestion = null;

        foreach ($lines as $line) {
            $fields = str_getcsv($line, $delimiter);
            $fields = array_map('trim', $fields);

            $categoryName = $categoryIdx !== false ? ($fields[$categoryIdx] ?? '') : '';
            $topicName = $topicIdx !== false ? ($fields[$topicIdx] ?? '') : '';
            $quizName = $quizIdx !== false ? ($fields[$quizIdx] ?? '') : '';
            $levelNum = $levelIdx !== false ? (int) ($fields[$levelIdx] ?? 1) : 1;
            $questionText = $fields[$questionIdx] ?? '';
            $answerText = $fields[$answerIdx] ?? '';
            $isCorrect = $correctIdx !== false ? strtolower($fields[$correctIdx] ?? '') === 'true' : false;
            $explanation = $explanationIdx !== false ? ($fields[$explanationIdx] ?? null) : null;

            if (empty($questionText) || empty($answerText)) {
                continue;
            }

            if ($categoryName && (!$currentCategory || $currentCategory->getName() !== $categoryName)) {
                $currentCategory = $this->categoryRepository->findOneBy(['name' => $categoryName]);
                if (!$currentCategory) {
                    $currentCategory = new Category();
                    $currentCategory->setName($categoryName);
                    $this->em->persist($currentCategory);
                    $this->em->flush($currentCategory);
                    $stats['categories']++;
                }
            }

            if ($topicName && (!$currentTopic || $currentTopic->getName() !== $topicName)) {
                $currentTopic = $this->topicRepository->findOneBy([
                    'name' => $topicName,
                    'category' => $currentCategory
                ]);
                if (!$currentTopic) {
                    $currentTopic = new Topic();
                    $currentTopic->setName($topicName);
                    $currentTopic->setCategory($currentCategory);
                    $this->em->persist($currentTopic);
                    $stats['topics']++;
                }
            }

            if ($quizName && (!$currentQuiz || $currentQuiz->getName() !== $quizName)) {
                $level = $this->levelRepository->find($levelNum);
                $currentQuiz = $this->quizRepository->findOneBy([
                    'name' => $quizName,
                    'topic' => $currentTopic,
                    'level' => $level
                ]);
                if (!$currentQuiz) {
                    $currentQuiz = new Quiz();
                    $currentQuiz->setName($quizName);
                    $currentQuiz->setTopic($currentTopic);
                    $currentQuiz->setLevel($level);
                    $currentQuiz->setActive(true);
                    $this->em->persist($currentQuiz);
                    $stats['quizzes']++;
                }

                if ($replace) {
                    $existingQuestions = $this->quizQuestionRepository->findBy(['quiz' => $currentQuiz]);
                    foreach ($existingQuestions as $eq) {
                        $this->em->remove($eq);
                    }
                    $this->em->flush();
                    $currentQuestion = null;
                }
            }

            if (!$currentQuestion || $currentQuestion->getText() !== $questionText) {
                $currentQuestion = $this->quizQuestionRepository->findOneBy([
                    'text' => $questionText,
                    'quiz' => $currentQuiz
                ]);
                if (!$currentQuestion) {
                    $currentQuestion = new QuizQuestion();
                    $currentQuestion->setText($questionText);
                    $currentQuestion->setQuiz($currentQuiz);
                    $currentQuestion->setActive(true);
                    $this->em->persist($currentQuestion);
                    $stats['questions']++;
                }
            }

            $answer = null;
            foreach ($currentQuestion->getAnswers() as $qa) {
                if ($qa->getText() === $answerText) {
                    $answer = $qa;
                    break;
                }
            }

            if (!$answer) {
                $answer = new QuizQuestionAnswer();
                $answer->setText($answerText);
                $answer->setQuizQuestion($currentQuestion);
                $answer->setActive(true);
                $stats['answers']++;
            }
            $answer->setValid($isCorrect);
            $answer->setExplanation($explanation);
            $this->em->persist($answer);
        }

        $this->em->flush();

        return $stats;
    }
}