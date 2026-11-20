
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>

    <style>
        /* -------------------------------------------------
           APPLICATION FONTS
        ------------------------------------------------- */
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

        /* -------------------------------------------------
           GENERAL STYLES
        ------------------------------------------------- */
        @page {
            margin: 15px 20px;
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
            line-height: 1.35;
            background-color: #ffffff;
        }

        .receipt-container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
        }

        strong,
        b {
            font-weight: bold;
            color: #0f172a;
        }

        /* -------------------------------------------------
           UTILITIES
        ------------------------------------------------- */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-muted {
            color: #64748b;
        }

        .text-indigo {
            color: #4f46e5;
        }

        .text-amber {
            color: #d97706;
        }

        .text-dark {
            color: #0f172a;
        }

        .font-bold {
            font-weight: bold;
        }

        /* -------------------------------------------------
           HEADER
        ------------------------------------------------- */
        .header-box {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
            background-color: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }

        .header-box td {
            padding: 12px 15px;
            color: #334155;
            vertical-align: middle;
        }

        .logo-container {
            background-color: transparent;
            padding: 0;
            border-radius: 0;
            display: inline-block;
            margin-bottom: 3px;
        }

        .logo-img {
            max-height: 36px;
            max-width: 190px;
            display: block;
        }

        .store-name {
            font-size: 21px;
            font-weight: bold;
            letter-spacing: -0.4px;
            color: #0f172a;
        }

        .receipt-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #4f46e5;
        }

        .order-number {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* -------------------------------------------------
           BILLED FROM / BILLED TO TABLE
        ------------------------------------------------- */
        .details-wrapper {
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .details-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .details-table th {
            width: 50%;
            background-color: #eef2ff;
            color: #4338ca;
            text-align: left;
            padding: 8px 11px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #c7d2fe;
            border-right: 1px solid #e0e7ff;
        }

        .details-table th:last-child {
            border-right: none;
        }

        .details-table td {
            width: 50%;
            padding: 9px 11px;
            vertical-align: top;
            border-right: 1px solid #e2e8f0;
        }

        .details-table td:last-child {
            border-right: none;
        }

        .details-table .billed-from-cell {
            border-left: 3px solid #4f46e5;
        }

        .details-table .billed-to-cell {
            border-left: 3px solid #f59e0b;
        }

        .person-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .muted {
            color: #64748b;
            font-size: 9px;
            margin-bottom: 2px;
        }

        /* -------------------------------------------------
           PRODUCTS TABLE
        ------------------------------------------------- */
        .items-wrapper {
            width: 100%;
            margin-bottom: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .items-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .items-table th {
            background-color: #eef2ff;
            color: #4338ca;
            text-align: left;
            padding: 8px 9px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #c7d2fe;
            border-right: 1px solid #e0e7ff;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table td {
            padding: 8px 9px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
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
            margin-top: 2px;
        }

        .attribute {
            font-size: 8px;
            color: #b45309;
            margin-top: 4px;
            background-color: #fffbeb;
            display: inline-block;
            padding: 2px 5px;
            border-radius: 5px;
            border: 1px solid #fde68a;
        }

        /* -------------------------------------------------
           TOTALS
           Card occupies exactly 50% of receipt width,
           matching the Billed To column.
        ------------------------------------------------- */
        .totals-wrapper {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 11px;
        }

        .totals-wrapper > tbody > tr > td {
            padding: 0;
            vertical-align: top;
        }

        .totals-box {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 7px 10px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
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
            font-size: 12px;
            font-weight: bold;
            padding: 9px 10px;
        }

        .grand-total-row strong {
            color: #ffffff !important;
        }

        /* -------------------------------------------------
           TRANSACTION DETAILS
        ------------------------------------------------- */
        .payment-wrapper {
            width: 100%;
            margin-bottom: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .payment-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .payment-table th {
            background-color: #f8fafc;
            color: #475569;
            padding: 7px 9px;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            text-align: left;
        }

        .payment-table th:last-child {
            border-right: none;
        }

        .payment-table td {
            padding: 8px 9px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            color: #334155;
        }

        .payment-table td:last-child {
            border-right: none;
        }

        .payment-table tr:last-child td {
            border-bottom: none;
        }

        /* -------------------------------------------------
           FOOTER
        ------------------------------------------------- */
        .footer {
            width: 100%;
            margin-top: 16px;
            padding: 10px 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
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
                    $mime = Storage::mimeType($logoPath) ?: 'image/png';

                    $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                } else {
                    Log::warning('Receipt logo missing on disk', [
                        'path' => $logoPath,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Receipt logo embedding failed', [
                    'path' => $logoPath,
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
            if (is_string($attributes)) {
                return $attributes;
            }

            if (!is_array($attributes)) {
                return is_scalar($attributes) ? (string) $attributes : '';
            }

            $formatValue = function ($value) use (&$formatValue) {
                if (is_array($value)) {
                    return collect($value)
                        ->map(fn ($nestedValue) => $formatValue($nestedValue))
                        ->filter(fn ($nestedValue) => $nestedValue !== '')
                        ->implode(', ');
                }

                return is_scalar($value) ? trim((string) $value) : '';
            };

            return collect($attributes)
                ->map(function ($value, $key) use ($formatValue) {
                    $formattedValue = $formatValue($value);

                    if ($formattedValue === '') {
                        return null;
                    }

                    if (is_string($key)) {
                        return ucfirst(str_replace('_', ' ', $key)) . ': ' . $formattedValue;
                    }

                    return $formattedValue;
                })
                ->filter()
                ->implode(' | ');
        };
    @endphp

    <div class="receipt-container">

        <!-- Receipt Header -->
        <table class="header-box">
            <tr>
                <td width="55%">
                    @if($logoDataUri)
                        <div class="logo-container">
                            <img
                                src="{{ $logoDataUri }}"
                                class="logo-img"
                                alt="{{ $settings['store_name'] ?? 'Store' }} Logo"
                            >
                        </div>

                        @if(!empty($settings['store_tagline']))
                            <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                                {{ $settings['store_tagline'] }}
                            </div>
                        @endif
                    @else
                        <div class="store-name">
                            {{ $settings['store_name'] ?? 'Store' }}
                        </div>

                        @if(!empty($settings['store_tagline']))
                            <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                                {{ $settings['store_tagline'] }}
                            </div>
                        @endif
                    @endif
                </td>

                <td width="45%" class="text-right" style="vertical-align: middle;">
                    <div class="receipt-title">Official Receipt</div>

                    <div class="order-number">
                        Order: {{ $order->order_number }}
                    </div>

                    <div class="order-number">
                        Date: {{ $order->created_at->format('d M Y, h:i A') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Billed From / Billed To Table -->
        <div class="details-wrapper">
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Billed From</th>
                        <th>Billed To</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="billed-from-cell">
                            <div class="person-name">
                                {{ $settings['store_name'] ?? 'Store' }}
                            </div>

                            @if(!empty($settings['store_address']))
                                <div class="muted">
                                    {{ $settings['store_address'] }}
                                </div>
                            @endif

                            @if(!empty($settings['store_email']))
                                <div class="muted">
                                    {{ $settings['store_email'] }}
                                </div>
                            @endif

                            @if(!empty($settings['store_phone']))
                                <div class="muted">
                                    {{ $settings['store_phone'] }}
                                </div>
                            @endif
                        </td>

                        <td class="billed-to-cell">
                            <div class="person-name">
                                {{ $order->customer_name ?? 'Walk-in Customer' }}
                            </div>

                            @if(!empty($address))
                                <div class="muted">
                                    {{ $address }}
                                </div>
                            @endif

                            @if(!empty($order->customer_email))
                                <div class="muted">
                                    {{ $order->customer_email }}
                                </div>
                            @endif

                            @if(!empty($order->customer_phone))
                                <div class="muted">
                                    {{ $order->customer_phone }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Products Table -->
        <div class="items-wrapper">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="40%">Item Description</th>
                        <th width="15%" class="text-center">Qty</th>
                        <th width="20%" class="text-right">Unit Price</th>
                        <th width="20%" class="text-right" style="text-align:right">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="text-center text-muted">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <div class="product-name">
                                    {{ $item->product_name }}
                                </div>

                                @if(!empty($item->sku))
                                    <div class="sku">
                                        SKU: {{ $item->sku }}
                                    </div>
                                @endif

                                @if(!empty($item->attributes))
                                    @php
                                        $formattedAttributes = $formatAttributes($item->attributes);
                                    @endphp

                                    @if($formattedAttributes !== '')
                                        <div class="attribute">
                                            {{ $formattedAttributes }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <td class="text-center text-muted">
                                {{ number_format($item->quantity, 0) }}
                            </td>

                            <td class="text-right text-muted">
                                {{ $money($item->unit_price) }}
                            </td>

                            <td class="text-right font-bold text-dark">
                                {{ $money($item->line_total) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <table class="totals-wrapper">
            <tbody>
                <tr>
                    <!-- Empty left half: 50% -->
                    <td width="50%"></td>

                    <!-- Totals card right half: 50% -->
                    <td width="50%">
                        <div class="totals-box">
                            <table class="totals-table">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Subtotal</td>

                                        <td class="text-right font-bold text-dark">
                                            {{ $money($order->subtotal) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Shipping Fee</td>

                                        <td class="text-right font-bold text-dark">
                                            {{ $money($order->shipping_fee) }}
                                        </td>
                                    </tr>

                                    @if($order->discount_total > 0)
                                        <tr>
                                            <td class="text-amber">Discount</td>

                                            <td class="text-right font-bold text-amber">
                                                -{{ $money($order->discount_total) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr class="grand-total-row">
                                        <td class="text-left" style="text-transform: uppercase; letter-spacing: 0.8px;">
                                            Total
                                        </td>

                                        <td class="text-right">
                                            {{ $money($order->grand_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Transaction Details -->
        @if($order->payments->count() > 0)
            <div class="payment-wrapper">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th
                                colspan="4"
                                style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left;"
                            >
                                Transaction Details
                            </th>
                        </tr>

                        <tr>
                            <th width="20%">Method</th>
                            <th width="30%">Reference</th>
                            <th width="25%">Date</th>
                            <th width="25%" class="text-right" style="text-align:right">Amount</th>
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
            This is a system-generated receipt and does not require a signature.
        </div>

    </div>
</body>
</html>
