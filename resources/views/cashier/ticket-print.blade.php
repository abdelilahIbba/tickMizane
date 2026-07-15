<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $ticketType === 'summary' ? 'resume' : 'detaille' }}</title>
    <style>
        @page { margin: 8mm; }
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 16px; }
        .ticket { max-width: 360px; margin: 0 auto; border: 1px dashed #666; padding: 14px; }
        .center { text-align: center; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 6px; }
        .sub { font-size: 12px; color: #444; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; margin: 4px 0; font-size: 13px; }
        .sep { border-top: 1px dashed #999; margin: 10px 0; }
        .total { font-weight: bold; font-size: 16px; }
        .small { font-size: 11px; color: #555; }
        .actions { margin-top: 14px; text-align: center; }
        .btn { border: 0; padding: 8px 12px; margin: 0 6px; border-radius: 6px; cursor: pointer; }
        .btn-print { background: #111; color: #fff; }
        .btn-back { background: #ddd; color: #111; text-decoration: none; display: inline-block; }

        @media print {
            .actions { display: none; }
            body { padding: 0; }
            .ticket { border: 0; }
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
