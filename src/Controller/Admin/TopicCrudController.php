<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TopicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Topic::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tema')
            ->setEntityLabelInPlural('Temas');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('category', 'Categoría')->setCrudController(false),
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nombre'),
            TextField::new('description', 'Descripción'),
        ];
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Topic) {
            return;
        }

        foreach ($entityInstance->getQuizzes() as $quiz) {
            foreach ($quiz->getQuestions() as $question) {
                foreach ($question->getAnswers() as $answer) {
                    $entityManager->remove($answer);
                }
                $entityManager->remove($question);
            }
            $entityManager->remove($quiz);
        }

        $entityManager->remove($entityInstance);
        $entityManager->flush();
    }
}
