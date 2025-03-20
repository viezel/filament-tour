<?php

namespace Viezel\FilamentTour\Tour\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Viezel\FilamentTour\Database\Factories\TourHistoryFactory;
use Viezel\FilamentTour\Tour\Enums\TourHistoryStatus;

class TourHistory extends Model
{
    use HasFactory;

    protected $table = 'tour_history';

    protected $fillable = [
        'user_id',
        'tour_id',
        'status',
    ];

    protected $casts = [
        'status' => TourHistoryStatus::class,
    ];

    protected static function newFactory(): Factory
    {
        return TourHistoryFactory::new();
    }

    public static function hasCompletedTour(string $tourId): bool
    {
        return self::query()
            ->where('user_id', '=', Filament::auth()->id())
            ->where('tour_id', '=', $tourId)
            ->exists();
    }

    public static function getCompletedTours(): array
    {
        return self::query()
            ->select('tour_id')
            ->where('user_id', '=', Filament::auth()->id())
            ->where('status', '=', TourHistoryStatus::Completed->value)
            ->get()
            ->pluck('tour_id')
            ->toArray();
    }

    public static function markAsCompleted(string $tourId): void
    {
        self::updateOrCreate([
            'user_id' => Filament::auth()->id(),
            'tour_id' => $tourId,
        ], [
            'status' => TourHistoryStatus::Completed,
        ]);
    }

    public static function markAsDismissed(string $tourId): void
    {
        self::updateOrCreate([
            'user_id' => Filament::auth()->id(),
            'tour_id' => $tourId,
        ], [
            'status' => TourHistoryStatus::Dismissed,
        ]);
    }
}
