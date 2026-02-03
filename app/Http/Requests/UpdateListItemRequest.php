<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'version' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome é obrigatório',
            'name.string' => 'Nome deve ser uma string',
            'name.max' => 'Nome deve ter no máximo 100 caracteres',
            'description.string' => 'Descrição deve ser uma string',
            'description.max' => 'Descrição deve ter no máximo 1000 caracteres',
            'version.required' => 'Versão é obrigatório',
            'version.integer' => 'Versão deve ser um inteiro',
        ];
    }
}
