<?php

declare(strict_types=1);

namespace App\Enums;

enum LogbookBookType: string
{
    case G01Releasing = 'g01_releasing';
    case O02Occupancy = 'o02_occupancy';
    case ESeries = 'e_series';
    case G05 = 'g05';
    case G06 = 'g06';
}
