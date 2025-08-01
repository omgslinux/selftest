<?php
// src/Components/BootstrapModalComponent.php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent]
class BootstrapModalComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public bool $isOpen = false;

    #[LiveProp]
    public string $modalId;

    #[LiveProp]
    public string $title = '';

    #[LiveProp]
    public string $size = 'lg'; // sm, md, lg, xl

    #[LiveProp]
    public bool $staticBackdrop = false;

    #[LiveProp]
    public bool $scrollable = false;

    #[LiveProp]
    public string $submitText = 'Guardar';

    #[LiveProp]
    public string $closeText = 'Cerrar';

    #[LiveAction]
    public function open(): void
    {
        $this->isOpen = true;
    }

    #[LiveAction]
    public function close(): void
    {
        $this->isOpen = false;
    }

    #[LiveAction]
    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }
}
