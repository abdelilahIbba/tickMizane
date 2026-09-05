<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Cuisine #{{ $order_id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #000 !important;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.3;
            padding: 4px 6px;
            width: 70mm;
            max-width: 70mm;
            margin: 0 auto;
            color: #000;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 3px;
        }
        .info-section {
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .info-row > span:last-child {
            padding-right: 1.5mm;
        }
        .label {
            font-weight: bold;
        }
        .items-section {
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 2px dashed #000;
        }
        .item {
            margin-bottom: 6px;
        }
        .item-header {
            font-weight: 800;
            font-size: 13.5px;
        }
        .item-notes {
            font-style: italic;
            margin-left: 14px;
            color: #000;
            font-weight: 700;
        }
        .notes-section {
            background: #fff;
            padding: 6px;
            margin-bottom: 8px;
            border: 1.5px dashed #000;
            color: #000;
        }
        .notes-title {
            font-weight: 800;
            margin-bottom: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 2px dashed #000;
        }
        .timestamp {
            font-size: 10px;
            color: #000;
            font-weight: 700;
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
