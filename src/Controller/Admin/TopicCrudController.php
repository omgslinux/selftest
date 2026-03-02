<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
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
}
