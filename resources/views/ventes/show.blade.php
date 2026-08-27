<x-layout.app title="Détails de la vente">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('ventes.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux ventes
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Vente #{{ str_pad($vente->id, 6, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-400 mt-1">{{ $vente->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($vente->status === 'paid')
                        <x-ui.badge variant="success">Payée</x-ui.badge>
                    @elseif($vente->status === 'pending')
                        <x-ui.badge variant="warning">En attente</x-ui.badge>
                    @else
                        <x-ui.badge variant="danger">Annulée</x-ui.badge>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sale Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Items --}}
                <x-ui.card title="Articles ({{ $vente->details->count() }})" :padding="false">
                    <x-ui.table :headers="['Produit', 'Prix unitaire', 'Quantité', 'Total']">
                        @forelse($vente->details as $detail)
                            <tr class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-white">{{ $detail->produit->name ?? 'Produit supprimé' }}</td>
                                <td class="px-6 py-4 text-gray-300">{{ number_format($detail->price, 2) }} DH</td>
                                <td class="px-6 py-4 text-gray-300">{{ $detail->quantity }}</td>
                                <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($detail->total_line, 2) }} DH</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    Aucun article dans cette vente
                                </td>
                            </tr>
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
            
            {{-- Summary --}}
            <div class="space-y-6">
                <x-ui.card title="Résumé">
                    <div class="space-y-4">
                        <div class="flex justify-between pt-2">
                            <span class="text-white font-semibold">Total</span>
                            <span class="text-2xl font-bold text-amber-400">{{ number_format($vente->total, 2) }} DH</span>
                        </div>
                    </div>
                </x-ui.card>
                
                <x-ui.card title="Paiement">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Mode</span>
                            @if($vente->payment_method === 'cash')
                                <x-ui.badge variant="success">Espèces</x-ui.badge>
                            @elseif($vente->payment_method === 'carte')
                                <x-ui.badge variant="info">Carte</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Mixte</x-ui.badge>
                            @endif
                        </div>
                        @if($vente->paiements->count() > 0)
                            @foreach($vente->paiements as $paiement)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Paiement {{ $loop->iteration }}</span>
                                    <span class="text-white">{{ number_format($paiement->amount, 2) }} DH</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </x-ui.card>
                
                <x-ui.card title="Informations">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Caissier</span>
                            <span class="text-white">{{ $vente->user->name ?? '-' }}</span>
                        </div>
                        @if($vente->table)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Table</span>
                                <span class="text-white">{{ $vente->table->name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-400">Date</span>
                            <span class="text-white">{{ $vente->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Heure</span>
                            <span class="text-white">{{ $vente->created_at->format('H:i:s') }}</span>
                        </div>
                    </div>
                </x-ui.card>

                @if($vente->status !== 'cancelled' && auth()->user()?->isAdmin())
                    <x-ui.card title="Annulation de vente">
                        <form method="POST" action="{{ route('ventes.cancel', $vente) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="cancel-comment" class="block text-sm font-medium text-gray-300 mb-2">
                                    Motif d'annulation <span class="text-red-400">*</span>
                                </label>
                                <textarea
                                    id="cancel-comment"
                                    name="comment"
                                    rows="4"
                                    required
                                    minlength="5"
                                    maxlength="1000"
                                    class="w-full rounded-xl border border-gray-700 bg-gray-900 text-white placeholder-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                    placeholder="Ex: Produit manquant, retour client, erreur de caisse..."
                                >{{ old('comment') }}</textarea>
                                @error('comment')
                                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500 transition-colors"
                                    onclick="return confirm('Confirmez-vous l\'annulation de cette vente ?')">
                                Confirmer l'annulation
                            </button>
                        </form>
                    </x-ui.card>
                @elseif($vente->status === 'cancelled' && $vente->cancel_reason)
                    <x-ui.card title="Motif d'annulation">
                        <p class="text-gray-200 whitespace-pre-line">{{ $vente->cancel_reason }}</p>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-layout.app>
