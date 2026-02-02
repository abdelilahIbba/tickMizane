@extends('layouts.app')

@section('title', 'Serveur - Tables')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tables</h1>
            <p class="text-gray-600 mt-1">Sélectionnez une table pour prendre une commande</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('waiter.orders') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Mes Commandes
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap gap-6">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-700">Disponible</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-700">Occupée</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span class="text-sm text-gray-700">Réservée</span>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($tables as $table)
        <a href="{{ route('waiter.table.order', $table) }}" 
           class="block bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-200 p-6 border-2 
                  @if($table->status === 'free') border-green-500 hover:border-green-600
                  @elseif($table->status === 'occupied') border-red-500 hover:border-red-600
                  @else border-yellow-500 hover:border-yellow-600 @endif">
            
            <!-- Status Badge -->
            <div class="flex justify-between items-start mb-4">
                <span class="text-3xl font-bold text-gray-900">{{ $table->numero }}</span>
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                             @if($table->status === 'free') bg-green-100 text-green-800
                             @elseif($table->status === 'occupied') bg-red-100 text-red-800
                             @else bg-yellow-100 text-yellow-800 @endif">
                    @if($table->status === 'free') Disponible @elseif($table->status === 'occupied') Occupée @else {{ ucfirst($table->status) }} @endif
                </span>
            </div>

            <!-- Table Info -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-700">{{ $table->name }}</p>
                <p class="text-xs text-gray-500">Capacité: {{ $table->capacity }} personnes</p>
            </div>

            <!-- Action Button -->
            <div class="mt-4">
                @if($table->status === 'free')
                    <div class="text-center text-sm font-semibold text-green-600">
                        Prendre commande
                    </div>
                @elseif($table->status === 'occupied')
                    <div class="text-center text-sm font-semibold text-red-600">
                        Voir commande
                    </div>
                @else
                    <div class="text-center text-sm font-semibold text-yellow-600">
                        Réservée
                    </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    @if($tables->isEmpty())
    <div class="text-center py-12 bg-white rounded-lg shadow">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune table</h3>
        <p class="mt-1 text-sm text-gray-500">Contactez l'administrateur pour ajouter des tables.</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh every 30 seconds
setInterval(() => {
    window.location.reload();
}, 30000);
</script>
@endpush
