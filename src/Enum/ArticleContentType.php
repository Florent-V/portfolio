<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleContentType: string
{
    case PARAGRAPH = 'paragraph';
    case IMAGE     = 'image';
    case CODE      = 'code';
}
