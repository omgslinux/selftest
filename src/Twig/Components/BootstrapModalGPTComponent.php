<?php

namespace App\Twig\Components;

use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

#[AsLiveComponent]
class BootstrapModalGPTComponent
{
    use DefaultActionTrait;
    //use ComponentWithFormTrait;

    //public ?FormInterface $form = null;
    public string $modalId = 'modal';
    public string $title = '';
    public string $actionLabel = 'Guardar';

}
