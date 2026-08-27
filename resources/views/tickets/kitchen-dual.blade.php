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

        .ticket-label {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            background: #111827;
            color: #e5d7a3;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border-radius: 999px;
        }

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

        .item {
            margin: 5px 0;
            font-family: 'Lato', sans-serif;
            font-size: 11.5px;
        }

        .item .row {
            grid-template-columns: minmax(0, 1fr) 86px;
        }

        .item .qty {
            color: #c9a84c;
            font-weight: 700;
        }

        .item-note {
            font-size: 10px;
            color: #888;
            padding-left: 14px;
            font-style: italic;
        }

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

        .notes-box {
            margin-top: 10px;
            background: #f8f8f8;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            font-size: 10px;
            color: #555;
        }

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

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .ticket {
                width: 68mm;
                box-shadow: none;
                margin: 0;
                max-width: none;
                padding-bottom: 10px;
                background: #fff !important;
            }

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

            .restaurant-tagline,
            .restaurant-phone,
            .ticket-date,
            .label,
            .item-note,
            .total-label,
            .total-amount,
            .footer-msg,
            .footer-sub,
            .ticket-body,
            .ticket-body * {
                color: #000 !important;
            }

            .item .qty {
                color: #000 !important;
                font-weight: 700;
            }

            .sep {
                border-top: 1px dashed #000 !important;
            }

            .sep-strong {
                border-top: 1.5px solid #000 !important;
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
