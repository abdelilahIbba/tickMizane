<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommandeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'fournisseur_id.required' => 'Le fournisseur est obligatoire.',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné n\'existe pas.',
            'items.required' => 'Au moins un produit est requis.',
            'items.array' => 'Les produits doivent être une liste.',
            'items.min' => 'Au moins un produit est requis.',
            'items.*.produit_id.required' => 'Le produit est obligatoire.',
            'items.*.produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'items.*.price.numeric' => 'Le prix doit être un nombre.',
            'items.*.price.min' => 'Le prix ne peut pas être négatif.',
        ];
    }
}
