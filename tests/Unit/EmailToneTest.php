<?php

namespace Tests\Unit;

use App\Enums\EmailTone;
use Tests\TestCase;

class EmailToneTest extends TestCase
{
    public function test_email_tone_temperatures()
    {
        $this->assertEquals(0.7, EmailTone::PROFESSIONAL->getTemperature());
        $this->assertEquals(0.8, EmailTone::FRIENDLY->getTemperature());
        $this->assertEquals(0.9, EmailTone::CASUAL->getTemperature());
        $this->assertEquals(0.5, EmailTone::FORMAL->getTemperature());
        $this->assertEquals(0.6, EmailTone::CONCISE->getTemperature());
    }

    public function test_email_tone_descriptions()
    {
        $this->assertStringContainsString(
            'professional',
            EmailTone::PROFESSIONAL->getDescription()
        );
    }
}