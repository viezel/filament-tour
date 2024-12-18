<?php

namespace Viezel\FilamentTour\Tour\Traits;

trait HasTourEvent
{
    private ?array $dispatchOnComplete = null;
    private ?array $dispatchOnDismiss = null;

    public function dispatchOnComplete(string $name, ...$params): self
    {
        $this->dispatchOnComplete = ['name' => $name, 'params' => $params];

        return $this;
    }

    public function getDispatchOnComplete(): ?array
    {
        return $this->dispatchOnComplete;
    }

    public function dispatchOnDismiss(string $name, ...$params): self
    {
        $this->dispatchOnDismiss = ['name' => $name, 'params' => $params];

        return $this;
    }

    public function getDispatchOnDismiss(): ?array
    {
        return $this->dispatchOnDismiss;
    }

}
