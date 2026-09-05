@php
    $paymentOrders = $paymentOrders ?? collect([$commande]);
    $primaryOrder = $paymentOrders->first() ?? $commande;
    $displayTable = $primaryOrder?->table;
    $displayUser = $primaryOrder?->user;
    $combinedTotal = $combinedTotal ?? (float) $paymentOrders->sum(fn($order) => (float) $order->total);
    $ticketDetails = $paymentOrders->flatMap(fn($order) => $order->details)->values();
    $combinedOrderRefs = $paymentOrders->pluck('id')->map(fn($id) => '#' . $id)->implode(', ');
    $combinedNotes = $paymentOrders->pluck('waiter_notes')->filter()->values();
    $displayStatus = $paymentOrders->contains(fn($order) => $order->status === 'servi') ? 'servi' : 'pret';
@endphp

<x-layout.app title="Paiement - Table {{ $displayTable->numero ?? 'N/A' }}">

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Lato:wght@300;400;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<style>
/* ── Thermal Ticket Styles (Used in Modal Preview & Print) ─────────────────── */
.ticket {
    width: 320px;
    max-width: 100%;
    margin: 0 auto;
    background: #fff;
    padding: 0 0 10px 0;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #000;
    overflow: hidden;
    font-family: 'Lato', 'Courier New', Courier, monospace;
}

.ticket-header {
    background: #ffffff;
    padding: 14px 10px 10px;
    text-align: center;
    color: #000;
    border-bottom: 2px solid #000;
}

.logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 8px;
}

.logo-wrap img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}

.restaurant-name {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #000;
    margin: 8px 0 3px 0;
    line-height: 1.2;
}

.restaurant-tagline {
    font-family: 'Lato', sans-serif;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #000;
    margin: 0 0 6px 0;
}

.restaurant-phone {
    font-family: 'Lato', sans-serif;
    font-size: 12px;
    font-weight: 700;
    color: #000;
    letter-spacing: 0.5px;
    margin: 0;
}

.restaurant-phone::before {
    content: '☎ ';
    font-size: 11px;
}

.ticket-body {
    padding: 8px 8px 0;
    color: #000;
}

.ticket-date {
    font-family: 'Lato', sans-serif;
    font-size: 10.5px;
    font-weight: 700;
    color: #000;
    text-align: center;
    margin: 6px 0 3px;
    letter-spacing: 0.5px;
}

.sep {
    border: none;
    border-top: 1px dashed #000;
    margin: 6px 0;
}
.sep-strong {
    border: none;
    border-top: 2px solid #000;
    margin: 8px 0;
}
.sep-gold {
    border: none;
    border-top: 1.5px solid #000;
    margin: 8px 0;
}

.row {
    display: grid;
    grid-template-columns: minmax(68px, 78px) minmax(0, 1fr);
    align-items: start;
    gap: 6px;
    margin: 3px 0;
    font-family: 'Lato', sans-serif;
    font-size: 11px;
    color: #000;
}
.row > span:first-child {
    min-width: 0;
    overflow-wrap: break-word;
    color: #000;
}
.row > span:last-child {
    min-width: 0;
    text-align: right;
    overflow-wrap: anywhere;
    word-break: break-word;
    color: #000;
    font-weight: 700;
    padding-right: 2px;
}
.label {
    color: #000;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}

.item { margin: 4px 0; font-family: 'Lato', sans-serif; font-size: 11.5px; color: #000; }
.item .row {
    grid-template-columns: minmax(0, 1fr) 72px;
    gap: 4px;
}
.item .row > span:last-child {
    color: #000;
    font-weight: 800;
    padding-right: 2px;
}
.item .qty {
    color: #000;
    font-weight: 800;
}
.item-note { font-size: 10px; color: #000; padding-left: 12px; font-style: italic; font-weight: 600; }

.item-name-fr  { display: block; font-size: 11.5px; font-weight: 700; color: #000; line-height: 1.3; }
.item-name-en  { display: block; font-size: 10px; color: #000; font-style: italic; font-weight: 600; line-height: 1.3; margin-top: 1px; }
.item-name-ar  { display: block; font-size: 11.5px; color: #000; font-weight: 700; direction: rtl; text-align: right;
                 font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', serif;
                 line-height: 1.4; margin-top: 1px; }

.total-row {
    display: grid;
    grid-template-columns: minmax(0,1fr) auto;
    align-items: center;
    margin: 6px 0 4px;
    color: #000;
}
.total-label {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #000;
}
.total-amount {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 800;
    text-align: right;
    color: #000;
    padding-right: 2px;
}

.ticket-footer {
    text-align: center;
    padding: 8px 8px 0;
    color: #000;
}
.footer-msg {
    font-family: 'Playfair Display', serif;
    font-size: 12px;
    font-style: italic;
    font-weight: 700;
    color: #000;
    margin: 3px 0 2px;
}
.footer-sub {
    font-family: 'Lato', sans-serif;
    font-size: 9.5px;
    color: #000;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

#print-ticket {
    position: absolute;
    left: -9999px;
    top: 0;
    width: 320px;
}

/* ── Print styles ───────────────────────────────────────────────────────────── */
@media print {
    @page {
        size: 80mm auto;
        margin: 0mm !important;
    }

    *, *::before, *::after {
        color: #000 !important;
        border-color: #000 !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    body * { visibility: hidden !important; }

    #print-ticket,
    #print-ticket * { visibility: visible !important; color: #000 !important; }

    #print-ticket {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 70mm !important;
        max-width: 70mm !important;
        margin: 0 auto !important;
        padding: 0 1.5mm 1mm 1.5mm !important;
        background: #fff !important;
    }

    #print-ticket .ticket {
        width: 70mm !important;
        max-width: 70mm !important;
        margin: 0 auto !important;
        padding: 0 1.5mm 1mm 1.5mm !important;
        box-shadow: none !important;
        border: none !important;
        background: #fff !important;
        color: #000 !important;
        height: auto !important;
        min-height: 0 !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        break-after: avoid !important;
    }

    #print-ticket .ticket-header {
        padding: 4px 2px 6px !important;
        background: #fff !important;
        color: #000 !important;
        border-bottom: 2px solid #000 !important;
    }

    #print-ticket .logo-wrap {
        margin-bottom: 6px !important;
    }

    #print-ticket .logo-wrap img {
        width: 75px !important;
        height: 75px !important;
    }

    #print-ticket .restaurant-name {
        font-size: 20px !important;
        font-weight: 800 !important;
        margin: 6px 0 2px 0 !important;
    }

    #print-ticket .restaurant-tagline {
        font-size: 10px !important;
        font-weight: 800 !important;
        margin: 0 0 4px 0 !important;
    }

    #print-ticket .restaurant-phone {
        font-size: 11.5px !important;
        font-weight: 700 !important;
    }

    #print-ticket .ticket-body {
        padding: 4px 1mm 0 1mm !important;
    }

    #print-ticket .ticket-date {
        font-size: 10px !important;
        font-weight: 700 !important;
        margin: 4px 0 2px !important;
    }

    #print-ticket .row {
        grid-template-columns: minmax(62px, 72px) minmax(0, 1fr) !important;
        gap: 4px !important;
        margin: 2px 0 !important;
    }

    #print-ticket .row > span:last-child {
        padding-right: 1.5mm !important;
        font-weight: 700 !important;
    }

    #print-ticket .item .row {
        grid-template-columns: minmax(0, 1fr) 68px !important;
        gap: 4px !important;
    }

    #print-ticket .item .row > span:last-child {
        padding-right: 1.5mm !important;
        font-weight: 800 !important;
    }

    #print-ticket .sep {
        border-top: 1px dashed #000 !important;
        margin: 4px 0 !important;
    }

    #print-ticket .sep-strong {
        border-top: 2px solid #000 !important;
        margin: 6px 0 !important;
    }

    #print-ticket .sep-gold {
        border-top: 1.5px solid #000 !important;
        margin: 5px 0 !important;
    }

    #print-ticket .total-row {
        margin: 4px 0 2px !important;
    }

    #print-ticket .total-label {
        font-size: 16px !important;
        font-weight: 800 !important;
    }

    #print-ticket .total-amount {
        font-size: 17px !important;
        font-weight: 800 !important;
        padding-right: 1.5mm !important;
    }

    #print-ticket .ticket-footer {
        padding: 4px 2px 0 !important;
    }

    #print-ticket .footer-msg {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        margin: 2px 0 1px !important;
    }

    #print-ticket .footer-sub {
        font-size: 9px !important;
        font-weight: 700 !important;
    }
}
</style>
@endpush

@php
    $bizName       = 'Oussoul House';
    $bizTagline    = 'RESTAURANT & HOTEL';
    $bizPhone      = '06-60-43-27-86';
    $receiptFooter = 'Merci de votre visite !';

    $logoPath = public_path('logo-hq.png');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : asset('logo.svg');
@endphp

{{-- ── Hidden print ticket ────────────────────────────────────────────────── --}}
<div id="print-ticket">
    <div class="ticket">
        {{-- ===== HEADER ===== --}}
        <div class="ticket-header">
            <div class="logo-wrap">
                <img src="{{ $logoSrc }}" alt="Oussoul House Logo">
            </div>
            <div class="restaurant-name">{{ $bizName }}</div>
            <div class="restaurant-tagline">{{ $bizTagline }}</div>
            <div class="restaurant-phone">{{ $bizPhone }}</div>
        </div>

        {{-- ===== BODY ===== --}}
        <div class="ticket-body">
            <div class="ticket-date">{{ now()->format('d/m/Y  ·  H:i') }}</div>

            <hr class="sep-gold">

            <div class="row">
                <span class="label">Commandes</span>
                <span>{{ $combinedOrderRefs }}</span>
            </div>
            <div class="row">
                <span class="label">Table</span>
                <span>{{ $displayTable->name ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Serveur</span>
                <span>{{ $displayUser->name ?? 'N/A' }}</span>
            </div>

            <hr class="sep">

            @foreach($ticketDetails as $detail)
                @php
                    $produit = $detail->produit;
                    $nameFr  = $produit->name    ?? 'Produit';
                    $nameEn  = $produit->name_en ?? null;
                    $nameAr  = $produit->name_ar ?? null;
                @endphp
                <div class="item">
                    <div class="row">
                        <span>
                            <span class="qty">{{ $detail->quantity }}×</span>
                            <span class="item-name-fr">{{ $nameFr }}</span>
                            @if($nameEn && $nameEn !== $nameFr)
                                <span class="item-name-en">{{ $nameEn }}</span>
                            @endif
                            @if($nameAr)
                                <span class="item-name-ar">{{ $nameAr }}</span>
                            @endif
                        </span>
                        <span>{{ number_format($detail->price * $detail->quantity, 2) }} DH</span>
                    </div>
                    @if($detail->notes)
                        <div class="item-note">{{ $detail->notes }}</div>
                    @endif
                </div>
            @endforeach

            <hr class="sep-strong">

            <div class="total-row">
                <span class="total-label">TOTAL</span>
                <span class="total-amount" id="ticket-total-amount">{{ number_format($combinedTotal, 2) }} DH</span>
            </div>

            <div id="ticket-discount-row" class="row" style="display:none; margin-top:4px;">
                <span class="label">Remise</span>
                <span id="ticket-discount-amount" style="color:#000; font-weight:700;"></span>
            </div>
            <div id="ticket-net-row" class="total-row" style="display:none; border-top:1px dashed #000; margin-top:6px; padding-top:6px;">
                <span class="total-label">NET À PAYER</span>
                <span class="total-amount" id="ticket-net-amount"></span>
            </div>

            <div class="row" style="margin-top:6px;">
                <span class="label">Règlement</span>
                <span id="ticket-payment-method">Espèces</span>
            </div>
            <div class="row" id="ticket-change-row" style="display:none;">
                <span class="label">Monnaie rendue</span>
                <span id="ticket-change-amount"></span>
            </div>

        </div>{{-- /.ticket-body --}}

        <hr class="sep" style="margin: 6px 8px 0;">

        {{-- ===== FOOTER ===== --}}
        <div class="ticket-footer">
            <div class="footer-msg">{{ $receiptFooter }}</div>
            <div class="footer-sub">{{ $bizName }} — {{ $bizPhone }}</div>
        </div>
    </div>
</div>

{{-- ── Print-preview modal ─────────────────────────────────────────────────── --}}
<div id="ticket-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm no-print p-4">
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" style="min-width:340px;">
        {{-- Modal header --}}
        <div class="flex items-center justify-between px-5 py-3 bg-gray-100 border-b border-gray-200 shrink-0">
            <span class="font-semibold text-gray-700">Aperçu du ticket</span>
            <button onclick="closeTicketModal()" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
        </div>
        {{-- Ticket preview (mirrors #print-ticket) --}}
        <div id="ticket-preview-body" class="p-4 overflow-y-auto flex-1"></div>
        {{-- Modal footer --}}
        <div class="flex gap-3 px-5 py-3 bg-gray-100 border-t border-gray-200 shrink-0">
            <button onclick="doPrint()"
                    class="flex-1 px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer
            </button>
            <button onclick="closeTicketModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg p-6 mb-6 no-print">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Paiement - Table {{ $displayTable->name ?? 'N/A' }}</h1>
                <p class="text-gray-400">Commandes {{ $combinedOrderRefs }} - {{ $primaryOrder?->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Print ticket button --}}
                <button onclick="openTicketModal()"
                        class="flex items-center gap-2 px-4 py-2 bg-amber-600/20 text-amber-400 border border-amber-500/40 rounded-lg hover:bg-amber-600/30 transition-colors no-print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Ticket
                </button>
                <a href="{{ route('cashier.pending') }}" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors no-print">
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 no-print">
        <!-- Order Details -->
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-800">
                <h2 class="text-lg font-bold text-white">Détails de la commande</h2>
            </div>
            
            <div class="p-4">
                <!-- Order Info -->
                <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-800">
                    <div>
                        <p class="text-sm text-gray-400">Table</p>
                        <p class="text-lg font-semibold text-white">{{ $displayTable->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Serveur</p>
                        <p class="text-lg font-semibold text-white">{{ $displayUser->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Statut</p>
                        <span class="inline-flex px-2 py-1 text-sm rounded-full
                            @if($displayStatus === 'pret') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($displayStatus === 'servi') bg-cyan-500/20 text-cyan-400 border border-cyan-500/30
                            @else bg-blue-500/20 text-blue-400 border border-blue-500/30
                            @endif">
                            Prêt à payer
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Commandes</p>
                        <p class="text-lg font-semibold text-white">{{ $paymentOrders->count() }}</p>
                    </div>
                </div>

                <!-- Items List -->
                <div class="space-y-3">
                    @foreach($ticketDetails as $detail)
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-white font-medium">{{ $detail->quantity }}x</span>
                                <span class="text-white">{{ $detail->produit->name }}</span>
                            </div>
                            @if($detail->notes)
                            <p class="text-xs text-gray-500 italic ml-6">{{ $detail->notes }}</p>
                            @endif
                        </div>
                        <span class="text-gray-300 font-semibold">{{ number_format($detail->price * $detail->quantity, 2) }} DH</span>
                    </div>
                    @endforeach
                </div>

                @if($combinedNotes->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <p class="text-sm text-gray-400 mb-1">Notes du serveur:</p>
                    <div class="space-y-1">
                        @foreach($combinedNotes as $note)
                        <p class="text-gray-300 italic">{{ $note }}</p>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Total -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-white">Total à payer</span>
                        <span class="text-2xl font-bold text-emerald-400" id="displayTotal">{{ number_format($combinedTotal, 2) }} DH</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-800">
                <h2 class="text-lg font-bold text-white">Mode de paiement</h2>
            </div>
            
            <form action="{{ route('cashier.process-payment', $primaryOrder) }}" method="POST" class="p-4" id="paymentForm">
                @csrf
                
                <!-- Discount Field -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Remise (%)
                        <span class="text-gray-500 font-normal ml-1">— optionnel</span>
                    </label>
                    <div class="relative">
                        <input type="number"
                               name="discount_percent"
                               id="discountPercent"
                               step="0.5"
                               min="0"
                               max="100"
                               value="0"
                               class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-lg pr-10"
                               placeholder="0"
                               oninput="applyDiscount()">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                    </div>
                    <div id="discountSummary" class="hidden mt-2 px-3 py-2 bg-amber-500/10 border border-amber-500/30 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Sous-total :</span>
                            <span id="discountSubtotal" class="text-gray-300"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-amber-400">Remise :</span>
                            <span id="discountAmount" class="text-amber-400 font-semibold"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold mt-1 pt-1 border-t border-amber-500/20">
                            <span class="text-white">Net à payer :</span>
                            <span id="discountFinal" class="text-emerald-400"></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-3">Sélectionner le mode</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" onclick="selectPaymentMethod('cash')"
                                class="payment-method-btn flex flex-col items-center p-4 rounded-lg border-2 border-gray-700 hover:border-green-500 transition-colors"
                                data-method="cash">
                            <svg class="w-8 h-8 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-white font-medium">Espèces</span>
                        </button>
                        
                        <button type="button" onclick="selectPaymentMethod('carte')"
                                class="payment-method-btn flex flex-col items-center p-4 rounded-lg border-2 border-gray-700 hover:border-blue-500 transition-colors"
                                data-method="carte">
                            <svg class="w-8 h-8 text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="text-white font-medium">Carte</span>
                        </button>
                        
                        <button type="button" onclick="selectPaymentMethod('mixte')"
                                class="payment-method-btn flex flex-col items-center p-4 rounded-lg border-2 border-gray-700 hover:border-purple-500 transition-colors"
                                data-method="mixte">
                            <svg class="w-8 h-8 text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="text-white font-medium">Mixte</span>
                        </button>
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                </div>

                <!-- Cash Payment Fields -->
                <div id="cashFields" class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Montant reçu (DH)</label>
                    <input type="number" 
                           name="amount_received" 
                           id="amountReceived"
                           step="0.01" 
                           min="{{ $combinedTotal }}"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           placeholder="{{ number_format($combinedTotal, 2) }}"
                           onchange="calculateChange()">
                    
                    <div id="changeDisplay" class="mt-3 p-3 bg-gray-800/50 rounded-lg hidden">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Monnaie à rendre:</span>
                            <span id="changeAmount" class="text-xl font-bold text-yellow-400">0.00 DH</span>
                        </div>
                    </div>
                </div>

                <!-- Mixed Payment Fields -->
                <div id="mixedFields" class="mb-6 hidden">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Espèces (DH)</label>
                            <input type="number" 
                                   name="cash_amount" 
                                   id="cashAmount"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00"
                                   onchange="calculateMixedTotal()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Carte (DH)</label>
                            <input type="number" 
                                   name="card_amount" 
                                   id="cardAmount"
                                   step="0.01" 
                                   min="0"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00"
                                   onchange="calculateMixedTotal()">
                        </div>
                    </div>
                    
                    <div id="mixedTotalDisplay" class="mt-3 p-3 bg-gray-800/50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Total saisi:</span>
                            <span id="mixedTotal" class="text-lg font-bold text-white">0.00 DH</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-400">Reste à payer:</span>
                            <span id="mixedRemaining" class="text-lg font-bold text-red-400">{{ number_format($combinedTotal, 2) }} DH</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Amount Buttons (for cash) -->
                <div id="quickAmounts" class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Montants rapides</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([10, 20, 50, 100, 200, 500] as $amount)
                        @if($amount >= $combinedTotal)
                        <button type="button" onclick="setAmount({{ $amount }})"
                                class="px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors border border-gray-700">
                            {{ $amount }} DH
                        </button>
                        @endif
                        @endforeach
                        <button type="button" onclick="setAmount({{ $combinedTotal }})"
                                class="px-3 py-2 bg-emerald-600/20 text-emerald-400 rounded-lg hover:bg-emerald-600/30 transition-colors border border-emerald-500/30">
                            Exact
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                        class="w-full px-6 py-4 bg-emerald-600 text-white text-xl font-bold rounded-lg hover:bg-emerald-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                    Valider le paiement
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const orderTotal = {{ $combinedTotal }};
let effectiveTotal = orderTotal;
let selectedMethod = 'cash';

function applyDiscount() {
    const pct = Math.min(100, Math.max(0, parseFloat(document.getElementById('discountPercent').value) || 0));
    const discountAmt = parseFloat((orderTotal * pct / 100).toFixed(2));
    effectiveTotal   = parseFloat((orderTotal - discountAmt).toFixed(2));

    // Update the displayed total in the order details panel
    document.getElementById('displayTotal').textContent = effectiveTotal.toFixed(2) + ' DH';

    // Show/hide discount breakdown
    const summary = document.getElementById('discountSummary');
    if (pct > 0) {
        summary.classList.remove('hidden');
        document.getElementById('discountSubtotal').textContent = orderTotal.toFixed(2) + ' DH';
        document.getElementById('discountAmount').textContent  = '-' + discountAmt.toFixed(2) + ' DH (' + pct + '%)';
        document.getElementById('discountFinal').textContent   = effectiveTotal.toFixed(2) + ' DH';
    } else {
        summary.classList.add('hidden');
    }

    // Update cash field min & placeholder
    const amtInput = document.getElementById('amountReceived');
    amtInput.min = effectiveTotal;
    amtInput.placeholder = effectiveTotal.toFixed(2);

    // Rebuild quick-amount buttons
    rebuildQuickAmounts();

    // Re-run current method calculations
    if (selectedMethod === 'cash') calculateChange();
    if (selectedMethod === 'mixte') calculateMixedTotal();
}

function rebuildQuickAmounts() {
    const presets = [10, 20, 50, 100, 200, 500];
    const container = document.getElementById('quickAmounts').querySelector('.grid');
    container.innerHTML = '';
    presets.filter(a => a >= effectiveTotal).forEach(a => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.onclick = () => setAmount(a);
        btn.className = 'px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors border border-gray-700';
        btn.textContent = a + ' DH';
        container.appendChild(btn);
    });
    const exact = document.createElement('button');
    exact.type = 'button';
    exact.onclick = () => setAmount(effectiveTotal);
    exact.className = 'px-3 py-2 bg-emerald-600/20 text-emerald-400 rounded-lg hover:bg-emerald-600/30 transition-colors border border-emerald-500/30';
    exact.textContent = 'Exact';
    container.appendChild(exact);
}

function selectPaymentMethod(method) {
    selectedMethod = method;
    document.getElementById('paymentMethod').value = method;
    
    // Update button styles
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('border-green-500', 'border-blue-500', 'border-purple-500', 'bg-green-500/10', 'bg-blue-500/10', 'bg-purple-500/10');
        btn.classList.add('border-gray-700');
    });
    
    const activeBtn = document.querySelector(`.payment-method-btn[data-method="${method}"]`);
    if (method === 'cash') {
        activeBtn.classList.remove('border-gray-700');
        activeBtn.classList.add('border-green-500', 'bg-green-500/10');
    } else if (method === 'carte') {
        activeBtn.classList.remove('border-gray-700');
        activeBtn.classList.add('border-blue-500', 'bg-blue-500/10');
    } else {
        activeBtn.classList.remove('border-gray-700');
        activeBtn.classList.add('border-purple-500', 'bg-purple-500/10');
    }
    
    // Show/hide fields
    document.getElementById('cashFields').classList.toggle('hidden', method === 'carte');
    document.getElementById('quickAmounts').classList.toggle('hidden', method !== 'cash');
    document.getElementById('mixedFields').classList.toggle('hidden', method !== 'mixte');
    
    if (method === 'cash') {
        document.getElementById('cashFields').classList.remove('hidden');
    }
}

function setAmount(amount) {
    document.getElementById('amountReceived').value = amount.toFixed(2);
    calculateChange();
}

function calculateChange() {
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    const change = received - effectiveTotal;

    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount  = document.getElementById('changeAmount');

    if (received >= effectiveTotal && change > 0) {
        changeDisplay.classList.remove('hidden');
        changeAmount.textContent = change.toFixed(2) + ' DH';
    } else {
        changeDisplay.classList.add('hidden');
    }
}

function calculateMixedTotal() {
    const cash = parseFloat(document.getElementById('cashAmount').value) || 0;
    const card = parseFloat(document.getElementById('cardAmount').value) || 0;
    const total = cash + card;
    const remaining = effectiveTotal - total;

    document.getElementById('mixedTotal').textContent = total.toFixed(2) + ' DH';

    const remainingEl = document.getElementById('mixedRemaining');
    if (remaining <= 0) {
        remainingEl.textContent = '0.00 DH';
        remainingEl.classList.remove('text-red-400');
        remainingEl.classList.add('text-emerald-400');
    } else {
        remainingEl.textContent = remaining.toFixed(2) + ' DH';
        remainingEl.classList.remove('text-emerald-400');
        remainingEl.classList.add('text-red-400');
    }
}

// Initialize with cash selected
selectPaymentMethod('cash');

// ── Ticket / print logic ─────────────────────────────────────────────────────
function buildTicketData() {
    const methodLabels = { cash: 'Espèces', carte: 'Carte bancaire', mixte: 'Mixte (Espèces + Carte)' };
    const methodLabel  = methodLabels[selectedMethod] || selectedMethod;
    let changeVal = 0;
    if (selectedMethod === 'cash') {
        const received = parseFloat(document.getElementById('amountReceived').value) || 0;
        changeVal = Math.max(0, received - effectiveTotal);
    }
    return { methodLabel, changeVal };
}

function populateTicket() {
    const { methodLabel, changeVal } = buildTicketData();
    const methodEl = document.getElementById('ticket-payment-method');
    if (methodEl) methodEl.textContent = methodLabel;

    const changeRow = document.getElementById('ticket-change-row');
    if (changeRow) {
        if (changeVal > 0) {
            document.getElementById('ticket-change-amount').textContent = changeVal.toFixed(2) + ' DH';
            changeRow.style.display = 'grid';
        } else {
            changeRow.style.display = 'none';
        }
    }

    const pct = Math.min(100, Math.max(0, parseFloat(document.getElementById('discountPercent')?.value) || 0));
    const discountRow = document.getElementById('ticket-discount-row');
    const netRow = document.getElementById('ticket-net-row');

    if (pct > 0) {
        const discountAmt = parseFloat((orderTotal * pct / 100).toFixed(2));
        const netAmt = parseFloat((orderTotal - discountAmt).toFixed(2));
        if (discountRow) {
            document.getElementById('ticket-discount-amount').textContent = '-' + discountAmt.toFixed(2) + ' DH (' + pct + '%)';
            discountRow.style.display = 'grid';
        }
        if (netRow) {
            document.getElementById('ticket-net-amount').textContent = netAmt.toFixed(2) + ' DH';
            netRow.style.display = 'grid';
        }
    } else {
        if (discountRow) discountRow.style.display = 'none';
        if (netRow) netRow.style.display = 'none';
    }
}

function openTicketModal() {
    populateTicket();
    const preview = document.getElementById('ticket-preview-body');
    preview.innerHTML = '';
    const ticketClone = document.querySelector('#print-ticket .ticket').cloneNode(true);
    preview.appendChild(ticketClone);
    const modal = document.getElementById('ticket-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeTicketModal() {
    const modal = document.getElementById('ticket-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function doPrint() {
    populateTicket();
    closeTicketModal();
    window.print();
}

document.getElementById('ticket-modal').addEventListener('click', function(e) {
    if (e.target === this) closeTicketModal();
});

// ── Submit payment via fetch → auto-print ticket → redirect ──────────────────
document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (selectedMethod === 'mixte') {
        const cash = parseFloat(document.getElementById('cashAmount').value) || 0;
        const card = parseFloat(document.getElementById('cardAmount').value) || 0;
        if ((cash + card) < orderTotal) {
            alert('Le montant total est insuffisant.');
            return;
        }
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Traitement…';

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(this),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            alert(data.message || 'Erreur lors du traitement du paiement.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Valider le paiement';
            return;
        }

        window.location.href = data.print_url || data.redirect_url || '{{ route("cashier.pending") }}';

    } catch (err) {
        alert('Erreur réseau. Veuillez réessayer.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Valider le paiement';
    }
});
</script>
@endpush
</x-layout.app>
