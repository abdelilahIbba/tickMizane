<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $ticketType === 'summary' ? 'resume' : 'detaille' }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 12px 8px;
            background: #f0ede8;
        }
        .ticket {
            width: 320px;
            max-width: 100%;
            margin: 0 auto;
            border: 2px dashed #000;
            padding: 12px 10px;
            background: #fff;
            color: #000;
        }
        .center { text-align: center; }
        .title { font-size: 18px; font-weight: 800; margin-bottom: 4px; color: #000; }
        .sub { font-size: 12px; color: #000; font-weight: 600; margin-bottom: 6px; }
        .row { display: flex; justify-content: space-between; margin: 3px 0; font-size: 12px; color: #000; }
        .row > span:last-child { font-weight: 700; padding-right: 2px; }
        .sep { border: none; border-top: 1.5px dashed #000; margin: 6px 0; }
        .total { font-weight: 800; font-size: 15px; color: #000; }
        .small { font-size: 11px; color: #000; font-weight: 600; }
        .actions { margin-top: 14px; text-align: center; }
        .btn { border: 0; padding: 8px 14px; margin: 0 6px; border-radius: 6px; cursor: pointer; font-weight: 700; }
        .btn-print { background: #000; color: #fff; }
        .btn-back { background: #ddd; color: #000; text-decoration: none; display: inline-block; }

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
            }
            .ticket {
                width: 70mm !important;
                max-width: 70mm !important;
                margin: 0 auto !important;
                padding: 2mm 3mm !important;
                border: none !important;
                height: auto !important;
                min-height: 0 !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .row > span:last-child {
                padding-right: 1.5mm !important;
                font-weight: 800 !important;
            }
            .actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center title">TechMizane Cash</div>
        <div class="center sub">Ticket {{ $ticketType === 'summary' ? 'resume du CA' : 'detail des ventes' }}</div>
        <div class="center small">Date: {{ \Carbon\Carbon::parse($ticketDate)->format('d/m/Y') }}</div>
        <div class="sep"></div>

        <div class="row">
            <span>Nombre de ventes</span>
            <span>{{ $orders->count() }}</span>
        </div>
        <div class="row total">
            <span>Total CA</span>
            <span>{{ number_format($totalRevenue, 2) }} DH</span>
        </div>

        @if($ticketType === 'detailed')
        <div class="sep"></div>
        @forelse($productSales as $productSale)
        <div class="row">
            <span>{{ $productSale->produit?->name ?? 'Produit supprime' }}</span>
            <span>x{{ (int) $productSale->total_quantity }}</span>
        </div>
        <div class="small">Montant cumule: {{ number_format((float) $productSale->total_amount, 2) }} DH</div>
        @empty
        <div class="small center">Aucune vente pour cette date.</div>
        @endforelse
        @endif
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">Imprimer</button>
        <a href="{{ route('cashier.tickets') }}" class="btn btn-back">Retour</a>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
