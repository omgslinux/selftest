<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ActivableEntityTrait
{

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $active=false;

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
