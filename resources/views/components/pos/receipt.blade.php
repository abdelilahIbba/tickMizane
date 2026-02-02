@props([
    'vente' => null,
    'paiement' => null,
])

<div class="bg-white text-gray-900 p-6 font-mono text-sm max-w-sm mx-auto">
    {{-- Header --}}
    <div class="text-center border-b-2 border-dashed border-gray-300 pb-4 mb-4">
        <h1 class="text-2xl font-bold tracking-wider">TECHMIZANE</h1>
        <p class="text-xs text-gray-600 mt-1">Cash Management System</p>
        <div class="mt-3 text-xs text-gray-500">
            <p>================================</p>
            <p>REÇU DE VENTE</p>
            <p>================================</p>
        </div>
    </div>
    
    {{-- Receipt Info --}}
    <div class="text-xs space-y-1 mb-4">
        <div class="flex justify-between">
            <span>N° Ticket:</span>
            <span class="font-semibold">#{{ str_pad($vente->id ?? rand(1000, 9999), 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Date:</span>
            <span>{{ ($vente->created_at ?? now())->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span>Caissier:</span>
            <span>{{ $vente->user->name ?? 'N/A' }}</span>
        </div>
        @if($vente && $vente->table_id)
            <div class="flex justify-between font-semibold text-amber-600">
                <span>Table:</span>
                <span>{{ $vente->table->name ?? 'N/A' }}</span>
            </div>
        @endif
    </div>
    
    <div class="border-t border-dashed border-gray-300 my-3"></div>
    
    {{-- Items --}}
    <div class="space-y-2 mb-4">
        <div class="flex justify-between font-semibold text-xs">
            <span class="flex-1">ARTICLE</span>
            <span class="w-12 text-center">QTÉ</span>
            <span class="w-20 text-right">PRIX</span>
            <span class="w-20 text-right">TOTAL</span>
        </div>
        <div class="border-t border-gray-200"></div>
        
        @if($vente && $vente->items)
            @foreach($vente->items as $item)
                <div class="flex justify-between text-xs">
                    <span class="flex-1 truncate pr-2">{{ $item->product->name ?? 'Produit' }}</span>
                    <span class="w-12 text-center">{{ $item->quantity }}</span>
                    <span class="w-20 text-right">{{ number_format($item->price, 2) }}</span>
                    <span class="w-20 text-right">{{ number_format($item->quantity * $item->price, 2) }}</span>
                </div>
            @endforeach
        @else
            {{-- Demo items --}}
            <div class="flex justify-between text-xs">
                <span class="flex-1">Produit exemple</span>
                <span class="w-12 text-center">2</span>
                <span class="w-20 text-right">25.00</span>
                <span class="w-20 text-right">50.00</span>
            </div>
        @endif
    </div>
    
    <div class="border-t-2 border-dashed border-gray-300 my-3"></div>
    
    {{-- Totals --}}
    <div class="space-y-1 text-xs">
        <div class="flex justify-between">
            <span>Sous-total HT:</span>
            <span>{{ number_format(($vente->total ?? 50) / 1.2, 2) }} DH</span>
        </div>
        <div class="flex justify-between">
            <span>TVA (20%):</span>
            <span>{{ number_format(($vente->total ?? 50) - (($vente->total ?? 50) / 1.2), 2) }} DH</span>
        </div>
        <div class="flex justify-between font-bold text-base mt-2 pt-2 border-t border-gray-300">
            <span>TOTAL TTC:</span>
            <span>{{ number_format($vente->total ?? 50, 2) }} DH</span>
        </div>
    </div>
    
    <div class="border-t border-dashed border-gray-300 my-3"></div>
    
    {{-- Payment Info --}}
    <div class="space-y-1 text-xs">
        <div class="flex justify-between">
            <span>Mode de paiement:</span>
            <span class="font-semibold uppercase">{{ $paiement->method ?? 'Espèces' }}</span>
        </div>
        <div class="flex justify-between">
            <span>Montant reçu:</span>
            <span>{{ number_format($paiement->amount ?? $vente->total ?? 50, 2) }} DH</span>
        </div>
        @if(($paiement->amount ?? 0) > ($vente->total ?? 0))
            <div class="flex justify-between font-semibold">
                <span>Monnaie rendue:</span>
                <span>{{ number_format(($paiement->amount ?? 0) - ($vente->total ?? 0), 2) }} DH</span>
            </div>
        @endif
    </div>
    
    <div class="border-t-2 border-dashed border-gray-300 my-4"></div>
    
    {{-- Footer --}}
    <div class="text-center text-xs text-gray-500 space-y-2">
        <p>================================</p>
        <p class="font-semibold">Merci pour votre achat!</p>
        <p>À bientôt chez Techmizane</p>
        <p>================================</p>
        <div class="mt-4">
            {{-- Barcode placeholder --}}
            <div class="flex justify-center gap-0.5">
                @for($i = 0; $i < 30; $i++)
                    <div class="w-0.5 bg-gray-900" style="height: {{ rand(20, 40) }}px"></div>
                @endfor
            </div>
            <p class="mt-2 text-[10px]">{{ str_pad($vente->id ?? rand(1000000, 9999999), 12, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
</div>
