<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isCaissier());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'table_id' => 'nullable|exists:tables,id',
            'payment_method' => 'required|in:cash,carte,mixte',
            'amount_paid' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.method' => 'required_with:payments|in:cash,carte',
            'payments.*.amount' => 'required_with:payments|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Au moins un produit est requis.',
            'items.array' => 'Les produits doivent être une liste.',
            'items.min' => 'Au moins un produit est requis.',
            'items.*.produit_id.required' => 'Le produit est obligatoire.',
            'items.*.produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'items.*.quantity.required' => 'La quantité est obligatoire.',
            'items.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'items.*.price.required' => 'Le prix est obligatoire.',
            'items.*.price.numeric' => 'Le prix doit être un nombre.',
            'table_id.exists' => 'La table sélectionnée n\'existe pas.',
            'payment_method.required' => 'Le mode de paiement est obligatoire.',
            'payment_method.in' => 'Le mode de paiement doit être espèces, carte ou mixte.',
            'amount_paid.required' => 'Le montant payé est obligatoire.',
            'amount_paid.numeric' => 'Le montant payé doit être un nombre.',
            'amount_paid.min' => 'Le montant payé ne peut pas être négatif.',
        ];
    }
}
