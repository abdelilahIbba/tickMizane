<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isServeur());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tables', 'name')->ignore($this->route('table')),
            ],
            'status' => 'nullable|in:free,occupied',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la table est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 50 caractères.',
            'name.unique' => 'Cette table existe déjà.',
            'status.in' => 'Le statut doit être libre ou occupée.',
        ];
    }
}
