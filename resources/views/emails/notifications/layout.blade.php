
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $heading }}</title>

    <style>
        @font-face {
            font-family: 'Geologica';
            src: url('{{ asset('fonts/Geologica.ttf') }}') format('truetype');
            font-weight: normal;
        }

        @font-face {
            font-family: 'Geologica';
            src: url('{{ asset('fonts/Geologica-Bold.ttf') }}') format('truetype');
            font-weight: bold;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #334155;
            font-family: 'Geologica', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.7;
        }

        table {
            border-collapse: collapse;
        }

        .email-wrapper {
            width: 100%;
            padding: 35px 15px;
            background-color: #f8fafc;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .email-header {
            padding: 22px 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .brand-name {
            margin: 0;
            color: #0f172a;
            font-size: 23px;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        .brand-tagline {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 11px;
        }

        .header-label {
            color: #4f46e5;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .email-content {
            padding: 32px 28px;
        }

        .email-heading {
            margin: 0 0 22px;
            color: #0f172a;
            font-size: 21px;
            font-weight: bold;
            line-height: 1.4;
        }

        .email-paragraph {
            margin: 0 0 16px;
            color: #475569;
            font-size: 13px;
            line-height: 1.8;
        }

        .email-action {
            display: inline-block;
            margin: 8px 0 18px;
            padding: 11px 22px;
            border-radius: 6px;
            background-color: #4f46e5;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
        }

        .email-footer {
            padding: 22px 25px;
            border-top: 1px solid #e2e8f0;
            background-color: #f8fafc;
            text-align: center;
        }

        .footer-message {
            margin: 0 0 7px;
            color: #334155;
            font-size: 12px;
            font-weight: bold;
        }

        .footer-copyright {
            margin: 0;
            color: #64748b;
            font-size: 10px;
        }

        .footer-automated {
            margin: 8px 0 0;
            color: #94a3b8;
            font-size: 10px;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 15px 8px !important;
            }

            .email-header,
            .email-content {
                padding: 22px 18px !important;
            }

            .email-heading {
                font-size: 19px !important;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" width="100%" class="email-wrapper">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" class="email-container">

                    <!-- Common Header -->
                    <tr>
                        <td class="email-header">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td align="left" valign="middle">
                                        <h1 class="brand-name">BAZARIO</h1>
                                        <p class="brand-tagline">
                                            Technology at your fingertips.
                                        </p>
                                    </td>

                                    <td align="right" valign="middle">
                                        <span class="header-label">
                                            Official Notification
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Dynamic Content -->
                    <tr>
                        <td class="email-content">
                            <h2 class="email-heading">
                                {{ $heading }}
                            </h2>

                            @foreach ($body as $block)
                                @if (is_array($block) && ($block['type'] ?? null) === 'action')
                                    <p style="margin: 0;">
                                        <a
                                            href="{{ $block['url'] }}"
                                            class="email-action"
                                        >
                                            {{ $block['label'] }}
                                        </a>
                                    </p>
                                @else
                                    <p class="email-paragraph">
                                        {{ is_array($block) ? '' : $block }}
                                    </p>
                                @endif
                            @endforeach
                        </td>
                    </tr>

                    <!-- Common Footer -->
                    <tr>
                        <td class="email-footer">
                            <p class="footer-message">
                                Thank you for choosing Bazario.
                            </p>

                            <p class="footer-copyright">
                                &copy; {{ date('Y') }} Bazario. All rights reserved.
                            </p>

                            <p class="footer-automated">
                                This is an automated notification. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
