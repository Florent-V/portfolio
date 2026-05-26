<?php

namespace App\Enum;

enum ArticleContentType: string
{
    case PARAGRAPH = 'paragraph';
    case IMAGE = 'image';
    case CODE = 'code';
}
