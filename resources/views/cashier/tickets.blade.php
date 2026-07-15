<x-layout.app title="Tickets de caisse et rapports">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Tickets et rapport PDF</h1>
                <p class="text-gray-400">Fonction admin pour calcul du chiffre d'affaires et impression des ventes.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('cashier.pending') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                    Retour encaissement
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-emerald-500/30 shadow-lg p-5">
            <p class="text-sm text-gray-400">Période sélectionnée</p>
            <p class="text-lg font-semibold text-white">{{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}</p>
        </div>
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-blue-500/30 shadow-lg p-5">
            <p class="text-sm text-gray-400">CA total / Nombre de ventes</p>
            <p class="text-lg font-semibold text-emerald-400">{{ number_format($totalRevenue, 2) }} DH</p>
            <p class="text-sm text-gray-300">{{ $salesCount }} ventes</p>
        </div>
    </div>

    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Tickets du jour</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('cashier.tickets.print', ['date' => today()->toDateString(), 'type' => 'summary']) }}"
               class="px-4 py-3 rounded-lg bg-amber-500 text-black font-semibold hover:bg-amber-400 transition-colors text-center">
                Ticket simple (CA total du jour)
            </a>
            <a href="{{ route('cashier.tickets.print', ['date' => today()->toDateString(), 'type' => 'detailed']) }}"
               class="px-4 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors text-center">
                Ticket detaille (toutes les ventes du jour)
            </a>
        </div>
    </div>

    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Rapport PDF (debut-fin)</h2>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="GET" action="{{ route('cashier.tickets') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
            <input type="date" name="date_start" value="{{ $dateStart }}" class="px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg">
            <input type="date" name="date_end" value="{{ $dateEnd }}" class="px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Appliquer filtre
            </button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('cashier.tickets.report.pdf', ['date_start' => $dateStart, 'date_end' => $dateEnd, 'type' => 'summary']) }}"
               class="px-4 py-3 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors text-center">
                Export PDF resume
            </a>
            <a href="{{ route('cashier.tickets.report.pdf', ['date_start' => $dateStart, 'date_end' => $dateEnd, 'type' => 'detailed']) }}"
               class="px-4 py-3 rounded-lg bg-purple-600 text-white font-semibold hover:bg-purple-700 transition-colors text-center">
                Export PDF detaille
            </a>
        </div>
    </div>
</div>
</x-layout.app>
