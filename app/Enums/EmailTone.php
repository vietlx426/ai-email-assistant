<?php

namespace App\Enums;

enum EmailTone: string
{
    case PROFESSIONAL = 'professional';
    case FRIENDLY = 'friendly';
    case CASUAL = 'casual';
    case FORMAL = 'formal';
    case CONCISE = 'concise';
    case URGENT = 'urgent';

    public function getTemperature(): float
    {
        return match($this) {
            self::PROFESSIONAL => 0.7,
            self::FRIENDLY => 0.8,
            self::CASUAL => 0.9,
            self::FORMAL => 0.5,
            self::CONCISE => 0.6,
            self::URGENT => 0.75,
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PROFESSIONAL => 'professional and formal',
            self::FRIENDLY => 'friendly and approachable while maintaining professionalism',
            self::CASUAL => 'casual and conversational',
            self::FORMAL => 'highly formal and corporate',
            self::CONCISE => 'brief and to-the-point',
            self::URGENT => 'urgent and direct with clear action items',
        };
    }
}