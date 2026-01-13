<?php
namespace App\Enums;

enum EmailOperation: string
{
    case DRAFT = 'draft';
    case RESPONSE = 'response';
    case ANALYZE = 'analyze';
    case SUMMARIZE = 'summarize';
    case TEMPLATE = 'template';
}
