
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
 @php
    $geologicaRegular = storage_path('fonts/Geologica.ttf');
    $geologicaBold = storage_path('fonts/Geologica-Bold.ttf');
@endphp
    <style>
        @if($geologicaRegular && $geologicaBold)
            @font-face {
                font-family: "Geologica";
                src: url("{{ $geologicaRegular }}") format("truetype");
                font-weight: 400;
                font-style: normal;
            }

            @font-face {
                font-family: "Geologica";
                src: url("{{ $geologicaBold }}") format("truetype");
                font-weight: 700;
                font-style: normal;
            }
        @endif

        @page {
            margin: 15px 20px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body,
        table,
        tr,
        td,
        th,
        div,
        span,
        strong {
            font-family: "Geologica", sans-serif;
        }

        body {
            font-family: "Geologica", sans-serif;
            color: #111827;
            font-size: 10px;
            line-height: 1.35;
            font-weight: 400;
        }

        strong,
        b {
            font-weight: 700;
        }

        /* Header */
        .header {
            text-align: center;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .store-name {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            line-height: 1.2;
        }

        .tagline {
            font-size: 9px;
            font-style: italic;
            color: #374151;
            margin-top: 2px;
            line-height: 1.2;
        }

        .receipt-title {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #111827;
            line-height: 1.2;
        }

        .order-number {
            color: #374151;
            margin-top: 2px;
            font-size: 9px;
            font-weight: 700;
        }

        .paid {
            display: inline-block;
            color: #14b8a6;
            background: #99f6e4\;
            padding: 2px 7px;
            font-weight: 700;
            border: 1pt solid #14b8a6;
            border-radius: 10px;
            font-size: 8px;
        }

        /* Details */
        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
            border: 0.75pt solid #374151;
        }

        .details td {
            width: 50%;
            vertical-align: top;
            padding: 6px 8px;
            background: #ffffff;
            border: 0.75pt solid #6b7280;
        }

        .section-label {
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .person-name {
            font-size: 10px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }

        .muted {
            color: #374151;
            font-size: 9px;
        }

        /* Items */
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
            border: 0.75pt solid #374151;
        }

        .items th {
            background: #ccfbf1;
            color: #2dd4bf;
            text-align: left;
            padding: 5px 6px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border: 0.75pt solid #2dd4bf;
        }

        .items td {
            padding: 5px 6px;
            border: 0.75pt solid #6b7280;
            vertical-align: top;
            color: #111827;
            font-size: 9px;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .product-name {
            font-weight: 700;
            color: #111827;
            font-size: 9px;
        }

        .sku {
            font-size: 8px;
            color: #374151;
        }

        .attribute {
            font-size: 8px;
            color: #374151;
            margin-top: 1px;
        }

        /* Totals */
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
            border: 0.75pt solid #374151;
        }

        .totals td {
            padding: 4px 7px;
            border: 0.75pt solid #6b7280;
            color: #111827;
            font-size: 9px;
        }

        .grand-total td {
            background: #ccfbf1;
            border: 0.75pt solid #2dd4bf;
            font-size: 11px;
            font-weight: 700;
            color: #2dd4bf;
            padding: 6px 7px;
        }

        /* Payments */
        .payment-heading {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .payment {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border: 0.75pt solid #374151;
        }

        .payment td {
            padding: 5px 7px;
            background: #ffffff;
            border: 0.75pt solid #6b7280;
            color: #111827;
            font-size: 9px;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            text-align: center;
            color: #374151;
            font-size: 9px;
            line-height: 1.4;
        }
    </style>
</head>

<body>

    @php
        $money = fn ($amount) => 'KSh ' . number_format((float) $amount, 2);

        /*
         * Format shipping address safely.
         */
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

        /*
         * Safely format product attributes, including nested arrays.
         */
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
                        return ucfirst(str_replace('_', ' ', $key))
                            . ': ' . $formattedValue;
                    }

                    return $formattedValue;
                })
                ->filter()
                ->implode(' | ');
        };
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="store-name">
            {{ $settings['store_name'] ?? 'Store' }}
        </div>

        @if(!empty($settings['store_tagline']))
            <div class="tagline">
                {{ $settings['store_tagline'] }}
            </div>
        @endif

        <div class="receipt-title">RECEIPT</div>

        <div class="order-number">
            {{ $order->order_number }}
        </div>

        {{-- <div style="margin-top: 2px;">
            <span class="paid">PAID</span>
        </div> --}}
    </div>

    <!-- Store and Customer Details -->
    <table class="details">
        <tr>
            <td>
                <div class="section-label">From</div>

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

                <div class="muted">
                    Issued: {{ $order->created_at->format('d M Y, h:i A') }}
                </div>
            </td>

            <td>
                <div class="section-label">To</div>

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
    </table>

    <!-- Order Items -->
    <table class="items">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="43%">Product</th>
                <th width="12%" class="text-center">Qty</th>
                <th width="20%" class="text-right">Unit Cost</th>
                <th width="20%" class="text-right">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">
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

                    <td class="text-center">
                        {{ number_format($item->quantity, 2) }}
                    </td>

                    <td class="text-right">
                        {{ $money($item->unit_price) }}
                    </td>

                    <td class="text-right">
                        {{ $money($item->line_total) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals">
        <tr>
            <td width="75%" class="text-right">
                Subtotal
            </td>

            <td width="25%" class="text-right">
                {{ $money($order->subtotal) }}
            </td>
        </tr>

            <tr>
                <td class="text-right">
                    Shipping Fee
                </td>

                <td class="text-right">
                    {{ $money($order->shipping_fee) }}
                </td>
            </tr>

        <tr class="grand-total">
            <td class="text-right">
                GRAND TOTAL
            </td>

            <td class="text-right">
                {{ $money($order->grand_total) }}
            </td>
        </tr>
    </table>

    <!-- Payment Details -->
    <div class="payment-heading">
        Payment Details
    </div>

    @foreach($order->payments as $payment)
        <table class="payment">
    <tr>
        <td width="20%">
            <strong>Method</strong>
        </td>

        <td width="30%">
            {{ \Illuminate\Support\Str::title($payment->provider?->value ?? 'N/A') }}
        </td>

        <td width="20%">
            <strong>Reference</strong>
        </td>

        <td width="30%">
            {{ $payment->provider_reference ?? 'N/A' }}
        </td>
    </tr>

    <tr>
        <td>
            <strong>Paid DateTime</strong>
        </td>

        <td>
            {{ $payment->created_at->format('d M Y, h:i A') }}
        </td>

        <td>
            <strong>Amount</strong>
        </td>

        <td>
            {{ $money($payment->amount) }}
        </td>
    </tr>
</table>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        Thank you for shopping with us!

        <br>

        This is a computer-generated receipt and does not require a signature.
    </div>

</body>
</html>
