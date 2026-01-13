<?php

namespace App\Enums;

enum TemplateType: string
{
    case MEETING_REQUEST = 'meeting_request';
    case FOLLOW_UP = 'follow_up';
    case INTRODUCTION = 'introduction';
    case THANK_YOU = 'thank_you';
    case APOLOGY = 'apology';
    case FEEDBACK = 'feedback';
    case PROPOSAL = 'proposal';

    public function getDescription(): string
    {
        return match($this) {
            self::MEETING_REQUEST => 'professional meeting request',
            self::FOLLOW_UP => 'follow-up email after a meeting or conversation',
            self::INTRODUCTION => 'professional self-introduction or introduction of others',
            self::THANK_YOU => 'thank you email',
            self::APOLOGY => 'professional apology email',
            self::FEEDBACK => 'constructive feedback email',
            self::PROPOSAL => 'business proposal email',
        };
    }
}