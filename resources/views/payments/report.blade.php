<x-layout.app title="Rapport journalier">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('payments.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux paiements
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Rapport journalier</h1>
                    <p class="text-gray-400 mt-1">{{ now()->format('d/m/Y') }}</p>
                </div>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-amber-500 text-gray-900 rounded-xl font-semibold hover:bg-amber-400 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </button>
            </div>
        </div>
        
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.card title="Entrées de caisse">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Espèces</span>
                        <span class="text-white">8,200.00 DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Carte</span>
                        <span class="text-white">3,800.00 DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Mixte</span>
                        <span class="text-white">500.00 DH</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-700">
                        <span class="text-white font-semibold">Total</span>
                        <span class="text-emerald-400 font-bold">12,500.00 DH</span>
                    </div>
                </div>
            </x-ui.card>
            
            <x-ui.card title="Sorties de caisse">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Remboursements</span>
                        <span class="text-white">0.00 DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Dépenses</span>
                        <span class="text-white">350.00 DH</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-700">
                        <span class="text-white font-semibold">Total</span>
                        <span class="text-red-400 font-bold">350.00 DH</span>
                    </div>
                </div>
            </x-ui.card>
            
            <x-ui.card title="Solde">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Ouverture</span>
                        <span class="text-white">500.00 DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">+ Entrées</span>
                        <span class="text-emerald-400">12,500.00 DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">- Sorties</span>
                        <span class="text-red-400">350.00 DH</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-700">
                        <span class="text-white font-semibold">Solde final</span>
                        <span class="text-amber-400 font-bold text-xl">12,650.00 DH</span>
                    </div>
                </div>
            </x-ui.card>
        </div>
        
        {{-- Transactions --}}
        <x-ui.card title="Détail des transactions" :padding="false">
            <x-ui.table :headers="['Heure', 'Type', 'Description', 'Montant']">
                @foreach([
                    ['time' => '09:15', 'type' => 'Ouverture', 'desc' => 'Fond de caisse', 'amount' => 500.00, 'color' => 'blue'],
                    ['time' => '09:32', 'type' => 'Vente', 'desc' => 'Vente #000044', 'amount' => 890.50, 'color' => 'emerald'],
                    ['time' => '10:15', 'type' => 'Vente', 'desc' => 'Vente #000045', 'amount' => 35.00, 'color' => 'emerald'],
                    ['time' => '11:45', 'type' => 'Vente', 'desc' => 'Vente #000046', 'amount' => 512.00, 'color' => 'emerald'],
                    ['time' => '12:30', 'type' => 'Dépense', 'desc' => 'Achat fournitures', 'amount' => -350.00, 'color' => 'red'],
                    ['time' => '14:20', 'type' => 'Vente', 'desc' => 'Vente #000047', 'amount' => 89.00, 'color' => 'emerald'],
                    ['time' => '15:45', 'type' => 'Vente', 'desc' => 'Vente #000048', 'amount' => 245.50, 'color' => 'emerald'],
                ] as $tx)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-gray-400">{{ $tx['time'] }}</td>
                        <td class="px-6 py-4">
                            <x-ui.badge variant="{{ $tx['color'] === 'emerald' ? 'success' : ($tx['color'] === 'red' ? 'danger' : 'info') }}">
                                {{ $tx['type'] }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 text-white">{{ $tx['desc'] }}</td>
                        <td class="px-6 py-4 font-semibold {{ $tx['amount'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $tx['amount'] >= 0 ? '+' : '' }}{{ number_format($tx['amount'], 2) }} DH
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
