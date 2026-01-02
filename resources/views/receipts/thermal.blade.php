<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Police monospace pour alignement */
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 80mm; /* Largeur standard ticket */
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .info {
            font-size: 11px;
            margin-bottom: 5px;
        }
        .separator {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            border-bottom: 1px solid #000;
        }
        .qty { width: 15%; text-align: center; }
        .item { width: 60%; }
        .price { width: 25%; text-align: right; }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->site_logo)
            <img src="{{ storage_path('app/public/' . $settings->site_logo) }}" style="max-width: 50px;"><br>
        @endif
        <div class="title">{{ $settings->site_name }}</div>
        <div>{{ $settings->address }}</div>
        <div>{{ $settings->phone }}</div>
    </div>

    <div class="separator"></div>

    <div class="info">
        Ticket #: {{ $order->local_id }}<br>
        Date: {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Serveur: {{ $order->server->name ?? 'N/A' }}<br>
        Table: {{ $order->table->name ?? 'A Emporter' }}
    </div>

    <div class="separator"></div>

    <table>
        <thead>
            <tr>
                <th class="qty">Qté</th>
                <th class="item">Article</th>
                <th class="price">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="item">
                    {{ $item->product->name }}
                    @if($item->notes)
                        <br><small>({{ $item->notes }})</small>
                    @endif
                </td>
                <td class="price">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    <table>
        <tr class="total-row">
            <td colspan="2">TOTAL</td>
            <td class="right">{{ number_format($order->total_amount, 2) }} DH</td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->receipt_footer }}<br>
        @if($settings->wifi_ssid)
            Wifi: {{ $settings->wifi_ssid }} / Pass: {{ $settings->wifi_password }}
        @endif
        <br>
        *** MERCI DE VOTRE VISITE ***
    </div>
</body>
</html>
