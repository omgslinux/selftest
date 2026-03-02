<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quiz::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cuestionario')
            ->setEntityLabelInPlural('Cuestionarios');
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): \Symfony\Component\Form\FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        
        $formBuilder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $form->add('topic', EntityType::class, [
                    'class' => Topic::class,
                    'choice_label' => 'name',
                    'placeholder' => 'Selecciona un tema',
                    'label' => 'Tema',
                    'group_by' => function($topic) {
                        return $topic->getCategory()?->getName();
                    },
                ]);
            }
        );

        return $formBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('topic', 'Tema')
                ->setCrudController(false)
                ->autocomplete(),
            AssociationField::new('level', 'Nivel')->setCrudController(false),
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nombre'),
            BooleanField::new('active', 'Activo'),
            DateTimeField::new('createdAt', 'Creado')->onlyOnIndex(),
            DateTimeField::new('updatedAt', 'Actualizado')->onlyOnIndex(),
        ];
    }

    public function createEntity(string $entityFqcn): object
    {
        $quiz = new Quiz();
        $quiz->setActive(true);
        return $quiz;
    }

    #[Route('/api/quiz/topics', name: 'quiz_topics')]
    public function getTopicsByCategory(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $categoryId = $request->query->get('category');
        
        if (!$categoryId) {
            $topics = $em->getRepository(Topic::class)->findAll();
        } else {
            $topics = $em->getRepository(Topic::class)->findBy(
                ['category' => $categoryId],
                ['name' => 'ASC']
            );
        }

        return new JsonResponse(
            array_map(fn($topic) => ['id' => $topic->getId(), 'name' => (string) $topic], $topics)
        );
    }
}
