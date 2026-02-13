<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu #{{ $commande->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-row span {
            display: inline-block;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .items-table th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .items-table .qty {
            text-align: center;
            width: 40px;
        }
        .items-table .price {
            text-align: right;
            width: 80px;
        }
        .total-section {
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
            color: #666;
        }
        .notes {
            background: #f9f9f9;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Techmizane Cash</h1>
        <p>Restaurant & Café</p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row">
            <span>Reçu N°:</span>
            <span>#{{ str_pad($commande->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span>Table:</span>
            <span>{{ $commande->table->name ?? 'N/A' }} (N°{{ $commande->table->numero ?? 'N/A' }})</span>
        </div>
        <div class="info-row">
            <span>Serveur:</span>
            <span>{{ $commande->user->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span>Date commande:</span>
            <span>{{ $commande->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Article</th>
                <th class="qty">Qté</th>
                <th class="price">Prix U.</th>
                <th class="price">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->details as $detail)
            <tr>
                <td>
                    {{ $detail->produit->name }}
                    @if($detail->notes)
                    <br><small style="color: #888;">{{ $detail->notes }}</small>
                    @endif
                </td>
                <td class="qty">{{ $detail->quantity }}</td>
                <td class="price">{{ number_format($detail->price, 2) }}</td>
                <td class="price">{{ number_format($detail->price * $detail->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($commande->waiter_notes)
    <div class="notes">
        <strong>Notes:</strong> {{ $commande->waiter_notes }}
    </div>
    @endif

    <div class="total-section">
        <div class="total-row">
            <span>TOTAL TTC</span>
            <span>{{ number_format($commande->total, 2) }} DH</span>
        </div>
    </div>

    <div class="footer">
        <p>Merci de votre visite!</p>
        <p>À bientôt</p>
        <p style="font-size: 10px; margin-top: 10px;">
            © {{ date('Y') }} Techmizane Cash
        </p>
    </div>
</body>
</html>
