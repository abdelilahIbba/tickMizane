<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ticketTitle ?? 'Ticket cuisine' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
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

        .ticket-label {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 10px;
            background: #fff;
            color: #000;
            border: 2px solid #000;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 999px;
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

        .item {
            margin: 4px 0;
            font-family: 'Lato', sans-serif;
            font-size: 11.5px;
            color: #000;
        }

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

        .item-note {
            font-size: 10px;
            color: #000;
            padding-left: 12px;
            font-style: italic;
            font-weight: 600;
        }

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

        .notes-box {
            margin-top: 8px;
            background: #fff;
            border: 1.5px dashed #000;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 10.5px;
            color: #000;
            font-weight: 600;
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

            .ticket-label {
                background: #fff !important;
                color: #000 !important;
                border: 2px solid #000 !important;
                font-size: 10px !important;
                font-weight: 800 !important;
                padding: 3px 8px !important;
            }

            .ticket-body {
                padding: 4px 1mm 0 1mm !important;
                color: #000 !important;
            }

            .ticket-date {
                font-size: 10px !important;
                font-weight: 700 !important;
                color: #000 !important;
                margin: 4px 0 2px !important;
            }

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

            .item .qty {
                color: #000 !important;
                font-weight: 800 !important;
            }

            .item-note {
                color: #000 !important;
                font-weight: 600 !important;
                padding-left: 10px !important;
            }

            .sep {
                border-top: 1px dashed #000 !important;
                margin: 4px 0 !important;
            }

            .sep-strong {
                border-top: 2px solid #000 !important;
                margin: 6px 0 !important;
            }

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

            .notes-box {
                background: #fff !important;
                border: 1.5px dashed #000 !important;
                color: #000 !important;
                padding: 4px 6px !important;
                margin-top: 6px !important;
            }

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
        }
    </style>
</head>
<body>
    @php
        $displayTable = $commande->table;
        $displayUser = $commande->user;
        $ticketDetails = $commande->details
            ->filter(fn($detail) => $detail->produit?->isKitchenActive() ?? true)
            ->values();
        $ticketDate = $commande->created_at ?? now();
        $logoPath = public_path('logo-hq.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : asset('logo.svg');
    @endphp

    <div class="ticket">
        <div class="ticket-header">
            <div class="logo-wrap">
                <img src="{{ $logoSrc }}" alt="Oussoul House Logo">
            </div>
            <div class="restaurant-name">Oussoul House</div>
            <div class="restaurant-tagline">RESTAURANT & HOTEL</div>
            <div class="restaurant-phone">06-60-43-27-86</div>
            <div class="ticket-label">{{ $ticketLabel ?? 'TICKET' }}</div>
        </div>

        <div class="ticket-body">
            <div class="ticket-date">{{ $ticketDate->format('d/m/Y  ·  H:i') }}</div>
            <hr class="sep">

            <div class="row">
                <span class="label">Commande</span>
                <span>#{{ $commande->id }}</span>
            </div>
            <div class="row">
                <span class="label">Table</span>
                <span>{{ $displayTable?->name ?? $displayTable?->numero ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">Serveur</span>
                <span>{{ $displayUser?->name ?? 'Système' }}</span>
            </div>
            <div class="row">
                <span class="label">Type</span>
                <span>{{ $ticketType ?? 'kitchen' }}</span>
            </div>

            <hr class="sep-strong">

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

            @if($commande->waiter_notes)
                <div class="notes-box">
                    <strong>Notes:</strong> {{ $commande->waiter_notes }}
                </div>
            @endif
        </div>

        <div class="ticket-footer">
            <div class="footer-msg">{{ $ticketSubtitle ?? 'Merci de votre visite !' }}</div>
            <div class="footer-sub">Oussoul House — 06-60-43-27-86</div>
        </div>
    </div>
</body>
</html>
