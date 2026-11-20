
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>

    <style>
        /* Custom Fonts */
        @font-face {
            font-family: 'Geologica';
            src: url('{{ public_path('fonts/Geologica.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Geologica';
            src: url('{{ public_path('fonts/Geologica-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        /* Compact Page */
        @page {
            margin: 12px 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Geologica', sans-serif;
            color: #334155;
            font-size: 10px;
            line-height: 1.3;
            background-color: #ffffff;
        }

        .receipt-container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
        }

        strong, b {
            font-weight: bold;
            color: #0f172a;
        }

        /* Utilities */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #64748b; }
        .text-indigo { color: #4f46e5; }
        .text-amber { color: #d97706; }
        .text-dark { color: #0f172a; }

        /* Header */
        .header-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #4f46e5;
            border-radius: 6px;
            border: 1px solid #4338ca;
            overflow: hidden;
        }

        .header-box td {
            padding: 10px 12px;
            color: #ffffff;
        }

        .logo-container {
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 2px;
        }

        .logo-img {
            max-height: 35px;
            max-width: 180px;
            display: block;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .receipt-title {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #fcd34d;
        }

        .order-number {
            font-size: 9px;
            color: #e0e7ff;
            margin-top: 2px;
        }

        /* Details */
        .details-wrapper {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
        }

        .details-box {
            width: 50%;
            vertical-align: top;
            background-color: #f8fafc;
            border-radius: 5px;
            padding: 7px 9px;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #4f46e5;
        }

        .details-box.amber {
            border-left: 3px solid #f59e0b;
        }

        .section-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.7px;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 5px;
            display: block;
        }

        .section-label.amber {
            color: #d97706;
        }

        .person-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .muted {
            color: #64748b;
            font-size: 9px;
            margin-bottom: 1px;
        }

        /* Items Table */
        .items-wrapper {
            width: 100%;
            margin-bottom: 8px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            background-color: #eef2ff;
            color: #4f46e5;
            text-align: left;
            padding: 5px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #c7d2fe;
            border-right: 1px solid #c7d2fe;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .items-table td:last-child {
            border-right: none;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .product-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
        }

        .sku {
            font-size: 8px;
            color: #64748b;
            margin-top: 1px;
        }

        .attribute {
            font-size: 8px;
            color: #b45309;
            margin-top: 2px;
            background-color: #fef3c7;
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            border: 1px solid #fde68a;
        }

        /* Totals */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 8px;
        }

        .totals-box {
            width: 45%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        .totals-table td:last-child {
            border-right: none;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .grand-total-row td {
            background-color: #4f46e5;
            color: #ffffff !important;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 8px;
        }

        .grand-total-row strong {
            color: #ffffff !important;
        }

        /* Payment Details */
        .payment-wrapper {
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table th {
            background-color: #f8fafc;
            color: #475569;
            padding: 5px 6px;
            font-size: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            text-align: left;
        }

        .payment-table th:last-child {
            border-right: none;
        }

        .payment-table td {
            padding: 5px 6px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            color: #334155;
        }

        .payment-table td:last-child {
            border-right: none;
        }

        .payment-table tr:last-child td {
            border-bottom: none;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding: 8px;
            border: 1px dashed #cbd5e1;
            border-radius: 5px;
            text-align: center;
            color: #64748b;
            font-size: 8px;
            background-color: #f8fafc;
        }
    </style>
</head>

<body>
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Facades\Log;

        // 1. Logo Retrieval Logic
        $logoPath = $settings['store_logo'] ?? $settings['logo'] ?? null;

        // Remove "storage/" or "/storage/" prefix if present
        if ($logoPath) {
            $logoPath = preg_replace('#^/?storage/#', '', $logoPath);
        }

        $logoDataUri = null;

        // 2. Convert to Base64 for Dompdf reliability
        if ($logoPath) {
            try {
                if (Storage::exists($logoPath)) {
                    $bytes = Storage::get($logoPath);
                    $mime  = Storage::mimeType($logoPath) ?: 'image/png';
                    $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                } else {
                    Log::warning('Receipt logo missing on disk', ['path' => $logoPath]);
                }
            } catch (\Throwable $e) {
                Log::warning('Receipt logo embedding failed', [
                    'path'  => $logoPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. General Data Formatting
        $money = fn ($amount) => 'KSh ' . number_format((float) $amount, 2);

        $address = $order->shipping_address;
        if (is_array($address)) {
            $address = collect($address)
                ->flatten()
                ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
                ->map(fn ($value) => trim((string) $value))
                ->implode(', ');
        } elseif (!is_scalar($address)) {
            $address = '';
        }

        $formatAttributes = function ($attributes) {
            if (is_string($attributes)) return $attributes;
            if (!is_array($attributes)) return is_scalar($attributes) ? (string) $attributes : '';

            $formatValue = function ($value) use (&$formatValue) {
                if (is_array($value)) {
                    return collect($value)
                        ->map(fn ($nestedValue) => $formatValue($nestedValue))
                        ->filter(fn ($nestedValue) => $nestedValue !== '')
                        ->implode(', ');
                }

                return is_scalar($value) ? trim((string) $value) : '';
            };

            return collect($attributes)->map(function ($value, $key) use ($formatValue) {
                $formattedValue = $formatValue($value);
                if ($formattedValue === '') return null;

                if (is_string($key)) {
                    return ucfirst(str_replace('_', ' ', $key)) . ': ' . $formattedValue;
                }

                return $formattedValue;
            })->filter()->implode(' | ');
        };
    @endphp

    <div class="receipt-container">

        <!-- Header -->
        <table class="header-box">
            <tr>
                <td width="55%">
                    @if($logoDataUri)
                        <div class="logo-container">
                            <img src="{{ $logoDataUri }}" class="logo-img" alt="{{ $settings['store_name'] ?? 'Store' }} Logo">
                        </div>

                        <div style="font-size: 9px; color: #e0e7ff; margin-top: 2px;">
                            {{ $settings['store_tagline'] ?? '' }}
                        </div>
                    @else
                        <div class="store-name">{{ $settings['store_name'] ?? 'Store' }}</div>

                        @if(!empty($settings['store_tagline']))
                            <div style="font-size: 9px; color: #e0e7ff; margin-top: 2px;">
                                {{ $settings['store_tagline'] }}
                            </div>
                        @endif
                    @endif
                </td>

                <td width="45%" class="text-right" style="vertical-align: middle;">
                    <div class="receipt-title">Official Receipt</div>
                    <div class="order-number">Order: {{ $order->order_number }}</div>
                    <div class="order-number">Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>

        <!-- Details -->
        <table class="details-wrapper">
            <tr>
                <td class="details-box">
                    <span class="section-label">Billed From</span>

                    <div class="person-name">{{ $settings['store_name'] ?? 'Store' }}</div>

                    @if(!empty($settings['store_address']))
                        <div class="muted">{{ $settings['store_address'] }}</div>
                    @endif

                    @if(!empty($settings['store_email']))
                        <div class="muted">{{ $settings['store_email'] }}</div>
                    @endif

                    @if(!empty($settings['store_phone']))
                        <div class="muted">{{ $settings['store_phone'] }}</div>
                    @endif
                </td>

                <td class="details-box amber">
                    <span class="section-label amber">Billed To</span>

                    <div class="person-name">{{ $order->customer_name ?? 'Walk-in Customer' }}</div>

                    @if(!empty($address))
                        <div class="muted">{{ $address }}</div>
                    @endif

                    @if(!empty($order->customer_email))
                        <div class="muted">{{ $order->customer_email }}</div>
                    @endif

                    @if(!empty($order->customer_phone))
                        <div class="muted">{{ $order->customer_phone }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <div class="items-wrapper">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="40%">Item Description</th>
                        <th width="15%" class="text-center">Qty</th>
                        <th width="20%" class="text-right">Unit Price</th>
                        <th width="20%" class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>

                            <td>
                                <div class="product-name">{{ $item->product_name }}</div>

                                @if(!empty($item->sku))
                                    <div class="sku">SKU: {{ $item->sku }}</div>
                                @endif

                                @if(!empty($item->attributes))
                                    @php $formattedAttributes = $formatAttributes($item->attributes); @endphp

                                    @if($formattedAttributes !== '')
                                        <div class="attribute">{{ $formattedAttributes }}</div>
                                    @endif
                                @endif
                            </td>

                            <td class="text-center text-muted">{{ number_format($item->quantity, 0) }}</td>
                            <td class="text-right text-muted">{{ $money($item->unit_price) }}</td>
                            <td class="text-right font-bold text-dark">{{ $money($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <table class="totals-wrapper">
            <tr>
                <td width="55%"></td>

                <td width="45%" class="totals-box">
                    <table class="totals-table">
                        <tr>
                            <td class="text-muted">Subtotal</td>
                            <td class="text-right font-bold text-dark">{{ $money($order->subtotal) }}</td>
                        </tr>

                        <tr>
                            <td class="text-muted">Shipping Fee</td>
                            <td class="text-right font-bold text-dark">{{ $money($order->shipping_fee) }}</td>
                        </tr>

                        @if($order->discount_total > 0)
                            <tr>
                                <td class="text-amber">Discount</td>
                                <td class="text-right font-bold text-amber">-{{ $money($order->discount_total) }}</td>
                            </tr>
                        @endif

                        <tr class="grand-total-row">
                            <td class="text-left" style="text-transform: uppercase; letter-spacing: 1px;">Total</td>
                            <td class="text-right">{{ $money($order->grand_total) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Payment Details -->
        @if($order->payments->count() > 0)
            <div class="payment-wrapper">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th colspan="4" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left;">
                                Transaction Details
                            </th>
                        </tr>

                        <tr>
                            <th width="20%">Method</th>
                            <th width="30%">Reference</th>
                            <th width="25%">Date</th>
                            <th width="25%" class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->payments as $payment)
                            <tr>
                                <td class="text-muted" style="text-transform: capitalize;">
                                    {{ $payment->provider?->value ?? 'N/A' }}
                                </td>

                                <td class="text-muted">
                                    {{ $payment->provider_reference ?? 'N/A' }}
                                </td>

                                <td class="text-muted">
                                    {{ $payment->created_at->format('d M Y, h:i A') }}
                                </td>

                                <td class="text-right font-bold text-dark">
                                    {{ $money($payment->amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Thank you for shopping with us!<br>
            This is a computer-generated receipt and does not require a signature.
        </div>

    </div>
</body>
</html>
