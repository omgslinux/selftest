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
    name: 'app:import-questions',
    description: 'Importa preguntas desde archivos CSV',
)]
class ImportQuestionsCommand extends Command
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
            ->addArgument('file', InputArgument::REQUIRED, 'Ruta al archivo CSV o directorio')
            ->addArgument('delimiter', InputArgument::OPTIONAL, 'Delimitador del CSV', ';')
            ->addOption('replace', 'r', null, 'Reemplazar preguntas existentes del quiz')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('file');
        $delimiter = $input->getArgument('delimiter');
        $replace = $input->getOption('replace');

        $files = [];
        if (is_dir($path)) {
            $files = glob($path . '/*.csv');
            if (empty($files)) {
                $io->error('No se encontraron archivos CSV en el directorio');
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
            $filename = basename($filePath, '.csv');

            // Nuevo Regex: Categoría_Tema_Quiz_LNivel
            if (!preg_match('/^(.+?)_(.+?)_(.+?)_L([1-3])$/', $filename, $matches)) {
                $io->warning('Archivo ignorado: ' . $filename . '. Usar: Categoria_Tema_Quiz_L[1-3].csv');
                continue;
            }

            [, $categoryName, $topicName, $quizName, $levelNum] = $matches;

            // Limpiamos los nombres (reemplazando guiones o guiones bajos si lo prefieres)
            $categoryName = trim(str_replace(['_', '-'], ' ', $categoryName));
            $topicName = trim(str_replace(['_', '-'], ' ', $topicName));
            $quizName = trim(str_replace('_', ' ', $quizName));

            // En lugar de un simple replace:
            // 1. Sustituimos '--' por un marcador temporal (ej: '###')
            // 2. Sustituimos '-' por un espacio
            // 3. Sustituimos el marcador '###' por un '-' real

            $quizName = str_replace('--', '###', $quizName);
            $quizName = str_replace('-', ' ', $quizName);
            $quizName = str_replace('###', '-', $quizName);
            $quizName = trim($quizName);

            $io->info('Procesando: ' . $filename);

            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                $io->error('No se pudo abrir: ' . $filePath);
                continue;
            }

            $headers = fgetcsv($handle, 0, $delimiter);

            $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
            if (!$category) {
                $category = new Category();
                $category->setName($categoryName);
                $this->em->persist($category);
                $totalCategories++;
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

            $questionsMap = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $data = array_combine($headers, $row);

                $questionText = trim($data['question'] ?? '');
                $answerText = trim($data['answer'] ?? '');
                $isCorrect = strtolower(trim($data['correct'] ?? '')) === 'true';

                if (empty($questionText)) {
                    continue;
                }

                if (!isset($questionsMap[$questionText])) {
                    $currentQuestion = new QuizQuestion();
                    $currentQuestion->setText($questionText);
                    $currentQuestion->setQuiz($quiz);
                    $currentQuestion->setActive(true);

                    $this->em->persist($currentQuestion);
                    $questionsMap[$questionText] = $currentQuestion;
                    $totalQuestions++;
                } else {
                    $currentQuestion = $questionsMap[$questionText];
                }

                $answer = new QuizQuestionAnswer();
                $answer->setText($answerText);
                $answer->setValid($isCorrect);
                $answer->setQuizQuestion($currentQuestion);
                $answer->setActive(true);

                $this->em->persist($answer);
                $totalAnswers++;
            }

            fclose($handle);
        }

        $this->em->flush();

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
