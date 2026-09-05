@props([
    'vente' => null,
    'paiement' => null,
])

<style>
@page {
    size: 80mm auto;
    margin: 0mm !important;
}
@media print {
    *, *::before, *::after {
        color: #000 !important;
        border-color: #000 !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    .pos-receipt-container {
        width: 70mm !important;
        max-width: 70mm !important;
        margin: 0 auto !important;
        padding: 2mm 3mm !important;
        color: #000 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .pos-receipt-container * {
        color: #000 !important;
    }
}
</style>

<div class="pos-receipt-container bg-white text-black p-4 font-mono text-xs max-w-sm mx-auto">
    {{-- Header --}}
    <div class="text-center border-b-2 border-dashed border-black pb-3 mb-3">
        <h1 class="text-2xl font-bold tracking-wider text-black">TECHMIZANE</h1>
        <p class="text-xs text-black font-semibold mt-1">Cash Management System</p>
        <div class="mt-2 text-xs text-black font-bold">
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
            <div class="flex justify-between font-semibold text-black">
                <span>Table:</span>
                <span>{{ $vente->table->name ?? 'N/A' }}</span>
            </div>
        @endif
    </div>
    
    <div class="border-t border-dashed border-black my-2"></div>
    
    {{-- Items --}}
    <div class="space-y-2 mb-3">
        <div class="flex justify-between font-bold text-xs text-black">
            <span class="flex-1">ARTICLE</span>
            <span class="w-12 text-center">QTÉ</span>
            <span class="w-20 text-right">PRIX</span>
            <span class="w-20 text-right">TOTAL</span>
        </div>
        <div class="border-t border-black"></div>
        
        @if($vente && $vente->items)
            @foreach($vente->items as $item)
                <div class="flex justify-between text-xs text-black">
                    <span class="flex-1 truncate pr-2 font-semibold">{{ $item->product->name ?? 'Produit' }}</span>
                    <span class="w-12 text-center font-bold">{{ $item->quantity }}</span>
                    <span class="w-20 text-right">{{ number_format($item->price, 2) }}</span>
                    <span class="w-20 text-right font-bold">{{ number_format($item->quantity * $item->price, 2) }}</span>
                </div>
            @endforeach
        @else
            {{-- Demo items --}}
            <div class="flex justify-between text-xs text-black">
                <span class="flex-1 font-semibold">Produit exemple</span>
                <span class="w-12 text-center font-bold">2</span>
                <span class="w-20 text-right">25.00</span>
                <span class="w-20 text-right font-bold">50.00</span>
            </div>
        @endif
    </div>
    
    <div class="border-t-2 border-dashed border-black my-2"></div>
    
    {{-- Totals --}}
    <div class="space-y-1 text-xs text-black">
        <div class="flex justify-between">
            <span>Sous-total HT:</span>
            <span>{{ number_format(($vente->total ?? 50) / 1.2, 2) }} DH</span>
        </div>
        <div class="flex justify-between">
            <span>TVA (20%):</span>
            <span>{{ number_format(($vente->total ?? 50) - (($vente->total ?? 50) / 1.2), 2) }} DH</span>
        </div>
        <div class="flex justify-between font-bold text-base mt-2 pt-2 border-t-2 border-black">
            <span>TOTAL TTC:</span>
            <span>{{ number_format($vente->total ?? 50, 2) }} DH</span>
        </div>
    </div>
    
    <div class="border-t border-dashed border-black my-2"></div>
    
    {{-- Payment Info --}}
    <div class="space-y-1 text-xs text-black">
        <div class="flex justify-between">
            <span>Mode de paiement:</span>
            <span class="font-bold uppercase">{{ $paiement->method ?? 'Espèces' }}</span>
        </div>
        <div class="flex justify-between">
            <span>Montant reçu:</span>
            <span>{{ number_format($paiement->amount ?? $vente->total ?? 50, 2) }} DH</span>
        </div>
        @if(($paiement->amount ?? 0) > ($vente->total ?? 0))
            <div class="flex justify-between font-bold">
                <span>Monnaie rendue:</span>
                <span>{{ number_format(($paiement->amount ?? 0) - ($vente->total ?? 0), 2) }} DH</span>
            </div>
        @endif
    </div>
    
    <div class="border-t-2 border-dashed border-black my-3"></div>
    
    {{-- Footer --}}
    <div class="text-center text-xs text-black font-semibold space-y-1">
        <p>================================</p>
        <p class="font-bold">Merci pour votre achat!</p>
        <p>À bientôt chez Techmizane</p>
        <p>================================</p>
        <div class="mt-3">
            {{-- Barcode placeholder --}}
            <div class="flex justify-center gap-0.5">
                @for($i = 0; $i < 30; $i++)
                    <div class="w-0.5 bg-black" style="height: {{ rand(20, 40) }}px"></div>
                @endfor
            </div>
            <p class="mt-2 text-[10px] font-bold">{{ str_pad($vente->id ?? rand(1000000, 9999999), 12, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
</div>
