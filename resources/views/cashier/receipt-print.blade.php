<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $commande->id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f0ede8;
            font-family: 'Lato', 'Courier New', Courier, monospace;
            color: #1a1a1a;
        }

        /* ===== TICKET CONTAINER ===== */
        .ticket {
            width: 340px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 0 0 18px 0;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
            color: #1a1a1a;
            overflow: hidden;
        }

        /* ===== HEADER BAND ===== */
        .ticket-header {
            background: #ffffff;
            padding: 24px 12px 18px;
            text-align: center;
            color: #1a1a1a;
            border-bottom: 1.5px solid #1a1a1a;
        }

        .logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        .logo-wrap img {
            width: 85px;
            height: 85px;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        .restaurant-name {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: #1a1a1a;
            margin: 12px 0 4px 0;
            line-height: 1.2;
        }

        .restaurant-tagline {
            font-family: 'Lato', sans-serif;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }

        .restaurant-phone {
            font-family: 'Lato', sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: 1px;
            margin: 0;
        }

        .restaurant-phone::before {
            content: '☎ ';
            font-size: 11px;
        }

        /* ===== BODY ===== */
        .ticket-body {
            padding: 12px 12px 0;
        }

        .ticket-date {
            font-family: 'Lato', sans-serif;
            font-size: 10px;
            font-weight: 300;
            color: #777;
            text-align: center;
            margin: 8px 0 4px;
            letter-spacing: 0.5px;
        }

        .center { text-align: center; }
        .sub { font-size: 10.5px; color: #555; margin-top: 3px; font-family: 'Lato', sans-serif; }

        /* Dividers */
        .sep {
            border: none;
            border-top: 1px dashed #bbb;
            margin: 8px 0;
        }
        .sep-strong {
            border: none;
            border-top: 1.5px solid #1a1a1a;
            margin: 10px 0;
        }
        .sep-gold {
            border: none;
            border-top: 1px solid #c9a84c;
            margin: 10px 0;
        }

        /* Rows */
        .row {
            display: grid;
            grid-template-columns: minmax(74px, 84px) minmax(0, 1fr);
            align-items: start;
            gap: 10px;
            margin: 4px 0;
            font-family: 'Lato', sans-serif;
            font-size: 11px;
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
        .label {
            color: #777;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        /* Items */
        .item { margin: 5px 0; font-family: 'Lato', sans-serif; font-size: 11.5px; }
        .item .row {
            grid-template-columns: minmax(0, 1fr) 86px;
        }
        .item .qty {
            color: #c9a84c;
            font-weight: 700;
        }
        .item-note { font-size: 10px; color: #888; padding-left: 14px; font-style: italic; }

        /* Total */
        .total-row {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            align-items: center;
            margin: 6px 0 4px;
        }
        .total-label {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .total-amount {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 700;
            text-align: right;
        }

        /* Footer */
        .ticket-footer {
            text-align: center;
            padding: 10px 12px 0;
        }
        .footer-msg {
            font-family: 'Playfair Display', serif;
            font-size: 12px;
            font-style: italic;
            color: #555;
            margin: 4px 0 2px;
        }
        .footer-sub {
            font-family: 'Lato', sans-serif;
            font-size: 9px;
            color: #999;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Action buttons (screen only) */
        .actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            justify-content: center;
            font-family: 'Lato', sans-serif;
        }
        .button {
            border: 0;
            border-radius: 10px;
            padding: 10px 18px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .button-primary { background: #1a1a1a; color: #e8d9b5; }
        .button-secondary { background: #e5e7eb; color: #111827; }

        /* ===== PRINT OVERRIDES — Thermal Printer Optimized ===== */
        @media print {

            /* ── Page & body ── */
            body {
                background: #fff !important;
                padding: 0 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* ── Ticket container ── */
            .ticket {
                width: 68mm;
                box-shadow: none;
                margin: 0;
                max-width: none;
                padding-bottom: 10px;
                background: #fff !important;
            }

            /* ── HEADER: clean white background, black text ── */
            .ticket-header {
                padding: 16px 10px 12px;
                background: #fff !important;
                color: #000 !important;
                border-bottom: 1.5px solid #000 !important;
            }

            .logo-wrap img {
                width: 80px;
                height: 80px;
                filter: none !important;
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }

            .restaurant-name {
                font-size: 22px !important;
                font-weight: 800 !important;
                letter-spacing: 1.5px !important;
                color: #000 !important;
            }

            .restaurant-tagline {
                font-size: 11px !important;
                font-weight: 800 !important;
                letter-spacing: 1px !important;
                color: #000 !important;
            }

            .restaurant-phone {
                font-size: 12px !important;
                font-weight: 700 !important;
                color: #000 !important;
            }

            /* ── BODY: force everything to pure black ── */
            .ticket-body,
            .ticket-body * {
                color: #000 !important;
            }

            /* Date */
            .ticket-date {
                color: #000 !important;
            }

            /* Labels (COMMANDES, TABLE, SERVEUR…) */
            .label {
                color: #000 !important;
            }

            /* Item quantity (was gold #c9a84c — doesn't print on thermal) */
            .item .qty {
                color: #000 !important;
                font-weight: 700;
            }

            /* Item notes */
            .item-note {
                color: #000 !important;
            }

            /* Separators — simple black lines */
            .sep {
                border-top: 1px dashed #000 !important;
            }

            .sep-strong {
                border-top: 1.5px solid #000 !important;
            }

            .sep-gold {
                border-top: 1px solid #000 !important;
            }

            /* Total */
            .total-label,
            .total-amount {
                color: #000 !important;
            }

            /* Footer */
            .ticket-footer,
            .footer-msg,
            .footer-sub {
                color: #000 !important;
            }

            /* Hide action buttons */
            .actions {
                display: none !important;
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
        $totalAmount      = $totalAmount      ?? (float) $orders->sum(fn($order) => (float) $order->total);
        $discountPercent  = $discountPercent  ?? 0;
        $discountAmount   = $discountAmount   ?? 0;
        $netAmount        = ($discountAmount > 0) ? round($totalAmount - $discountAmount, 2) : $totalAmount;
        $methodLabels = [
            'cash' => 'Espèces',
            'carte' => 'Carte bancaire',
            'mixte' => 'Mixte (Espèces + Carte)',
        ];
        $combinedOrderRefs = $orders->pluck('id')->map(fn($id) => '#' . $id)->implode(', ');
    @endphp

    <div class="ticket">

        {{-- ===== HEADER ===== --}}
        <div class="ticket-header">
            <div class="logo-wrap">
                @php
                    // Embed the logo as base64 so it always shows during print
                    // (no external HTTP request needed — works offline & in print dialogs)
                    // HQ 300x300 transparent PNG — inverted to white in dark header
                    $logoPath = public_path('logo-hq.png');
                    $logoSrc = file_exists($logoPath)
                        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                        : asset('logo.svg');
                @endphp
                <img src="{{ $logoSrc }}" alt="Oussoul House Logo">
            </div>
            <div class="restaurant-name">Oussoul House</div>
            <div class="restaurant-tagline">RESTAURANT & HOTEL</div>
            <div class="restaurant-phone">06-60-43-27-86</div>
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
                <div class="item">
                    <div class="row">
                        <span><span class="qty">{{ $detail->quantity }}×</span> {{ $detail->produit->name }}</span>
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
                <span class="total-amount">{{ number_format($totalAmount, 2) }} DH</span>
            </div>

            @if($discountPercent > 0)
            <div class="row" style="margin-top:4px;">
                <span class="label">Remise</span>
                <span style="color:#c0392b;">-{{ number_format($discountAmount, 2) }} DH ({{ $discountPercent }}%)</span>
            </div>
            <div class="total-row" style="border-top:1px dashed #ccc; margin-top:6px; padding-top:6px;">
                <span class="total-label">NET À PAYER</span>
                <span class="total-amount">{{ number_format($netAmount, 2) }} DH</span>
            </div>
            @endif

            <div class="row" style="margin-top:6px;">
                <span class="label">Règlement</span>
                <span>{{ $methodLabels[$paymentMethod] ?? ucfirst($paymentMethod) }}</span>
            </div>
            @if($changeAmount > 0)
                <div class="row">
                    <span class="label">Monnaie rendue</span>
                    <span>{{ number_format($changeAmount, 2) }} DH</span>
                </div>
            @endif

        </div>{{-- /.ticket-body --}}

        <hr class="sep" style="margin: 12px 12px 0;">

        {{-- ===== FOOTER ===== --}}
        <div class="ticket-footer">
            <div class="footer-msg">Merci de votre visite !</div>
            <div class="footer-sub">Oussoul House — 06-60-43-27-86</div>
        </div>

    </div>{{-- /.ticket --}}

    <div class="actions">
        <button class="button button-primary" onclick="window.print()">🖨 Imprimer</button>
        <button class="button button-secondary" onclick="window.location.href = @js($redirectUrl)">← Retour</button>
    </div>

    <script>
        let redirected = false;

        function goBack() {
            if (redirected) return;
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