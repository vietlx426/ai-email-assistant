<?php

namespace App\Http\Requests;

use App\Enums\EmailTone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class DraftEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'tone' => ['sometimes', new Enum(EmailTone::class)],
            'context' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Please provide a description of the email you want to write.',
            'description.min' => 'The description should be at least 10 characters long.',
            'tone.enum' => 'Invalid tone selected. Please choose from: professional, friendly, formal, casual, urgent, or apologetic.',
        ];
    }
}
