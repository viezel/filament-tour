<?php

namespace Viezel\FilamentTour\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Viezel\FilamentTour\Tour\Enums\TourHistoryStatus;
use Viezel\FilamentTour\Tour\Models\TourHistory;

class TourHistoryFactory extends Factory
{
    protected $model = TourHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => fake()->randomNumber(),
            'tour_id' => fake()->unique()->text(10),
            'status' => fake()->randomElement(TourHistoryStatus::cases()),
        ];
    }
}
