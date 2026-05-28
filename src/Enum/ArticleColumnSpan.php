<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleColumnSpan: string
{
    case FULL       = 'full';
    case ONE_THIRD  = 'one_third';
    case TWO_THIRDS = 'two_thirds';
}
