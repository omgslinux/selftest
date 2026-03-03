<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Usuario')
            ->setEntityLabelInPlural('Usuarios');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('username', 'Usuario'),
            TextField::new('name', 'Nombre'),
        ];

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            $fields[] = TextField::new('password', 'Contraseña')
                ->setFormType(PasswordType::class)
                ->setRequired(true);
        }

        $fields[] = ChoiceField::new('role', 'Rol')
            ->setChoices([
                'Admin' => 'Admin',
                'Profesor' => 'Teacher',
                'Usuario' => 'User',
            ])
            ->renderAsBadges();

        $fields[] = BooleanField::new('active', 'Activo');
        $fields[] = DateTimeField::new('createdAt', 'Creado')->onlyOnIndex();
        $fields[] = DateTimeField::new('updatedAt', 'Actualizado')->onlyOnIndex();

        return $fields;
    }

    public function createEntity(string $entityFqcn): object
    {
        $user = new User();
        $user->setActive(true);
        return $user;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function encodePassword(User $user): void
    {
        $request = $this->getContext()->getRequest();
        $password = $request->request->all('User')['password'] ?? null;

        if ($password && is_string($password) && !empty($password)) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }
    }
}
