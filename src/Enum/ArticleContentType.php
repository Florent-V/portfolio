<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleContentType: string
{
    case SUMMARY   = 'summary';
    case PARAGRAPH = 'paragraph';
    case IMAGE     = 'image';
    case CODE      = 'code';
}
