<x-layout.app title="Paiement - Commande #{{ $commande->id }}">

@push('styles')
<style>
/* ── Print styles ───────────────────────────────────────────────────────────── */
@media print {
    /* Make everything invisible but keep layout intact (display:none on a parent
       blocks children even when the child has display:block !important) */
    body * { visibility: hidden !important; }

    /* Show only the ticket and all its descendants */
    #print-ticket,
    #print-ticket * { visibility: visible !important; }

    /* Pin the ticket to the top-left corner of the printed page */
    #print-ticket {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        background: #fff !important;
    }
}

#print-ticket {
    /* Hidden on screen but still rendered so @media print can show it */
    position: absolute;
    left: -9999px;
    top: 0;
    width: 300px;
}

/* Ticket styles (used both in modal preview and when printing) */
.ticket-body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    color: #111;
    background: #fff;
    width: 300px;
    margin: 0 auto;
    padding: 16px 12px;
}
.ticket-body .ticket-title  { text-align: center; font-size: 17px; font-weight: bold; }
.ticket-body .ticket-sub    { text-align: center; font-size: 11px; color: #444; margin-bottom: 2px; }
.ticket-body .ticket-sep    { border: none; border-top: 1px dashed #555; margin: 8px 0; }
.ticket-body .ticket-sep-solid { border: none; border-top: 2px solid #111; margin: 8px 0; }
.ticket-body .ticket-row    { display: flex; justify-content: space-between; margin: 3px 0; }
.ticket-body .ticket-label  { color: #555; font-size: 11px; }
.ticket-body .ticket-total  { font-size: 16px; font-weight: bold; }
.ticket-body .ticket-footer { text-align: center; font-size: 11px; color: #444; margin-top: 4px; }
</style>
@endpush

@php
    $bizName       = \App\Models\Setting::getValue('business_name',    'Restaurant Dar El Amal');
    $bizAddress    = \App\Models\Setting::getValue('business_address', '');
    $bizPhone      = \App\Models\Setting::getValue('business_phone',   '');
    $receiptFooter = \App\Models\Setting::getValue('receipt_footer',   'Merci de votre visite !');
@endphp

{{-- ── Hidden print ticket ────────────────────────────────────────────────── --}}
<div id="print-ticket">
    <div class="ticket-body">
        {{-- Header --}}
        <p class="ticket-title">{{ $bizName }}</p>
        @if($bizAddress)
        <p class="ticket-sub">{{ $bizAddress }}</p>
        @endif
        @if($bizPhone)
        <p class="ticket-sub">Tél : {{ $bizPhone }}</p>
        @endif

        <hr class="ticket-sep-solid">

        {{-- Meta --}}
        <div class="ticket-row">
            <span class="ticket-label">Date</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="ticket-row">
            <span class="ticket-label">Commande #</span>
            <span>{{ $commande->id }}</span>
        </div>
        <div class="ticket-row">
            <span class="ticket-label">Table</span>
            <span>{{ $commande->table->name ?? 'N/A' }}</span>
        </div>
        <div class="ticket-row">
            <span class="ticket-label">Serveur</span>
            <span>{{ $commande->user->name ?? 'N/A' }}</span>
        </div>

        <hr class="ticket-sep">

        {{-- Items --}}
        @foreach($commande->details as $detail)
        <div style="margin: 4px 0;">
            <div style="display:flex; justify-content:space-between;">
                <span>{{ $detail->quantity }}x {{ $detail->produit->name }}</span>
                <span>{{ number_format($detail->price * $detail->quantity, 2) }} DH</span>
            </div>
            @if($detail->notes)
            <div style="font-size:11px; color:#666; padding-left:12px; font-style:italic;">
                ↳ {{ $detail->notes }}
            </div>
            @endif
        </div>
        @endforeach

        <hr class="ticket-sep-solid">

        {{-- Total --}}
        <div class="ticket-row ticket-total">
            <span>TOTAL</span>
            <span>{{ number_format($commande->total, 2) }} DH</span>
        </div>

        {{-- Payment method (filled by JS before print) --}}
        <div class="ticket-row" style="margin-top:4px;">
            <span class="ticket-label">Règlement</span>
            <span id="ticket-payment-method">—</span>
        </div>

        {{-- Change (filled by JS, hidden if zero) --}}
        <div class="ticket-row" id="ticket-change-row" style="display:none;">
            <span class="ticket-label">Monnaie rendue</span>
            <span id="ticket-change-amount"></span>
        </div>

        <hr class="ticket-sep">

        {{-- Footer --}}
        <p class="ticket-footer">{{ $receiptFooter }}</p>
        <p class="ticket-footer" style="margin-top:8px; font-size:10px;">
            — Techmizane Cash —
        </p>
    </div>
</div>

{{-- ── Print-preview modal ─────────────────────────────────────────────────── --}}
<div id="ticket-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm no-print">
    <div class="bg-white rounded-xl shadow-2xl overflow-hidden" style="min-width:340px;">
        {{-- Modal header --}}
        <div class="flex items-center justify-between px-5 py-3 bg-gray-100 border-b border-gray-200">
            <span class="font-semibold text-gray-700">Aperçu du ticket</span>
            <button onclick="closeTicketModal()" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
        </div>
        {{-- Ticket preview (mirrors #print-ticket) --}}
        <div id="ticket-preview-body" class="p-4"></div>
        {{-- Modal footer --}}
        <div class="flex gap-3 px-5 py-3 bg-gray-100 border-t border-gray-200">
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
                <h1 class="text-2xl font-bold text-white">Paiement - Table {{ $commande->table->name ?? 'N/A' }}</h1>
                <p class="text-gray-400">Commande #{{ $commande->id }} - {{ $commande->created_at->format('d/m/Y H:i') }}</p>
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
                        <p class="text-lg font-semibold text-white">{{ $commande->table->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Serveur</p>
                        <p class="text-lg font-semibold text-white">{{ $commande->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Statut</p>
                        <span class="inline-flex px-2 py-1 text-sm rounded-full
                            @if($commande->status === 'pret') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            @elseif($commande->status === 'servi') bg-cyan-500/20 text-cyan-400 border border-cyan-500/30
                            @else bg-blue-500/20 text-blue-400 border border-blue-500/30
                            @endif">
                            {{ $commande->status_label }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Heure</p>
                        <p class="text-lg font-semibold text-white">{{ $commande->created_at->format('H:i') }}</p>
                    </div>
                </div>

                <!-- Items List -->
                <div class="space-y-3">
                    @foreach($commande->details as $detail)
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

                @if($commande->waiter_notes)
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <p class="text-sm text-gray-400 mb-1">Notes du serveur:</p>
                    <p class="text-gray-300 italic">{{ $commande->waiter_notes }}</p>
                </div>
                @endif

                <!-- Total -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold text-white">Total à payer</span>
                        <span class="text-2xl font-bold text-emerald-400">{{ number_format($commande->total, 2) }} DH</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-gray-900/50 backdrop-blur-sm rounded-lg border border-gray-800 shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-800">
                <h2 class="text-lg font-bold text-white">Mode de paiement</h2>
            </div>
            
            <form action="{{ route('cashier.process-payment', $commande) }}" method="POST" class="p-4" id="paymentForm">
                @csrf
                
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
                           min="{{ $commande->total }}"
                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           placeholder="{{ number_format($commande->total, 2) }}"
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
                            <span id="mixedRemaining" class="text-lg font-bold text-red-400">{{ number_format($commande->total, 2) }} DH</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Amount Buttons (for cash) -->
                <div id="quickAmounts" class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Montants rapides</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([10, 20, 50, 100, 200, 500] as $amount)
                        @if($amount >= $commande->total)
                        <button type="button" onclick="setAmount({{ $amount }})"
                                class="px-3 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors border border-gray-700">
                            {{ $amount }} DH
                        </button>
                        @endif
                        @endforeach
                        <button type="button" onclick="setAmount({{ $commande->total }})"
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
const orderTotal = {{ $commande->total }};
let selectedMethod = 'cash';

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
    const change = received - orderTotal;
    
    const changeDisplay = document.getElementById('changeDisplay');
    const changeAmount = document.getElementById('changeAmount');
    
    if (received >= orderTotal && change > 0) {
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
    const remaining = orderTotal - total;
    
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
        changeVal = Math.max(0, received - orderTotal);
    }
    return { methodLabel, changeVal };
}

function populateTicket() {
    const { methodLabel, changeVal } = buildTicketData();
    document.getElementById('ticket-payment-method').textContent = methodLabel;
    const changeRow = document.getElementById('ticket-change-row');
    if (changeVal > 0) {
        document.getElementById('ticket-change-amount').textContent = changeVal.toFixed(2) + ' DH';
        changeRow.style.display = 'flex';
    } else {
        changeRow.style.display = 'none';
    }
}

function openTicketModal() {
    populateTicket();
    const preview = document.getElementById('ticket-preview-body');
    preview.innerHTML = '';
    preview.appendChild(document.querySelector('#print-ticket .ticket-body').cloneNode(true));
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

        // Payment confirmed — print then redirect
        populateTicket();

        // Redirect after print dialog is dismissed
        window.onafterprint = function() {
            window.location.href = '{{ route("cashier.pending") }}';
        };
        // Fallback redirect after 10 s in case onafterprint doesn't fire
        setTimeout(() => { window.location.href = '{{ route("cashier.pending") }}'; }, 10000);

        window.print();

    } catch (err) {
        alert('Erreur réseau. Veuillez réessayer.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Valider le paiement';
    }
});
</script>
@endpush
</x-layout.app>
