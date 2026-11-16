<?php

namespace App\Modules\Metrics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['question' => ['required', 'string', 'max:500']];
    }

    public function messages(): array
    {
        return ['question.required' => 'La pregunta es obligatoria.', 'question.max' => 'La pregunta no puede superar 500 caracteres.'];
    }
}
