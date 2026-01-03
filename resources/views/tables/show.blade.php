<x-layout.app title="Table {{ $table->name }}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="tableDetail()">
        {{-- Page Header --}}
        <div class="mb-8">
            <a href="{{ route('tables.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux tables
            </a>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 {{ $table->status === 'occupied' ? 'bg-red-500/20' : 'bg-emerald-500/20' }} rounded-2xl flex items-center justify-center">
                        <span class="text-2xl font-bold {{ $table->status === 'occupied' ? 'text-red-400' : 'text-emerald-400' }}">{{ $table->name }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Table {{ $table->name }}</h1>
                        <p class="text-gray-400">
                            {{ $table->places ?? $table->seats ?? 4 }} places
                            @if($table->zone)
                                • {{ ucfirst($table->zone) }}
                            @endif
                            @if($table->status === 'occupied' && $table->occupied_at)
                                • Occupée depuis {{ $table->getOccupiedMinutes() }} min
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tables.edit', $table) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Status Badge --}}
        <div class="mb-6">
            @if($table->status === 'occupied')
                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-red-500/20 text-red-400 font-medium">
                    <span class="w-2 h-2 bg-red-400 rounded-full mr-2 animate-pulse"></span>
                    Table occupée
                </span>
            @elseif($table->status === 'reserved')
                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-amber-500/20 text-amber-400 font-medium">
                    <span class="w-2 h-2 bg-amber-400 rounded-full mr-2"></span>
                    Table réservée
                </span>
            @else
                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-500/20 text-emerald-400 font-medium">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2"></span>
                    Table libre
                </span>
            @endif
        </div>
        
        {{-- Quick Info --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Serveur</span>
                    <div class="text-white font-semibold mt-1">{{ $table->serveur?->name ?? 'Non assigné' }}</div>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Places</span>
                    <div class="text-white font-semibold mt-1">{{ $table->places ?? $table->seats ?? 4 }}</div>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Zone</span>
                    <div class="text-white font-semibold mt-1">{{ ucfirst($table->zone ?? 'Non définie') }}</div>
                </div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-center">
                    <span class="text-gray-400 text-sm">Total en cours</span>
                    <div class="text-amber-400 font-bold mt-1">{{ number_format($table->getCurrentBillAmount(), 2) }} DH</div>
                </div>
            </x-ui.card>
        </div>
        
        @if($table->status === 'occupied' && $table->currentVente)
            {{-- Current Order Items --}}
            <x-ui.card title="Commande en cours" class="mb-6">
                @if($table->currentVente->details && $table->currentVente->details->count() > 0)
                    <div class="space-y-4">
                        @foreach($table->currentVente->details as $detail)
                            <div class="flex items-center justify-between py-3 border-b border-gray-700 last:border-0">
                                <div class="flex items-center gap-4">
                                    <span class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center text-white font-medium">
                                        {{ $detail->quantity }}
                                    </span>
                                    <span class="text-white">{{ $detail->produit?->name ?? 'Produit inconnu' }}</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-amber-400 font-semibold">{{ number_format($detail->quantity * $detail->price, 2) }} DH</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-700">
                        <div class="flex justify-between text-lg">
                            <span class="text-white font-semibold">Total</span>
                            <span class="text-amber-400 font-bold">{{ number_format($table->currentVente->total ?? 0, 2) }} DH</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-400">Aucun article commandé pour le moment</p>
                    </div>
                @endif
            </x-ui.card>
        @elseif($table->status === 'free')
            {{-- Empty State --}}
            <x-ui.card class="mb-6">
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">Table libre</h3>
                    <p class="text-gray-400 mb-6">Cette table n'a pas de commande en cours</p>
                    <button @click="occupyTable()" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-medium rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Occuper la table
                    </button>
                </div>
            </x-ui.card>
        @endif
        
        {{-- Analytics --}}
        @if(isset($analytics))
            <x-ui.card title="Statistiques" class="mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-gray-400 text-sm">Ventes aujourd'hui</p>
                        <p class="text-2xl font-bold text-white">{{ $analytics['today_sales'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">CA aujourd'hui</p>
                        <p class="text-2xl font-bold text-amber-400">{{ number_format($analytics['today_revenue'] ?? 0, 2) }} DH</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Panier moyen</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($analytics['avg_ticket'] ?? 0, 2) }} DH</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Temps moyen</p>
                        <p class="text-2xl font-bold text-white">{{ $analytics['avg_duration'] ?? 0 }} min</p>
                    </div>
                </div>
            </x-ui.card>
        @endif
        
        {{-- Notes --}}
        @if($table->notes)
            <x-ui.card title="Notes" class="mb-6">
                <p class="text-gray-300">{{ $table->notes }}</p>
            </x-ui.card>
        @endif
        
        {{-- Actions --}}
        <div class="grid grid-cols-2 gap-4">
            @if($table->status === 'occupied')
                <x-ui.button variant="primary" size="xl" href="{{ route('pos.index', ['table' => $table->id]) }}" class="w-full justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter des articles
                </x-ui.button>
                <x-ui.button variant="success" size="xl" class="w-full justify-center" @click="showPaymentModal = true">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Encaisser
                </x-ui.button>
                <x-ui.button variant="info" size="xl" class="w-full justify-center" @click="showTransferModal = true">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Transférer
                </x-ui.button>
                <button @click="releaseTable()" class="flex items-center justify-center w-full px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Libérer la table
                </button>
            @else
                <x-ui.button variant="primary" size="xl" class="w-full justify-center col-span-2" @click="occupyTable()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Occuper la table
                </x-ui.button>
            @endif
        </div>
        
        {{-- Transfer Modal --}}
        <div x-show="showTransferModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/70" @click="showTransferModal = false"></div>
                <div class="relative bg-gray-800 rounded-2xl max-w-md w-full p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Transférer vers une autre table</h3>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach(\App\Models\Table::where('id', '!=', $table->id)->where('status', 'free')->get() as $otherTable)
                            <button @click="transferToTable({{ $otherTable->id }})" 
                                    class="w-full p-3 text-left bg-gray-700 hover:bg-gray-600 rounded-xl transition-colors">
                                <span class="text-white font-medium">Table {{ $otherTable->name }}</span>
                                <span class="text-gray-400 text-sm ml-2">{{ $otherTable->places ?? $otherTable->seats ?? 4 }} places</span>
                            </button>
                        @endforeach
                    </div>
                    <button @click="showTransferModal = false" class="mt-4 w-full p-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function tableDetail() {
            return {
                showTransferModal: false,
                showPaymentModal: false,
                
                async occupyTable() {
                    try {
                        const response = await fetch('{{ route("tables.occupy", $table) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (response.ok) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                
                async releaseTable() {
                    if (!confirm('Libérer cette table? La commande en cours sera fermée.')) return;
                    
                    try {
                        const response = await fetch('{{ route("tables.release", $table) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (response.ok) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                
                async transferToTable(targetTableId) {
                    try {
                        const response = await fetch('{{ route("tables.transfer", $table) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ target_table_id: targetTableId })
                        });
                        if (response.ok) {
                            window.location.href = '/tables/' + targetTableId;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }
            }
        }
    </script>
</x-layout.app>
