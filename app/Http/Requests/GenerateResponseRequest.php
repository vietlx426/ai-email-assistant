<?php

namespace App\Http\Requests;

use App\Enums\EmailTone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GenerateResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'original_email' => ['required', 'string', 'min:20', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'tone' => ['sometimes', new Enum(EmailTone::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'original_email.required' => 'Please provide the original email to respond to.',
            'original_email.min' => 'The original email seems too short. Please provide more content.',
        ];
    }
}
