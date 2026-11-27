
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading ?? 'Bazario Notification' }}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #334155;
            font-family: Geologica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .email-wrapper {
            width: 100%;
            padding: 22px 12px;
            background-color: #f8fafc;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }

        .email-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            display: block;
            max-width: 350px;
            max-height: 76px;
            width: auto;
            height: auto;
        }

        .store-name {
            color: #0f172a;
            font-size: 20px;
            font-weight: bold;
        }

        .tagline {
            margin-top: 3px;
            color: #64748b;
            font-size: 10px;
        }

        .header-label {
            color: #4f46e5;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: right;
            text-transform: uppercase;
        }

        .email-content {
            padding: 20px;
        }

        .email-heading {
            margin: 0 0 12px;
            color: #0f172a;
            font-size: 19px;
            font-weight: bold;
            line-height: 1.3;
        }

        .email-body {
            color: #334155;
            font-size: 13px;
            line-height: 1.4;
        }

        .email-body p {
            margin: 0 0 7px;
            padding: 0;
            line-height: 1.4;
        }

        .email-body p:last-child {
            margin-bottom: 0;
        }

        .email-body strong,
        .email-body b {
            color: #0f172a;
        }

        .email-action {
            margin-top: 14px;
        }

        .email-button {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 6px;
            background-color: #4f46e5;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
        }

        .email-footer {
            padding: 13px 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 10px;
            line-height: 1.4;
            text-align: center;
        }

        .email-footer p {
            margin: 0 0 3px;
            padding: 0;
        }

        .email-footer p:last-child {
            margin-bottom: 0;
        }

        @media only screen and (max-width: 480px) {
            .email-wrapper {
                padding: 10px 6px;
            }

            .email-header,
            .email-content {
                padding: 15px;
            }

            .email-heading {
                font-size: 17px;
            }
        }
    </style>
</head>

<body>
    @php
        use App\Models\StoreSetting;
        use Illuminate\Support\Facades\Storage;

        $settings = StoreSetting::public()
            ->pluck('value', 'key')
            ->all();

        $logoPath = $settings['store_logo'] ?? $settings['logo'] ?? null;

        if ($logoPath) {
            $logoPath = preg_replace('#^/?storage/#', '', $logoPath);
        }

        $logoUrl = null;

        if ($logoPath) {
            try {
                if (Storage::exists($logoPath)) {
                    $logoUrl = Storage::url($logoPath);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    @endphp

    <table role="presentation" width="100%" class="email-wrapper">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" class="email-container">

                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="left" valign="middle">
                                        @if($logoUrl)
                                            <img
                                                src="{{ $logoUrl }}"
                                                alt="{{ $settings['store_name'] ?? 'Bazario' }}"
                                                class="logo"
                                            >
                                        @else
                                            <div class="store-name">
                                                {{ $settings['store_name'] ?? 'Bazario' }}
                                            </div>
                                        @endif

                                        @if(!empty($settings['store_tagline']))
                                            <div class="tagline">
                                                {{ $settings['store_tagline'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <td align="right" valign="middle">
                                        <div class="header-label">
                                            Official
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Dynamic Content -->
                    <tr>
                        <td class="email-content">

                            <h1 class="email-heading">
                                {{ $heading }}
                            </h1>

                            <div class="email-body">
                                @foreach($body as $block)
                                    @if(is_array($block) && ($block['type'] ?? null) === 'action')
                                        <div class="email-action">
                                            <a
                                                href="{{ $block['url'] }}"
                                                class="email-button"
                                            >
                                                {{ $block['label'] }}
                                            </a>
                                        </div>
                                    @elseif(is_string($block))
                                        <p>{{ $block }}</p>
                                    @endif
                                @endforeach
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p>
                                Thank you for choosing
                                {{ $settings['store_name'] ?? 'Bazario' }}.
                            </p>

                            <p>
                                This is an automated notification.
                                Please do not reply to this email.
                            </p>

                            <p>
                                &copy; {{ date('Y') }}
                                {{ $settings['store_name'] ?? 'Bazario' }}.
                                All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
