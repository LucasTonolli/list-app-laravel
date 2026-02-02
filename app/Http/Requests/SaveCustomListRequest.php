<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:40'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Título é obrigatório',
            'title.string' => 'Título deve ser uma string',
            'title.max' => 'Título deve ter no máximo 40 caracteres',
        ];
    }
}
