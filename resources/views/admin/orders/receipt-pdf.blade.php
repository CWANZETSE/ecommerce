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

        /* Force Single Page */
        @page {
            margin: 20px 25px;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Geologica', sans-serif;
            color: #334155; /* Slate 700 */
            font-size: 11px;
            line-height: 1.4;
            background-color: #ffffff;
        }

        /* Strict Container */
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

        /* Vibrant Header Box */
        .header-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #4f46e5; /* Indigo */
            border-radius: 8px;
            border: 2px solid #4338ca; /* Darker Indigo Border */
            overflow: hidden;
        }
        .header-box td {
            padding: 20px;
            color: #ffffff;
        }
        
        /* Logo / Store Name Styling */
        .logo-container {
            background-color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .logo-img {
            max-height: 50px;
            max-width: 220px;
            display: block;
        }
        .store-name {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fcd34d; /* Amber light */
        }
        .order-number {
            font-size: 10px;
            color: #e0e7ff; /* Indigo 100 */
            margin-top: 4px;
        }

        /* Details Grid */
        .details-wrapper {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: separate;
            border-spacing: 15px 0;
            margin-left: -15px; /* Offset the spacing */
        }
        .details-box {
            width: 50%;
            vertical-align: top;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 12px 15px;
            border: 2px solid #e2e8f0; /* Heavy border */
            border-left: 5px solid #4f46e5; /* Indigo accent */
        }
        .details-box.amber {
            border-left: 5px solid #f59e0b; /* Amber accent */
        }
        .section-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 8px;
            display: block;
        }
        .section-label.amber { color: #d97706; }
        
        .person-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .muted {
            color: #64748b;
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* Items Table */
        .items-wrapper {
            width: 100%;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            overflow: hidden;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            background-color: #eef2ff; /* Indigo 50 */
            color: #4f46e5;
            text-align: left;
            padding: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #c7d2fe;
            border-right: 1px solid #c7d2fe;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            padding: 10px;
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
            font-size: 11px;
        }
        .sku {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .attribute {
            font-size: 9px;
            color: #b45309; /* Amber 700 */
            margin-top: 4px;
            background-color: #fef3c7; /* Amber 50 */
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #fde68a;
        }

        /* Totals Section */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 15px;
        }
        .totals-box {
            width: 45%;
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            font-size: 10px;
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
            background-color: #4f46e5; /* Indigo */
            color: #ffffff !important;
            font-size: 13px;
            font-weight: bold;
            padding: 12px;
        }
        .grand-total-row strong {
            color: #ffffff !important;
        }

        /* Payment Details */
        .payment-wrapper {
            width: 100%;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-table th {
            background-color: #f8fafc;
            color: #475569;
            padding: 8px 10px;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            text-align: left;
        }
        .payment-table th:last-child {
            border-right: none;
        }
        .payment-table td {
            padding: 10px;
            font-size: 10px;
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
            margin-top: 25px;
            padding: 15px;
            border: 2px dashed #cbd5e1;
            border-radius: 6px;
            text-align: center;
            color: #64748b;
            font-size: 9px;
            background-color: #f8fafc;
        }
    </style>
</head>

<body>
    @php
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Facades\Log;

        // 1. Logo Retrieval Logic
        $logoPath = $settings['store_logo'] ?? null;
        
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
            $address = collect($address)->flatten()->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')->map(fn ($value) => trim((string) $value))->implode(', ');
        } elseif (!is_scalar($address)) {
            $address = '';
        }

        $formatAttributes = function ($attributes) {
            if (is_string($attributes)) return $attributes;
            if (!is_array($attributes)) return is_scalar($attributes) ? (string) $attributes : '';
            $formatValue = function ($value) use (&$formatValue) {
                if (is_array($value)) {
                    return collect($value)->map(fn ($nestedValue) => $formatValue($nestedValue))->filter(fn ($nestedValue) => $nestedValue !== '')->implode(', ');
                }
                return is_scalar($value) ? trim((string) $value) : '';
            };
            return collect($attributes)->map(function ($value, $key) use ($formatValue) {
                $formattedValue = $formatValue($value);
                if ($formattedValue === '') return null;
                if (is_string($key)) return ucfirst(str_replace('_', ' ', $key)) . ': ' . $formattedValue;
                return $formattedValue;
            })->filter()->implode(' | ');
        };
    @endphp

    <div class="receipt-container">
        <!-- Vibrant Header -->
        <table class="header-box">
            <tr>
                <td width="55%">
                    @if($logoDataUri)
                        <div class="logo-container">
                            <img src="{{ $logoDataUri }}" class="logo-img" alt="{{ $settings['store_name'] ?? 'Store' }} Logo">
                        </div>
                        <div style="font-size: 10px; color: #e0e7ff; margin-top: 4px;">
                            {{ $settings['store_tagline'] ?? '' }}
                        </div>
                    @else
                        <div class="store-name">{{ $settings['store_name'] ?? 'Store' }}</div>
                        @if(!empty($settings['store_tagline']))
                            <div style="font-size: 10px; color: #e0e7ff; margin-top: 4px;">{{ $settings['store_tagline'] }}</div>
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
                    @if(!empty($settings['store_address'])) <div class="muted">{{ $settings['store_address'] }}</div> @endif
                    @if(!empty($settings['store_email'])) <div class="muted">{{ $settings['store_email'] }}</div> @endif
                    @if(!empty($settings['store_phone'])) <div class="muted">{{ $settings['store_phone'] }}</div> @endif
                </td>
                <td class="details-box amber">
                    <span class="section-label amber">Billed To</span>
                    <div class="person-name">{{ $order->customer_name ?? 'Walk-in Customer' }}</div>
                    @if(!empty($address)) <div class="muted">{{ $address }}</div> @endif
                    @if(!empty($order->customer_email)) <div class="muted">{{ $order->customer_email }}</div> @endif
                    @if(!empty($order->customer_phone)) <div class="muted">{{ $order->customer_phone }}</div> @endif
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
                                @if(!empty($item->sku)) <div class="sku">SKU: {{ $item->sku }}</div> @endif
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
                            <th colspan="4" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">Transaction Details</th>
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