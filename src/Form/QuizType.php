<?php

namespace App\Form;

use App\Entity\Topic;
use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new \Symfonycasts\DynamicForms\DynamicFormBuilder($builder);

        $builder
            ->add('topic', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                'class' => Topic::class,
                'choice_label' => 'name',
                'placeholder' => 'Selecciona un tema',
                'label' => 'Tema',
                'group_by' => function($topic) {
                    return $topic->getCategory()?->getName();
                },
                'autocomplete' => true,
            ])
            ->addDependent('level', 'topic', function (DependentField $field, ?Topic $topic) {
                $field->add(\Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                    'class' => \App\Entity\Level::class,
                    'choice_label' => 'name',
                    'placeholder' => $topic ? 'Selecciona un nivel' : 'Selecciona un tema primero',
                    'label' => 'Nivel',
                    'required' => false,
                    'disabled' => null === $topic,
                ]);
            })
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add('active', CheckboxType::class, ['label' => 'Activo', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
