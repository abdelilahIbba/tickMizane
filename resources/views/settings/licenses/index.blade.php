<x-layout.app title="Licences clients">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Licences clients</h1>
        <p class="text-gray-400 mt-1">
            Réservé au Super Admin (développeur DevNApp). Créez une période d’essai pour un client, puis activez-la :
            le compte à rebours démarre immédiatement. À l’expiration, le système se bloque jusqu’à une nouvelle licence.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-700 bg-emerald-900/40 px-4 py-3 text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-700 bg-red-900/40 px-4 py-3 text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-1 bg-gray-900/50 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Licence active</h2>
            @if($current)
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400">Client</dt>
                        <dd class="text-white font-medium">{{ $current->client_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Période</dt>
                        <dd class="text-white">{{ $current->periodLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Expire le</dt>
                        <dd class="text-amber-300">{{ $current->expires_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-red-300 text-sm">
                    Aucune licence active. Les clients (Super User et autres rôles) sont bloqués
                    avec une alerte leur demandant de contacter DevNApp pour payer / prolonger.
                </p>
            @endif
        </div>

        <div class="lg:col-span-2 bg-gray-900/50 border border-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Créer une période d’essai / licence</h2>
            <form method="POST" action="{{ route('settings.licenses.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="client_name" class="block text-sm text-gray-300 mb-1">Nom du client</label>
                    <input
                        id="client_name"
                        name="client_name"
                        type="text"
                        value="{{ old('client_name') }}"
                        required
                        class="w-full rounded-lg bg-gray-950 border border-gray-700 text-white px-3 py-2"
                    >
                    @error('client_name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="period" class="block text-sm text-gray-300 mb-1">Période</label>
                    <select
                        id="period"
                        name="period"
                        required
                        class="w-full rounded-lg bg-gray-950 border border-gray-700 text-white px-3 py-2"
                    >
                        @foreach($periods as $value => $label)
                            <option value="{{ $value }}" @selected(old('period') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('period')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm text-gray-300 mb-1">Notes (optionnel)</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="w-full rounded-lg bg-gray-950 border border-gray-700 text-white px-3 py-2"
                    >{{ old('notes') }}</textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="activate_now" value="1" class="rounded border-gray-600 bg-gray-950" checked>
                    Activer maintenant (démarre le compte à rebours immédiatement)
                </label>

                <p class="text-xs text-gray-500">
                    Sans activation, la licence reste inactive et le client ne peut pas utiliser le système.
                </p>

                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold">
                    Créer la licence
                </button>
            </form>
        </div>
    </div>

    <div class="bg-gray-900/50 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
            <h2 class="text-lg font-semibold text-white">Historique des licences</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-950/60 text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Client</th>
                        <th class="px-4 py-3 text-left">Période</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-left">Activation</th>
                        <th class="px-4 py-3 text-left">Expiration</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($licenses as $license)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ $license->client_name }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $license->periodLabel() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                    @if($license->status === 'active') bg-emerald-900/40 text-emerald-300
                                    @elseif($license->status === 'created') bg-blue-900/40 text-blue-300
                                    @elseif($license->status === 'expired') bg-amber-900/40 text-amber-300
                                    @else bg-red-900/40 text-red-300 @endif">
                                    {{ $license->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $license->activated_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $license->expires_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                @if(!$license->isCurrentlyValid() && $license->status !== 'revoked')
                                    <form method="POST" action="{{ route('settings.licenses.activate', $license) }}" class="inline">
                                        @csrf
                                        <button class="text-emerald-400 hover:text-emerald-300">Activer</button>
                                    </form>
                                @endif
                                @if($license->status !== 'revoked')
                                    <form method="POST" action="{{ route('settings.licenses.revoke', $license) }}" class="inline" onsubmit="return confirm('Révoquer cette licence ?')">
                                        @csrf
                                        <button class="text-red-400 hover:text-red-300">Révoquer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Aucune licence créée pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($licenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-800">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</div>
</x-layout.app>
