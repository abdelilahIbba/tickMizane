<x-layout.app title="Paiements">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Paiements</h1>
                <p class="text-gray-400 mt-1">Historique des paiements reçus</p>
            </div>
            <x-ui.button variant="info" href="{{ route('payments.report') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Rapport journalier
            </x-ui.button>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.stat-card 
                title="Total du jour" 
                value="{{ number_format(12500, 2) }} DH"
                color="amber"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            />
            <x-ui.stat-card 
                title="Espèces" 
                value="{{ number_format(8200, 2) }} DH"
                color="emerald"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'
            />
            <x-ui.stat-card 
                title="Carte" 
                value="{{ number_format(3800, 2) }} DH"
                color="blue"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>'
            />
            <x-ui.stat-card 
                title="Mixte" 
                value="{{ number_format(500, 2) }} DH"
                color="purple"
                icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>'
            />
        </div>
        
        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap gap-4">
            <x-form.input 
                type="date" 
                name="date_from" 
                class="w-40"
            />
            <x-form.input 
                type="date" 
                name="date_to" 
                class="w-40"
            />
            <x-form.select 
                name="method" 
                placeholder="Mode de paiement"
                :options="['cash' => 'Espèces', 'card' => 'Carte', 'mixed' => 'Mixte']"
                class="w-48"
            />
        </div>
        
        {{-- Payments Table --}}
        <x-ui.card :padding="false">
            <x-ui.table :headers="['N° Paiement', 'Vente', 'Date', 'Montant', 'Mode', 'Actions']">
                @foreach([
                    ['id' => 52, 'vente_id' => 48, 'date' => now()->subMinutes(15), 'amount' => 245.50, 'method' => 'cash'],
                    ['id' => 51, 'vente_id' => 47, 'date' => now()->subMinutes(45), 'amount' => 89.00, 'method' => 'card'],
                    ['id' => 50, 'vente_id' => 46, 'date' => now()->subHours(1), 'amount' => 512.00, 'method' => 'mixed'],
                    ['id' => 49, 'vente_id' => 45, 'date' => now()->subHours(2), 'amount' => 35.00, 'method' => 'cash'],
                    ['id' => 48, 'vente_id' => 44, 'date' => now()->subHours(3), 'amount' => 890.50, 'method' => 'card'],
                ] as $payment)
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">#{{ str_pad($payment['id'], 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('ventes.show', $payment['vente_id']) }}" class="text-amber-400 hover:text-amber-300">
                                Vente #{{ str_pad($payment['vente_id'], 6, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $payment['date']->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-amber-400 font-semibold">{{ number_format($payment['amount'], 2) }} DH</td>
                        <td class="px-6 py-4">
                            @if($payment['method'] === 'cash')
                                <x-ui.badge variant="success">Espèces</x-ui.badge>
                            @elseif($payment['method'] === 'card')
                                <x-ui.badge variant="info">Carte</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Mixte</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <x-ui.button variant="ghost" size="sm" href="{{ route('payments.receipt', $payment['id']) }}">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layout.app>
