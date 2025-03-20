<?php

namespace Viezel\FilamentTour\Tour;

use Filament\Facades\Filament;
use Viezel\FilamentTour\Tour\Traits\CanConstructRoute;

trait HasTour
{
    use CanConstructRoute;

    public function constructTours($class): array
    {
        $prefixId = config('filament-tour.tour_prefix_id');
        $tours = [];

        foreach ($this->tours() as $tour) {
            if ($tour instanceof Tour) {
                if ($tour->getRoute() && Filament::auth()->user()) {
                    $this->setRoute($tour->getRoute());
                }

                $steps = json_encode(collect($tour->getSteps())->mapWithKeys(function (Step $step, $item) use ($tour) {
                    $data[$item] = [
                        'uncloseable' => $step->isUncloseable(),
                        'popover' => [
                            'title' => view('filament-tour::tour.step.popover.title')
                                ->with('title', $step->getTitle())
                                ->with('icon', $step->getIcon())
                                ->with('iconColor', $step->getIconColor())
                                ->render(),
                            'description' => $step->getDescription(),
                        ],
                        'progress' => [
                            'current' => $item,
                            'total' => count($tour->getSteps()),
                        ],
                    ];

                    if (! $tour->hasDisabledEvents()) {
                        $data[$item]['events'] = [
                            'redirectOnNext' => $step->getRedirectOnNext(),
                            'clickOnNext' => $step->getClickOnNext(),
                            'notifyOnNext' => $step->getNotifyOnNext(),
                            'dispatchOnNext' => $step->getDispatchOnNext(),
                        ];
                    }

                    if ($step->getElement()) {
                        $data[$item]['element'] = $step->getElement();
                    }

                    return $data;
                })->toArray());

                if ($steps) {
                    $route = $this->getRoute($class);
                    $tours[] = [
                        'id' => "{$prefixId}{$tour->getId()}",
                        'routesIgnored' => $tour->isRoutesIgnored(),
                        'uncloseable' => $tour->isUncloseable(),
                        'dispatchOnComplete' => $tour->getDispatchOnComplete(),
                        'dispatchOnDismiss' => $tour->getDispatchOnDismiss(),
                        'route' => $route,
                        'routeName' => $tour->getRouteName(),
                        'alwaysShow' => $tour->isAlwaysShow(),
                        'showProgress' => $tour->getShowProgress(),
                        'progressText' => $tour->getProgressText(),
                        'popoverClass' => $tour->getPopoverClass(),
                        'shouldCompleteOnDismiss' => $tour->getShouldCompleteOnDismiss(),
                        'colors' => [
                            'light' => $tour->getColors()['light'],
                            'dark' => $tour->getColors()['dark'],
                        ],
                        'nextButtonLabel' => $tour->getNextButtonLabel(),
                        'previousButtonLabel' => $tour->getPreviousButtonLabel(),
                        'doneButtonLabel' => $tour->getDoneButtonLabel(),
                        'steps' => $steps,
                    ];
                }
            }
        }

        return $tours;
    }

    /**
     * Define your tours here.
     */
    abstract public function tours(): array;
}
