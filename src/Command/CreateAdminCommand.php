<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crea o actualiza un usuario administrador',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Nombre de usuario (default: admin)', 'admin')
            ->addArgument('password', InputArgument::OPTIONAL, 'Contraseña (si no se especifica, se generará una aleatoria)')
            ->addArgument('name', InputArgument::OPTIONAL, 'Nombre (default: Administrador)', 'Administrador')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');

        if (!$password) {
            $password = $this->generateRandomPassword();
            $io->info("Se ha generado una contraseña aleatoria");
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($user) {
            $io->info("El usuario '$username' ya existe. Actualizando...");
        } else {
            $user = new User();
            $user->setUsername($username);
            $user->setName($name);
            $user->setRole('Admin');
            $user->setActive(true);
            $io->info("Creando nuevo usuario '$username'...");
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $io->success("Usuario '$username' guardado correctamente");
        $io->warning("Contraseña: $password");

        return Command::SUCCESS;
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }

        return $password;
    }
}
