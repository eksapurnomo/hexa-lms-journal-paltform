<?php

namespace App\Enum;

enum QuestionTypeEnum: string
{
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case BINARY = 'binary';
}
