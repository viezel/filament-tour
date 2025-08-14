<?php

namespace Viezel\FilamentTour\Livewire;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Viezel\FilamentTour\FilamentTourPlugin;
use Viezel\FilamentTour\Highlight\HasHighlight;
use Viezel\FilamentTour\Tour\Enums\TourHistoryType;
use Viezel\FilamentTour\Tour\HasTour;
use Viezel\FilamentTour\Tour\Models\TourHistory;

class FilamentTourWidget extends Component
{
    public array $tours = [];

    public bool $autoStartTours = true;

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

        $filamentTourPlugin = FilamentTourPlugin::get();
        $historyType = $filamentTourPlugin->getHistoryType();

        // default to no history, if using database option and guest users
        if ($historyType === TourHistoryType::Database && auth()->guest()) {
            $historyType = TourHistoryType::None;
        }

        $completedTours = [];
        if ($historyType === TourHistoryType::Database) {
            $completedTours = TourHistory::getCompletedTours();
        }

        $this->dispatch('filament-tour::loaded-elements',
            history_type: $historyType->value,
            completed_tours: $completedTours,
            prefix: config('filament-tour.tour_prefix_id'),
            only_visible_once: $historyType !== TourHistoryType::None && (is_bool($filamentTourPlugin->isOnlyVisibleOnce()) ? $filamentTourPlugin->isOnlyVisibleOnce() : config('filament-tour.only_visible_once')),
            tours: $this->tours,
            highlights: $this->highlights,
            current_route_name: $this->getCurrentRouteName(),
            auto_start_tours: $filamentTourPlugin->getAutoStart(),
        );

        if (config('app.env') != 'production') {
            $hasCssSelector = is_bool($filamentTourPlugin->isCssSelectorEnabled()) ? $filamentTourPlugin->isCssSelectorEnabled() : config('filament-tour.enable_css_selector');
            $this->dispatch('filament-tour::change-css-selector-status', enabled: $hasCssSelector);
        }
    }

    #[On('filament-tour::tour-dismissed')]
    public function tourDismissed(string $id): void
    {
        $tourId = Str::after($id, config('filament-tour.tour_prefix_id'));
        TourHistory::markAsDismissed($tourId);
    }

    #[On('filament-tour::tour-completed')]
    public function tourCompleted(string $id): void
    {
        $tourId = Str::after($id, config('filament-tour.tour_prefix_id'));
        TourHistory::markAsCompleted($tourId);
    }

    public function render()
    {
        return view('filament-tour::livewire.filament-tour-widget');
    }

    private function getCurrentRouteName(): ?string
    {
        if (request()->route()->named('livewire.update')) {
            $previousUrl = URL::previous();
            $previousRoute = app('router')->getRoutes()->match(request()->create($previousUrl));

            return $previousRoute->getName();
        }

        return request()->route()->getName();
    }
}
