<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('usuarios', 'correo')->ignore($this->user()->id),
            ],
            'pais' => ['nullable', 'string', 'max:100'],
            'ci' => ['nullable', 'string', 'max:50'], // Más flexible para documentos internacionales
            'telefono' => ['nullable', 'string', 'max:20'],
            'codigo_pais_telefono' => ['nullable', 'string', 'max:10'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB máximo
        ];
    }
}
