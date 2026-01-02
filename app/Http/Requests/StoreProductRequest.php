<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:produits,name',
            'price_vente' => 'required|numeric|min:0',
            'price_achat' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_stock' => 'required|integer|min:1',
            'unit' => 'required|in:pcs,kg,l',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'name.unique' => 'Ce produit existe déjà.',
            'price_vente.required' => 'Le prix de vente est obligatoire.',
            'price_vente.numeric' => 'Le prix de vente doit être un nombre.',
            'price_vente.min' => 'Le prix de vente ne peut pas être négatif.',
            'price_achat.numeric' => 'Le prix d\'achat doit être un nombre.',
            'price_achat.min' => 'Le prix d\'achat ne peut pas être négatif.',
            'stock_quantity.required' => 'La quantité en stock est obligatoire.',
            'stock_quantity.integer' => 'La quantité doit être un nombre entier.',
            'stock_quantity.min' => 'La quantité ne peut pas être négative.',
            'alert_stock.required' => 'Le seuil d\'alerte est obligatoire.',
            'alert_stock.integer' => 'Le seuil d\'alerte doit être un nombre entier.',
            'alert_stock.min' => 'Le seuil d\'alerte doit être au moins 1.',
            'unit.required' => 'L\'unité est obligatoire.',
            'unit.in' => 'L\'unité doit être pcs, kg ou l.',
            'status.in' => 'Le statut doit être actif ou inactif.',
        ];
    }
}
