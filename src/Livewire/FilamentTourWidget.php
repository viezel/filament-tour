<?php

namespace Viezel\FilamentTour\Livewire;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Livewire\Attributes\On;
use Livewire\Component;
use Viezel\FilamentTour\FilamentTourPlugin;
use Viezel\FilamentTour\Highlight\HasHighlight;
use Viezel\FilamentTour\Tour\HasTour;

class FilamentTourWidget extends Component
{
    public array $tours = [];

    public array $highlights = [];

    #[On('filament-tour::load-elements')]
    public function load(): void
    {
        $classesUsingHasTour = [];
        $classesUsingHasHighlight = [];
        $filamentClasses = [];

        foreach (array_merge(Filament::getResources(), Filament::getPages()) as $class) {
            $instance = new $class;

            if ($instance instanceof Resource) {
                collect($instance->getPages())->map(fn ($item) => $item->getPage())
                    ->flatten()
                    ->each(function ($item) use (&$filamentClasses) {
                        $filamentClasses[] = $item;
                    });
            } else {
                $filamentClasses[] = $class;
            }

        }

        foreach ($filamentClasses as $class) {
            $traits = class_uses($class);

            if (in_array(HasTour::class, $traits)) {
                $classesUsingHasTour[] = $class;
            }

            if (in_array(HasHighlight::class, $traits)) {
                $classesUsingHasHighlight[] = $class;
            }
        }

        foreach ($classesUsingHasTour as $class) {
            $this->tours = array_merge($this->tours, (new $class)->constructTours($class));
        }

        foreach ($classesUsingHasHighlight as $class) {
            $this->highlights = array_merge($this->highlights, (new $class)->constructHighlights($class));
        }

        $this->dispatch('filament-tour::loaded-elements',
            only_visible_once: FilamentTourPlugin::get()->getHistoryType() == 'local_storage' && (is_bool(FilamentTourPlugin::get()->isOnlyVisibleOnce()) ? FilamentTourPlugin::get()->isOnlyVisibleOnce() : config('filament-tour.only_visible_once')),
            tours: $this->tours,
            highlights: $this->highlights,
        );

        if (config('app.env') != 'production') {
            $hasCssSelector = is_bool(FilamentTourPlugin::get()->isCssSelectorEnabled()) ? FilamentTourPlugin::get()->isCssSelectorEnabled() : config('filament-tour.enable_css_selector');
            $this->dispatch('filament-tour::change-css-selector-status', enabled: $hasCssSelector);
        }
    }

    public function render()
    {
        return view('filament-tour::livewire.filament-tour-widget');
    }
}
