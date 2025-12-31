<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Caisse</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 300px;
            margin: 0 auto;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; }
        .logo { width: 80px; margin: 10px auto; display: block; }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="text-center">
        @if(settings('general.site_logo'))
            <img src="{{ Storage::url(settings('general.site_logo')) }}" class="logo" alt="Logo">
        @endif
        
        <h2 class="bold">{{ settings('general.site_name', 'Restaurant') }}</h2>
        <p>{{ settings('general.address', '') }}</p>
        <p>Tél: {{ settings('general.phone', '') }}</p>
        @if(settings('general.email'))
            <p>{{ settings('general.email') }}</p>
        @endif
    </div>

    <div class="divider"></div>

    <!-- ORDER INFO -->
    <table>
        <tr>
            <td>Ticket N°:</td>
            <td class="text-right bold">#{{ $order->id }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if($order->table)
        <tr>
            <td>Table:</td>
            <td class="text-right">{{ $order->table->name }}</td>
        </tr>
        @endif
        @if($order->user)
        <tr>
            <td>Serveur:</td>
            <td class="text-right">{{ $order->user->name }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- ITEMS -->
    <table>
        <thead>
            <tr>
                <th align="left">Article</th>
                <th align="right">Qté</th>
                <th align="right">Prix</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td align="right">{{ $item->quantity }}</td>
                <td align="right">{{ number_format($item->price * $item->quantity, 2) }} {{ settings('pos.currency', 'DH') }}</td>
            </tr>
            @if($item->options)
                <tr>
                    <td colspan="3" style="font-size: 10px; padding-left: 10px;">
                        <em>{{ is_array($item->options) ? implode(', ', array_column($item->options, 'name')) : $item->options }}</em>
                    </td>
                </tr>
            @endif
            @if($item->note)
                <tr>
                    <td colspan="3" style="font-size: 10px; padding-left: 10px; color: #666;">
                        Note: {{ $item->note }}
                    </td>
                </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- TOTAL -->
    <table>
        <tr>
            <td>Sous-total:</td>
            <td class="text-right">{{ number_format($order->total_amount, 2) }} {{ settings('pos.currency', 'DH') }}</td>
        </tr>
        @if(settings('pos.default_tax_rate') > 0)
        <tr>
            <td>TVA ({{ settings('pos.default_tax_rate') }}%):</td>
            <td class="text-right">{{ number_format($order->total_amount * settings('pos.default_tax_rate') / 100, 2) }} {{ settings('pos.currency', 'DH') }}</td>
        </tr>
        @endif
        <tr class="bold" style="font-size: 14px;">
            <td>TOTAL:</td>
            <td class="text-right">{{ number_format($order->total_amount * (1 + settings('pos.default_tax_rate', 0) / 100), 2) }} {{ settings('pos.currency', 'DH') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- PAYMENT INFO -->
    @if($payment ?? null)
    <table>
        <tr>
            <td>Mode de paiement:</td>
            <td class="text-right">{{ ucfirst($payment->method) }}</td>
        </tr>
        @if($payment->method === 'cash' && $payment->amount_tendered)
        <tr>
            <td>Montant reçu:</td>
            <td class="text-right">{{ number_format($payment->amount_tendered, 2) }} {{ settings('pos.currency', 'DH') }}</td>
        </tr>
        <tr>
            <td>Rendu monnaie:</td>
            <td class="text-right">{{ number_format($payment->change_amount, 2) }} {{ settings('pos.currency', 'DH') }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>
    @endif

    <!-- FOOTER -->
    <div class="text-center">
        <p>{{ settings('general.receipt_footer', 'Merci de votre visite !') }}</p>
        
        @if(settings('general.wifi_ssid'))
        <div class="divider"></div>
        <p class="bold">WIFI GRATUIT</p>
        <p>Réseau: <span class="bold">{{ settings('general.wifi_ssid') }}</span></p>
        <p>Mot de passe: <span class="bold">{{ settings('general.wifi_password') }}</span></p>
        @endif
        
        @if(settings('general.qr_code_url'))
        <div class="divider"></div>
        <p>Scannez pour nous évaluer:</p>
        <!-- QR Code can be generated with a package like simplesoftwareio/simple-qrcode -->
        @endif
    </div>

</body>
</html>
