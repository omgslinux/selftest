<?php

namespace App\Controller\Admin;

use App\Entity\QuizQuestion;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class QuizQuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizQuestion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pregunta')
            ->setEntityLabelInPlural('Preguntas');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('text', 'Pregunta');
        
        yield AssociationField::new('quiz', 'Quiz')
            ->setCrudController(false);
        
        yield BooleanField::new('active', 'Activo');
        
        yield CollectionField::new('answers', 'Respuestas')
            ->setEntryType(\App\Form\QuizQuestionAnswerType::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setFormTypeOption('by_reference', false);
    }
}
