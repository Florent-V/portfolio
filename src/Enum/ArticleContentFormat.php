<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleContentFormat: string
{
    case HTML     = 'html';
    case MARKDOWN = 'markdown';
}
