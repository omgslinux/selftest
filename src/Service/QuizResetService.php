<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class QuizResetService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function checkAndReset(): bool
    {
        $resetFilePath = $_ENV['RESETFILE'] ?? null;

        if (!$resetFilePath) {
            return false;
        }

        $now = new \DateTime();

        if (!file_exists($resetFilePath)) {
            $nextWeek = $now->modify('+1 week');
            file_put_contents($resetFilePath, $nextWeek->format('Y-m-d H:i:s'));
            return false;
        }

        $resetTime = trim(file_get_contents($resetFilePath));
        $resetDate = new \DateTime($resetTime);

        if ($now >= $resetDate) {
            $this->performReset();
            $this->updateResetFile($resetFilePath);
            return true;
        }

        return false;
    }

    private function updateResetFile(string $resetFilePath): void
    {
        $nextWeek = (new \DateTime())->modify('+1 week');
        file_put_contents($resetFilePath, $nextWeek->format('Y-m-d H:i:s'));
    }

    private function performReset(): void
    {
        $allUsers = $this->em->getRepository(User::class)->findAll();

        $nonTeacherUsers = array_filter(
            $allUsers,
            function($user) {
                $roles = $user->getRoles();
                return !in_array('ROLE_TEACHER', $roles) && !in_array('ROLE_ADMIN', $roles);
            }
        );

        $quizTests = [];
        foreach ($nonTeacherUsers as $user) {
            foreach ($user->getQuizTests() as $quizTest) {
                $quizTests[] = $quizTest;
            }
        }

        foreach ($quizTests as $quizTest) {
            $answers = $quizTest->getQuizTestAnswers();
            if ($answers) {
                $this->em->remove($answers);
            }
            $this->em->remove($quizTest);
        }

        $this->em->flush();
    }
}
