<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'vente_id' => 'required|exists:ventes,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,carte,mixte',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vente_id.required' => 'La vente est obligatoire.',
            'vente_id.exists' => 'La vente sélectionnée n\'existe pas.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être supérieur à 0.',
            'method.required' => 'Le mode de paiement est obligatoire.',
            'method.in' => 'Le mode de paiement doit être espèces, carte ou mixte.',
        ];
    }
}
