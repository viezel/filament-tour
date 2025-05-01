<?php

namespace Viezel\FilamentTour;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Blade;
use Viezel\FilamentTour\Tour\Enums\TourHistoryType;

class FilamentTourPlugin implements Plugin
{
    use EvaluatesClosures;

    private ?bool $onlyVisibleOnce = null;

    private ?bool $enableCssSelector = null;

    private TourHistoryType $historyType = TourHistoryType::LocalStorage;

    private bool $autoStart = true;

    public static function make(): static
    {
        return app(static::class)->autoStart(config('filament-tour.tour_auto_start', true));
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-tour';
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook('panels::body.start', fn () => Blade::render('<livewire:filament-tour-widget/>'));
    }

    public function boot(Panel $panel): void {}

    public function onlyVisibleOnce(bool $onlyVisibleOnce = true): self
    {
        $this->onlyVisibleOnce = $onlyVisibleOnce;

        return $this;
    }

    public function isOnlyVisibleOnce(): ?bool
    {
        return $this->onlyVisibleOnce;
    }

    // Generate documentation
    public function enableCssSelector(bool|Closure $enableCssSelector = true): self
    {
        if (is_callable($enableCssSelector)) {
            $this->enableCssSelector = $enableCssSelector();
        } elseif (is_bool($enableCssSelector)) {
            $this->enableCssSelector = $enableCssSelector;
        }

        return $this;
    }

    public function isCssSelectorEnabled(): ?bool
    {
        return $this->enableCssSelector;
    }

    public function historyType(TourHistoryType $type): self
    {
        $this->historyType = $type;

        return $this;
    }

    public function getHistoryType(): TourHistoryType
    {
        return $this->historyType;
    }

    public function autoStart(bool|Closure $autoStart = true): self
    {
        if (is_callable($autoStart)) {
            $this->autoStart = (bool)$autoStart();
        } elseif (is_bool($autoStart)) {
            $this->autoStart = $autoStart;
        }

        return $this;
    }

    public function getAutoStart(): bool
    {
        return $this->autoStart;
    }
}
