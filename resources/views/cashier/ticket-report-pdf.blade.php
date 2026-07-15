<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport CA</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
        .header { margin-bottom: 18px; }
        .title { font-size: 18px; font-weight: bold; }
        .meta { color: #444; font-size: 11px; }
        .summary { margin: 12px 0 16px; padding: 10px; border: 1px solid #ccc; }
        .summary-row { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Rapport de chiffre d'affaires</div>
        <div class="meta">Periode: {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}</div>
        <div class="meta">Type: {{ $reportType === 'summary' ? 'Resume' : 'Detaille' }}</div>
        <div class="meta">Genere le: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <div class="summary-row"><strong>Nombre total de ventes:</strong> {{ $salesCount }}</div>
        <div class="summary-row"><strong>Chiffre d'affaires total:</strong> {{ number_format($totalRevenue, 2) }} DH</div>
    </div>

    @if($reportType === 'detailed')
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="right">Quantite vendue</th>
                <th class="right">Montant cumule</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productSales as $productSale)
            <tr>
                <td>{{ $productSale->produit?->name ?? 'Produit supprime' }}</td>
                <td class="right">{{ (int) $productSale->total_quantity }}</td>
                <td class="right">{{ number_format((float) $productSale->total_amount, 2) }} DH</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">Aucune vente trouvee sur cette periode.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</body>
</html>
