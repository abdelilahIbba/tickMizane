<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cuisine #{{ $order_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .label {
            font-weight: bold;
        }
        .items-section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }
        .item {
            margin-bottom: 8px;
        }
        .item-header {
            font-weight: bold;
            font-size: 14px;
        }
        .item-notes {
            font-style: italic;
            margin-left: 20px;
            color: #333;
        }
        .notes-section {
            background: #f5f5f5;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #000;
        }
        .timestamp {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="title">CUISINE</div>
        <div>Commande #{{ $order_id }}</div>
    </div>

    <!-- Order Info -->
    <div class="info-section">
        <div class="info-row">
            <span class="label">TABLE:</span>
            <span style="font-size: 16px; font-weight: bold;">{{ $table_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Nom:</span>
            <span>{{ $table_name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Serveur:</span>
            <span>{{ $waiter_name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Heure:</span>
            <span>{{ $created_at->format('H:i:s') }}</span>
        </div>
    </div>

    <!-- Order Items -->
    <div class="items-section">
        <div style="font-weight: bold; margin-bottom: 8px; text-decoration: underline;">ARTICLES:</div>
        
        @foreach($items as $item)
        <div class="item">
            <div class="item-header">
                {{ $item['quantity'] }}x {{ strtoupper($item['product_name']) }}
            </div>
            @if($item['notes'])
            <div class="item-notes">
                → {{ $item['notes'] }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Waiter Notes -->
    @if($waiter_notes)
    <div class="notes-section">
        <div class="notes-title">NOTES GÉNÉRALES:</div>
        <div>{{ $waiter_notes }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">
            BON APPÉTIT !
        </div>
        <div class="timestamp">
            Imprimé le {{ now()->format('d/m/Y à H:i:s') }}
        </div>
    </div>
</body>
</html>
