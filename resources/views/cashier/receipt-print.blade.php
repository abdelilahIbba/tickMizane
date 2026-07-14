<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $commande->id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 6mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f5f5f5;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
        }

        .ticket {
            width: 340px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 16px 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            color: #000;
        }

        .center { text-align: center; }
        .title { font-size: 18px; font-weight: bold; }
        .sub { font-size: 11px; color: #000; margin-top: 3px; }
        .sep { border: none; border-top: 1px dashed #000; margin: 8px 0; }
        .sep-strong { border: none; border-top: 2px solid #000; margin: 8px 0; }
        .row {
            display: grid;
            grid-template-columns: minmax(74px, 84px) minmax(0, 1fr);
            align-items: start;
            gap: 10px;
            margin: 4px 0;
            color: #000;
        }
        .row > span:first-child {
            min-width: 0;
            overflow-wrap: break-word;
        }
        .row > span:last-child {
            min-width: 0;
            text-align: right;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .label { color: #000; font-size: 11px; }
        .item { margin: 6px 0; }
        .item .row {
            grid-template-columns: minmax(0, 1fr) 86px;
        }
        .item-note { font-size: 11px; color: #000; padding-left: 14px; font-style: italic; }
        .total { font-size: 16px; font-weight: bold; }
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            justify-content: center;
            font-family: sans-serif;
        }
        .button {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
        }
        .button-primary { background: #059669; color: #fff; }
        .button-secondary { background: #e5e7eb; color: #111827; }

        @media print {
            body {
                background: #fff;
                padding: 0;
                color: #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .ticket {
                width: 68mm;
                box-shadow: none;
                margin: 0;
                max-width: none;
                padding: 8px 6px;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $orders = $orders ?? collect([$commande]);
        $primaryOrder = $orders->first() ?? $commande;
        $displayTable = $primaryOrder?->table;
        $displayUser = $primaryOrder?->user;
        $ticketDetails = $orders->flatMap(fn($order) => $order->details)->values();
        $totalAmount = $totalAmount ?? (float) $orders->sum(fn($order) => (float) $order->total);
        $methodLabels = [
            'cash' => 'Espèces',
            'carte' => 'Carte bancaire',
            'mixte' => 'Mixte (Espèces + Carte)',
        ];
        $combinedOrderRefs = $orders->pluck('id')->map(fn($id) => '#' . $id)->implode(', ');
    @endphp

    <div class="ticket">
        <div class="center title">Techmizane Cash</div>
        <div class="center sub">Restaurant & Café</div>
        <div class="center sub">{{ now()->format('d/m/Y H:i') }}</div>

        <hr class="sep-strong">

        <div class="row"><span class="label">Commandes</span><span>{{ $combinedOrderRefs }}</span></div>
        <div class="row"><span class="label">Table</span><span>{{ $displayTable->name ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">Serveur</span><span>{{ $displayUser->name ?? 'N/A' }}</span></div>

        <hr class="sep">

        @foreach($ticketDetails as $detail)
            <div class="item">
                <div class="row">
                    <span>{{ $detail->quantity }}x {{ $detail->produit->name }}</span>
                    <span>{{ number_format($detail->price * $detail->quantity, 2) }} DH</span>
                </div>
                @if($detail->notes)
                    <div class="item-note">{{ $detail->notes }}</div>
                @endif
            </div>
        @endforeach

        <hr class="sep-strong">

        <div class="row total"><span>TOTAL</span><span>{{ number_format($totalAmount, 2) }} DH</span></div>
        <div class="row"><span class="label">Règlement</span><span>{{ $methodLabels[$paymentMethod] ?? ucfirst($paymentMethod) }}</span></div>
        @if($changeAmount > 0)
            <div class="row"><span class="label">Monnaie rendue</span><span>{{ number_format($changeAmount, 2) }} DH</span></div>
        @endif

        <hr class="sep">

        <div class="center sub">Merci de votre visite !</div>
    </div>

    <div class="actions">
        <button class="button button-primary" onclick="window.print()">Imprimer</button>
        <button class="button button-secondary" onclick="window.location.href = @js($redirectUrl)">Retour</button>
    </div>

    <script>
        let redirected = false;

        function goBack() {
            if (redirected) {
                return;
            }

            redirected = true;
            window.location.href = @js($redirectUrl);
        }

        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 150);
            setTimeout(goBack, 4000);
        });

        window.addEventListener('afterprint', goBack);
    </script>
</body>
</html>