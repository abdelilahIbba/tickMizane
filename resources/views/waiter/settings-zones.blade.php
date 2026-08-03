<x-layout.app title="Parametres des zones">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Parametres des zones</h1>
            <p class="text-gray-400 text-sm mt-1">Creer des zones et generer automatiquement leurs tables.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('waiter.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-sm font-semibold transition-colors">
                Retour reception
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 px-4 py-3 text-sm">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="text-white font-semibold mb-4">Nouvelle zone</h2>

            <form method="POST" action="{{ route('waiter.settings.zones.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nom</label>
                    <input type="text" name="name" required maxlength="100"
                           class="w-full rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="Ex: Terrasse" value="{{ old('name') }}">
                </div>
                <div>
                      <label class="block text-sm text-slate-300 mb-1">Prefixe des tables (optionnel)</label>
                      <input type="text" name="prefix" maxlength="10"
                          class="w-full rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-500"
                          placeholder="Ex: T, S ou DF" value="{{ old('prefix') }}">
                      <p class="text-xs text-slate-500 mt-1">Si vide, la premiere lettre du nom de zone sera utilisee.</p>
                  </div>
                  <div>
                      <label class="block text-sm text-slate-300 mb-1">Nombre de tables</label>
                      <input type="number" name="tables_count" min="1" max="500" required
                          class="w-full rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                          placeholder="Ex: 10" value="{{ old('tables_count', 10) }}">
                      <p class="text-xs text-slate-500 mt-1">Format genere: prefixe + 3 chiffres (ex: T001, T002).</p>
                  </div>
                  <div>
                    <label class="block text-sm text-slate-300 mb-1">Description (optionnel)</label>
                    <input type="text" name="description" maxlength="255"
                           class="w-full rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                           placeholder="Ex: Espace exterieur" value="{{ old('description') }}">
                </div>
                <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-400 text-slate-900 font-semibold rounded-xl px-4 py-2.5 text-sm transition-colors">
                    Creer la zone
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-4">
            @forelse ($zones as $zone)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $zone->name }}</h3>
                            @if ($zone->description)
                                <p class="text-slate-400 text-sm mt-1">{{ $zone->description }}</p>
                            @endif
                            <p class="text-xs text-slate-500 mt-2">
                                Prefixe: <span class="text-slate-300 font-semibold">{{ $zone->prefix }}</span>
                                • {{ $zone->tables->count() }} table(s)
                            </p>
                        </div>

                        <form method="POST" action="{{ route('waiter.settings.zones.destroy', $zone) }}" onsubmit="return confirm('Supprimer cette zone ? Les tables seront desaffectees.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg bg-red-500/15 border border-red-500/25 text-red-300 hover:bg-red-500/25">
                                Supprimer
                            </button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('waiter.settings.zones.update', $zone) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $zone->name }}" maxlength="100" required
                               class="rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <input type="text" name="prefix" value="{{ $zone->prefix }}" maxlength="10"
                               class="rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-amber-500"
                               placeholder="Prefixe">
                        <input type="number" name="tables_count" value="{{ $zone->tables_count ?? $zone->tables->count() }}" min="1" max="500" required
                               class="rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                               placeholder="Nb tables">
                        <input type="text" name="description" value="{{ $zone->description }}" maxlength="255"
                               class="rounded-xl border border-slate-700 bg-slate-800 text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                               placeholder="Description">
                        <button type="submit" class="rounded-xl bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 text-sm font-semibold">
                            Mettre a jour
                        </button>
                    </form>

                    <div>
                        <p class="text-sm text-slate-300 mb-3">Tables generees</p>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-2 max-h-60 overflow-y-auto pr-1">
                            @forelse ($zone->tables as $table)
                                <div class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-200">
                                    {{ $table->name }}
                                </div>
                            @empty
                                <div class="col-span-full text-slate-500 text-sm">Aucune table dans cette zone.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-500">
                    Aucune zone pour le moment. Creez votre premiere zone.
                </div>
            @endforelse
        </div>
    </div>
</div>
</x-layout.app>
