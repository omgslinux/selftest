<?php

namespace App\Command;

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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-json',
    description: 'Importa preguntas desde archivos JSON',
)]
class ImportJsonQuestionsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private TopicRepository $topicRepository,
        private LevelRepository $levelRepository,
        private QuizRepository $quizRepository,
        private QuizQuestionRepository $quizQuestionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Ruta al archivo JSON o directorio')
            ->addOption('replace', 'r', null, 'Reemplazar preguntas existentes del quiz');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('file');
        $replace = $input->getOption('replace');

        $files = [];
        if (is_dir($path)) {
            $files = glob($path . '/*.json');
            if (empty($files)) {
                $io->error('No se encontraron archivos JSON en el directorio');
                return Command::FAILURE;
            }
        } elseif (file_exists($path)) {
            $files = [$path];
        } else {
            $io->error('El archivo o directorio no existe: ' . $path);
            return Command::FAILURE;
        }

        $totalCategories = 0;
        $totalTopics = 0;
        $totalQuizzes = 0;
        $totalQuestions = 0;
        $totalAnswers = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $io->info('Procesando: ' . $filename);

            $json = json_decode(file_get_contents($filePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $io->error('Error al parsear JSON: ' . json_last_error_msg());
                continue;
            }

            $categoryName = trim($json['category'] ?? '');
            if (empty($categoryName)) {
                $io->error('Categoría no definida en el JSON');
                continue;
            }

            $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
            if (!$category) {
                $category = new Category();
                $category->setName($categoryName);
                $this->em->persist($category);
                $this->em->flush($category);
                $totalCategories++;
            }

            $topicName = trim($json['name'] ?? '');
            if (empty($topicName)) {
                $io->error('Topic name no definido en el JSON');
                continue;
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
                $totalTopics++;
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
                    $io->warning('Level no encontrado: ' . $levelNum);
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
                    $totalQuizzes++;
                }

                if ($replace) {
                    $existingQuestions = $this->quizQuestionRepository->findBy(['quiz' => $quiz]);
                    foreach ($existingQuestions as $eq) {
                        $this->em->remove($eq);
                    }
                    $this->em->flush();
                    $io->info('Preguntas anteriores eliminadas del quiz: ' . $quizName);
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
                        $totalQuestions++;
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
                            $totalAnswers++;
                        }
                        $answer->setValid($isCorrect);
                        $answer->setExplanation($explanation);
                        $this->em->persist($answer);
                    }
                }
            }

            $this->em->flush();
        }

        $io->success(sprintf(
            'Importación completada: %d categorías, %d temas, %d quizzes, %d preguntas y %d respuestas',
            $totalCategories,
            $totalTopics,
            $totalQuizzes,
            $totalQuestions,
            $totalAnswers
        ));

        return Command::SUCCESS;
    }
}