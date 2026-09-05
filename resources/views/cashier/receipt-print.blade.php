<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $commande->id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 16px 8px;
            background: #f0ede8;
            font-family: 'Lato', 'Courier New', Courier, monospace;
            color: #000;
        }

        /* ===== TICKET CONTAINER ===== */
        .ticket {
            width: 320px;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 0 0 10px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            color: #000;
            overflow: hidden;
        }

        /* ===== HEADER BAND ===== */
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

        /* ===== BODY ===== */
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

        .center { text-align: center; }
        .sub { font-size: 10.5px; color: #000; font-weight: 600; margin-top: 3px; font-family: 'Lato', sans-serif; }

        /* Dividers */
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

        /* Rows */
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

        /* Items */
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

        /* Multilingual item name */
        .item-name-fr  { display: block; font-size: 11.5px; font-weight: 700; color: #000; line-height: 1.3; }
        .item-name-en  { display: block; font-size: 10px; color: #000; font-style: italic; font-weight: 600; line-height: 1.3; margin-top: 1px; }
        .item-name-ar  { display: block; font-size: 11.5px; color: #000; font-weight: 700; direction: rtl; text-align: right;
                         font-family: 'Amiri', 'Scheherazade New', 'Traditional Arabic', serif;
                         line-height: 1.4; margin-top: 1px; }

        /* Total */
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

        /* Footer */
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

        /* Action buttons (screen only) */
        .actions {
            margin-top: 16px;
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
        .button-primary { background: #000; color: #fff; }
        .button-secondary { background: #e5e7eb; color: #000; }

        /* ===== PRINT OVERRIDES — Thermal Printer Optimized ===== */
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

            /* ── Page & body: Zero margins, auto height ── */
            html, body {
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* ── Ticket container: 70mm width safe zone for 80mm roll ── */
            .ticket {
                width: 70mm !important;
                max-width: 70mm !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                padding: 0 1.5mm 1mm 1.5mm !important;
                background: #fff !important;
                color: #000 !important;
                height: auto !important;
                min-height: 0 !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            /* ── HEADER: Compact, pure black ── */
            .ticket-header {
                padding: 4px 2px 6px !important;
                background: #fff !important;
                color: #000 !important;
                border-bottom: 2px solid #000 !important;
            }

            .logo-wrap {
                margin-bottom: 6px !important;
            }

            .logo-wrap img {
                width: 75px !important;
                height: 75px !important;
                filter: none !important;
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }

            .restaurant-name {
                font-size: 20px !important;
                font-weight: 800 !important;
                letter-spacing: 1px !important;
                color: #000 !important;
                margin: 6px 0 2px 0 !important;
            }

            .restaurant-tagline {
                font-size: 10px !important;
                font-weight: 800 !important;
                letter-spacing: 1px !important;
                color: #000 !important;
                margin: 0 0 4px 0 !important;
            }

            .restaurant-phone {
                font-size: 11.5px !important;
                font-weight: 700 !important;
                color: #000 !important;
            }

            /* ── BODY: force everything to pure black ── */
            .ticket-body,
            .ticket-body * {
                color: #000 !important;
            }

            .ticket-body {
                padding: 4px 1mm 0 1mm !important;
            }

            /* Date */
            .ticket-date {
                font-size: 10px !important;
                font-weight: 700 !important;
                color: #000 !important;
                margin: 4px 0 2px !important;
            }

            /* Labels (COMMANDES, TABLE, SERVEUR…) */
            .label {
                color: #000 !important;
                font-weight: 800 !important;
            }

            .row {
                grid-template-columns: minmax(62px, 72px) minmax(0, 1fr) !important;
                gap: 4px !important;
                margin: 2px 0 !important;
            }

            .row > span:last-child {
                padding-right: 1.5mm !important;
                color: #000 !important;
                font-weight: 700 !important;
            }

            .item {
                margin: 3px 0 !important;
            }

            .item .row {
                grid-template-columns: minmax(0, 1fr) 68px !important;
                gap: 4px !important;
            }

            .item .row > span:last-child {
                padding-right: 1.5mm !important;
                color: #000 !important;
                font-weight: 800 !important;
            }

            /* Item quantity */
            .item .qty {
                color: #000 !important;
                font-weight: 800 !important;
            }

            .item-name-fr,
            .item-name-en,
            .item-name-ar {
                color: #000 !important;
            }

            /* Item notes */
            .item-note {
                color: #000 !important;
                font-weight: 600 !important;
                padding-left: 10px !important;
            }

            /* Separators — simple solid/dashed black lines */
            .sep {
                border-top: 1px dashed #000 !important;
                margin: 4px 0 !important;
            }

            .sep-strong {
                border-top: 2px solid #000 !important;
                margin: 6px 0 !important;
            }

            .sep-gold {
                border-top: 1.5px solid #000 !important;
                margin: 5px 0 !important;
            }

            /* Total */
            .total-row {
                margin: 4px 0 2px !important;
            }

            .total-label {
                font-size: 16px !important;
                font-weight: 800 !important;
                color: #000 !important;
            }

            .total-amount {
                font-size: 17px !important;
                font-weight: 800 !important;
                color: #000 !important;
                padding-right: 1.5mm !important;
            }

            /* Footer */
            .ticket-footer {
                padding: 4px 2px 0 !important;
                color: #000 !important;
            }

            .footer-msg {
                font-size: 11.5px !important;
                font-weight: 700 !important;
                color: #000 !important;
                margin: 2px 0 1px !important;
            }

            .footer-sub {
                font-size: 9px !important;
                font-weight: 700 !important;
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
                <span class="total-amount">{{ number_format($totalAmount, 2) }} DH</span>
            </div>

            @if($discountPercent > 0)
            <div class="row" style="margin-top:4px;">
                <span class="label">Remise</span>
                <span style="color:#000; font-weight:700;">-{{ number_format($discountAmount, 2) }} DH ({{ $discountPercent }}%)</span>
            </div>
            <div class="total-row" style="border-top:1px dashed #000; margin-top:6px; padding-top:6px;">
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

        <hr class="sep" style="margin: 6px 8px 0;">

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