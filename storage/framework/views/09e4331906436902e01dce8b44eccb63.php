<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?php echo e($order->order_number); ?></title>

    <style>
        /* -------------------------------------------------
           APPLICATION FONTS
        ------------------------------------------------- */
        @font-face {
            font-family: 'Geologica';
            src: url('<?php echo e(public_path('fonts/Geologica.ttf')); ?>') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Geologica';
            src: url('<?php echo e(public_path('fonts/Geologica-Bold.ttf')); ?>') format('truetype');
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
           Transparent background with readable text.
        ------------------------------------------------- */
        .header-box {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;

            /* Keep the header transparent */
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
            color: #d97706;
        }

        .order-number {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* -------------------------------------------------
           BILLED FROM / BILLED TO
        ------------------------------------------------- */
        .details-wrapper {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
        }

        .details-box {
            width: 50%;
            vertical-align: top;
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 9px 11px;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #4f46e5;
        }

        .details-box.amber {
            border-left-color: #f59e0b;
        }

        .section-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.8px;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: block;
        }

        .section-label.amber {
            color: #d97706;
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
        ------------------------------------------------- */
        .totals-wrapper {
            width: 100%;
            margin-bottom: 11px;
        }

        .totals-box {
            width: 45%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-table {
            width: 100%;
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
    <?php
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
    ?>

    <div class="receipt-container">

        <!-- Receipt Header -->
        <table class="header-box">
            <tr>
                <td width="55%">
                    <?php if($logoDataUri): ?>
                        <div class="logo-container">
                            <img
                                src="<?php echo e($logoDataUri); ?>"
                                class="logo-img"
                                alt="<?php echo e($settings['store_name'] ?? 'Store'); ?> Logo"
                            >
                        </div>

                        <?php if(!empty($settings['store_tagline'])): ?>
                            <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                                <?php echo e($settings['store_tagline']); ?>

                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="store-name">
                            <?php echo e($settings['store_name'] ?? 'Store'); ?>

                        </div>

                        <?php if(!empty($settings['store_tagline'])): ?>
                            <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                                <?php echo e($settings['store_tagline']); ?>

                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>

                <td width="45%" class="text-right" style="vertical-align: middle;">
                    <div class="receipt-title">Official Receipt</div>

                    <div class="order-number">
                        Order: <?php echo e($order->order_number); ?>

                    </div>

                    <div class="order-number">
                        Date: <?php echo e($order->created_at->format('d M Y, h:i A')); ?>

                    </div>
                </td>
            </tr>
        </table>

        <!-- Billed From / Billed To -->
        <table class="details-wrapper">
            <tr>
                <td class="details-box">
                    <span class="section-label">Billed From</span>

                    <div class="person-name">
                        <?php echo e($settings['store_name'] ?? 'Store'); ?>

                    </div>

                    <?php if(!empty($settings['store_address'])): ?>
                        <div class="muted"><?php echo e($settings['store_address']); ?></div>
                    <?php endif; ?>

                    <?php if(!empty($settings['store_email'])): ?>
                        <div class="muted"><?php echo e($settings['store_email']); ?></div>
                    <?php endif; ?>

                    <?php if(!empty($settings['store_phone'])): ?>
                        <div class="muted"><?php echo e($settings['store_phone']); ?></div>
                    <?php endif; ?>
                </td>

                <td class="details-box amber">
                    <span class="section-label amber">Billed To</span>

                    <div class="person-name">
                        <?php echo e($order->customer_name ?? 'Walk-in Customer'); ?>

                    </div>

                    <?php if(!empty($address)): ?>
                        <div class="muted"><?php echo e($address); ?></div>
                    <?php endif; ?>

                    <?php if(!empty($order->customer_email)): ?>
                        <div class="muted"><?php echo e($order->customer_email); ?></div>
                    <?php endif; ?>

                    <?php if(!empty($order->customer_phone)): ?>
                        <div class="muted"><?php echo e($order->customer_phone); ?></div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- Products Table -->
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
                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-center text-muted">
                                <?php echo e($index + 1); ?>

                            </td>

                            <td>
                                <div class="product-name">
                                    <?php echo e($item->product_name); ?>

                                </div>

                                <?php if(!empty($item->sku)): ?>
                                    <div class="sku">
                                        SKU: <?php echo e($item->sku); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($item->attributes)): ?>
                                    <?php
                                        $formattedAttributes = $formatAttributes($item->attributes);
                                    ?>

                                    <?php if($formattedAttributes !== ''): ?>
                                        <div class="attribute">
                                            <?php echo e($formattedAttributes); ?>

                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <td class="text-center text-muted">
                                <?php echo e(number_format($item->quantity, 0)); ?>

                            </td>

                            <td class="text-right text-muted">
                                <?php echo e($money($item->unit_price)); ?>

                            </td>

                            <td class="text-right font-bold text-dark">
                                <?php echo e($money($item->line_total)); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

                            <td class="text-right font-bold text-dark">
                                <?php echo e($money($order->subtotal)); ?>

                            </td>
                        </tr>

                        <tr>
                            <td class="text-muted">Shipping Fee</td>

                            <td class="text-right font-bold text-dark">
                                <?php echo e($money($order->shipping_fee)); ?>

                            </td>
                        </tr>

                        <?php if($order->discount_total > 0): ?>
                            <tr>
                                <td class="text-amber">Discount</td>

                                <td class="text-right font-bold text-amber">
                                    -<?php echo e($money($order->discount_total)); ?>

                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr class="grand-total-row">
                            <td class="text-left" style="text-transform: uppercase; letter-spacing: 0.8px;">
                                Total
                            </td>

                            <td class="text-right">
                                <?php echo e($money($order->grand_total)); ?>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Transaction Details -->
        <?php if($order->payments->count() > 0): ?>
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
                            <th width="25%" class="text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-muted" style="text-transform: capitalize;">
                                    <?php echo e($payment->provider?->value ?? 'N/A'); ?>

                                </td>

                                <td class="text-muted">
                                    <?php echo e($payment->provider_reference ?? 'N/A'); ?>

                                </td>

                                <td class="text-muted">
                                    <?php echo e($payment->created_at->format('d M Y, h:i A')); ?>

                                </td>

                                <td class="text-right font-bold text-dark">
                                    <?php echo e($money($payment->amount)); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            Thank you for shopping with us!<br>
            This is a computer-generated receipt and does not require a signature.
        </div>

    </div>
</body>
</html><?php /**PATH D:\SoftwareDevelopment\Vuejs\laravel_vuejs_inertia_tailwind_bootstrap\Ecommerce\resources\views/admin/orders/receipt-pdf.blade.php ENDPATH**/ ?>