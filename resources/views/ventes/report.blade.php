<x-layout.app title="Rapport des ventes">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Rapport des ventes</h1>
                <p class="mt-1 text-gray-400">Période du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('ventes.index') }}" class="rounded-xl border border-gray-700 bg-gray-800 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-700">
                Retour aux ventes
            </a>
        </div>

        <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-700 bg-gray-900/60 p-5">
                <p class="text-sm text-gray-400">Ventes payées</p>
                <p class="mt-2 text-3xl font-bold text-emerald-400">{{ number_format($stats['total_sales'], 2) }} DH</p>
            </div>
            <div class="rounded-2xl border border-gray-700 bg-gray-900/60 p-5">
                <p class="text-sm text-gray-400">Nb. ventes</p>
                <p class="mt-2 text-3xl font-bold text-blue-400">{{ $stats['sales_count'] }}</p>
            </div>
            <div class="rounded-2xl border border-red-500/30 bg-red-950/30 p-5">
                <p class="text-sm text-red-300">Annulations</p>
                <p class="mt-2 text-3xl font-bold text-red-400">{{ $stats['cancelled_sales'] }}</p>
            </div>
            <div class="rounded-2xl border border-red-500/30 bg-red-950/30 p-5">
                <p class="text-sm text-red-300">Montant annulé</p>
                <p class="mt-2 text-3xl font-bold text-red-400">{{ number_format($stats['cancelled_total'], 2) }} DH</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-700 bg-gray-900/60 overflow-hidden">
            <div class="border-b border-gray-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Historique des annulations</h2>
            </div>

            @if($cancelledSales->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400">
                    Aucune vente annulée pour cette période.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Vente</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Date</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Utilisateur</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Montant</th>
                                <th class="px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-400">Motif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($cancelledSales as $sale)
                                <tr class="hover:bg-gray-800/40">
                                    <td class="px-6 py-4 text-white">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ $sale->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ $sale->user->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-red-400 font-semibold">{{ number_format($sale->total, 2) }} DH</td>
                                    <td class="px-6 py-4 text-gray-200 max-w-md whitespace-pre-line">{{ $sale->cancel_reason ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layout.app>