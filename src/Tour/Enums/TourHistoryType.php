<?php

namespace Viezel\FilamentTour\Tour\Enums;

enum TourHistoryType: string
{
    case None = 'none';
    case LocalStorage = 'local_storage';
    case Database = 'database';
}
