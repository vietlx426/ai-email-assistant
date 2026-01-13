<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SummarizeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_thread' => ['required', 'string', 'min:50', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'email_thread.required' => 'Please provide the email thread to summarize.',
            'email_thread.min' => 'The email thread is too short to summarize effectively.',
        ];
    }
}