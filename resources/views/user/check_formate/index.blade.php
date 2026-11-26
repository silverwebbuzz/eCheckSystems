<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Echeck Systems</title>
    <style>
        @page {
            margin: 0;
            size: letter portrait;
        }

        html {
            margin: 0 auto;
            padding: 0;
        }

        body {
            margin: 0 auto;
        }

        @font-face {
            font-family: "MICRCheckPrixa";
            src: url("./font/MICRCheckPrixa.eot");
            src: url("./font/MICRCheckPrixa.eot?#iefix") format("embedded-opentype"), url("./font/MICRCheckPrixa.woff2") format("woff2"), url("./font/MICRCheckPrixa.woff") format("woff"), url("./font/MICRCheckPrixa.ttf") format("truetype");
        }

        td {
            padding: 0;
        }
    </style>
</head>

@php
    $accent = '#7367f0';
    $accentDark = '#6258cc';
    $paperBg = '#f9f9fe';
    $bandBg = '#f4f3fe';
    $label = '#68757a';
    $muted = '#9aa6aa';
    $text = '#1d2a2e';
    $line = '#aeb9bd';
    $checkNo = (isset($send_check) && $send_check == 1) ? 'EC' . $data['check_number'] : $data['check_number'];
    $amountValue = isset($send_check) && $send_check == 1 ? $data['amount'] : $data['total'];
@endphp

<body style="padding: 18px; font-family: DejaVu Sans, Arial, sans-serif; color: {{ $text }};">
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1px solid #d3d8db; background: {{ $paperBg }};">
        <tr>
            <td style="height: 6px; background: {{ $accent }}; font-size: 1px; line-height: 6px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding: 22px 28px 12px 20px;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top; width: 62%;">
                            <table cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                                <tr>
                                    <td width="58" style="vertical-align: middle; padding-right: 20px;">
                                        <img src="https://echecksystems.com/wp-content/uploads/elementor/thumbs/echeck-systems-logo-r3qzixzultt1pr9mur1kl8ksvlbpbynxzcx5fso11c.png" width="130" height="50" alt="logo" />
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="font-size: 16px; font-weight: bold; letter-spacing: 0.02em; color: {{ $text }};">
                                            {{ $data['payor_name'] }}
                                        </div>
                                        <div style="font-size: 11px; color: {{ $label }}; line-height: 1.45; margin-top: 2px;">
                                            @if (!empty($data['address1']))
                                                {{ $data['address1'] }}@if (!empty($data['address2'])), {{ $data['address2'] }}@endif<br />
                                            @endif
                                            {{ $data['city'] }}, {{ $data['state'] }} {{ $data['zip'] }}
                                            @if (!empty($data['bank_name']))
                                                <br />{{ $data['bank_name'] }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align: top; text-align: right; width: 38%;">
                            <div style="font-size: 11px; letter-spacing: 0.12em; color: {{ $label }};">
                                CHECK NO.
                                <span style="color: {{ $accentDark }}; font-weight: bold; font-family: DejaVu Sans Mono, monospace; letter-spacing: 0;">{{ $checkNo }}</span>
                            </div>
                            <div style="margin-top: 16px;">
                                <table cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-left: auto;">
                                    <tr>
                                        <td style="font-size: 10px; letter-spacing: 0.12em; color: {{ $label }}; padding-right: 8px; vertical-align: bottom;">DATE</td>
                                        <td style="border-bottom: 1px solid {{ $line }}; min-width: 140px; padding: 0 4px 2px 4px; font-size: 13px; font-weight: bold; text-align: center; vertical-align: bottom;">
                                            {{ $data['check_date'] }}
                                        </td>
                                    </tr>
                                </table>
                                <div style="font-size: 9px; color: {{ $muted }}; margin-top: 4px;">void after 90 days</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 28px 0 28px;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="width: 78px; font-size: 10px; letter-spacing: 0.08em; color: {{ $label }}; line-height: 1.35; vertical-align: middle;">
                            PAY TO THE<br />ORDER OF
                        </td>
                        <td style="border-bottom: 1px solid {{ $line }}; padding: 4px 10px 6px 10px; font-size: 15px; font-weight: bold; vertical-align: bottom;">
                            {{ $data['payee_name'] }}
                        </td>
                        <td style="width: 18px; text-align: center; font-size: 20px; color: {{ $accentDark }}; font-weight: bold; vertical-align: middle; padding: 0 6px;">$</td>
                        <td style="width: 140px; height: 34px; border: 1.5px solid {{ $accent }}; background: #ffffff; text-align: center; vertical-align: middle; font-size: 16px; font-weight: bold;">
                            {{ $amountValue }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 18px 28px 0 28px;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="border-bottom: 1px solid {{ $line }}; padding: 4px 8px 6px 4px; font-size: 14px; font-weight: bold; vertical-align: bottom;">
                            {{ $data['amount_word'] }}
                        </td>
                        <td style="width: 72px; font-size: 10px; letter-spacing: 0.12em; color: {{ $label }}; text-align: right; vertical-align: bottom; padding-bottom: 6px;">
                            DOLLARS
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 22px 28px 18px 28px;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: bottom; width: 55%;">
                            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                                <tr>
                                    <td style="width: 48px; font-size: 10px; letter-spacing: 0.12em; color: {{ $label }}; vertical-align: bottom; padding-bottom: 4px;">MEMO</td>
                                    <td style="border-bottom: 1px solid {{ $line }}; padding: 2px 8px 4px 8px; font-size: 12px; vertical-align: bottom;">
                                        {{ $data['memo'] }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 24px;"></td>
                        <td style="width: 240px; vertical-align: bottom; text-align: center;">
                            @if (!empty($data['signature']))
                                <div style="border-bottom: 1px solid #7d8a8f; height: 48px; text-align: center;">
                                    <img src="{{ public_path('sign/' . $data['signature']) }}" alt="signature" style="max-height: 44px; max-width: 220px;" />
                                </div>
                            @else
                                <div style="border-bottom: 1px solid #7d8a8f; min-height: 28px; font-size: 9px; color: {{ $label }}; padding: 2px 4px 4px 4px; text-align: left; line-height: 1.25;">
                                    SIGNATURE NOT REQUIRED<br />
                                    Your depositor has authorized this payment to payee.
                                </div>
                            @endif
                            <div style="font-size: 9px; letter-spacing: 0.1em; color: {{ $muted }}; margin-top: 4px;">AUTHORIZED SIGNATURE</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background: {{ $bandBg }}; border-top: 1px solid #dde3e5; padding: 10px 28px; text-align: center;">
                <div style="font-family: 'MICRCheckPrixa', DejaVu Sans Mono, monospace; font-size: 22px; letter-spacing: 0.08em; color: #7d8a8f;">
                    @if ($data['package'] == -1)
                        ;VOID; :VOID: VOID;
                    @else
                        ;{{ str_pad($data['check_number'], 6, '0', STR_PAD_LEFT) }};
                        :{{ $data['routing_number'] }}:
                        {{ $data['account_number'] }};
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td style="height: 15px"></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td>
                <span style="width: 100%; display: block">
                    <img src="{{ public_path('assets/cut_here.png') }}" alt="" width="100%" />
                </span>
            </td>
        </tr>
    </table>
    @if (isset($data['grid_items']) && !empty($data['grid_items']))
        <table width="100%" style="font-size:18px; text-align: center; border: 2px solid #b2c6cd; margin: 40px auto;" cellspacing="0" cellpadding="5">
            <thead>
                <tr style="border: 2px solid #b2c6cd;">
                    @foreach ($data['grid_headers'] as $header)
                        <th style="border: 2px solid #b2c6cd;">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data['grid_items'] as $item)
                    <tr style="border: 2px solid #b2c6cd;">
                        @foreach ($item as $val)
                            <td style="border: 2px solid #b2c6cd;">
                                {{ $val }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <table width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="font-size: 30px; color: #000; text-align: left">How to use this check</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="height: 10px"></td>
                    </tr>
                </table>
                <table width="100%" style="border-collapse: collapse; border: 2px solid #b2c6cd">
                    <thead style="font-size: 20px; background: #e1eef3">
                        <tr>
                            <th
                                style="border: 2px solid #b2c6cd; padding: 20px 20px 0 20px; vertical-align: middle; width: 30%; text-align: left; height: 100px">
                                <span
                                    style="font-size: 60px; line-height: 0.8; font-weight: bold; display: inline-block; height: 50px">1</span>
                                <span
                                    style="width: 130px; display: inline-block; font-size: 22px; line-height: 1; height: 50px">Printing
                                    the check</span>
                            </th>
                            <th
                                style="border: 2px solid #b2c6cd; padding: 20px 20px 0 20px; vertical-align: middle; width: 40%; text-align: left; height: 100px">
                                <span
                                    style="font-size: 60px; line-height: 0.8; font-weight: bold; display: inline-block; height: 50px">2</span>
                                <span
                                    style="width: 240px; display: inline-block; font-size: 22px; line-height: 1; height: 50px">Make
                                    sure everything printed properly</span>
                            </th>
                            <th
                                style="border: 2px solid #b2c6cd; padding: 20px 20px 0 20px; vertical-align: middle; width: 30%; text-align: left; height: 100px">
                                <span
                                    style="font-size: 60px; line-height: 0.8; font-weight: bold; display: inline-block; height: 50px">3</span>
                                <span
                                    style="width: 250px; display: inline-block; font-size: 22px; line-height: 1; height: 50px">Deposit
                                    like you would your regular checks</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td
                                style="border: 2px solid #b2c6cd; padding: 20px; width: 30%; vertical-align: top; font-size: 20px">
                                <ul style="margin: 0">
                                    <li>
                                        <strong>Use any printer</strong>
                                    </li>
                                    <li>
                                        <strong>Use color or black ink</strong>
                                    </li>
                                    <li>
                                        <strong>Use white printer paper</strong>
                                    </li>
                                </ul>
                            </td>
                            <td
                                style="border: 2px solid #b2c6cd; vertical-align: top; padding: 20px; width: 40%; font-size: 20px">
                                <ul style="margin: 0">
                                    <li>
                                        <strong>Make sure all bank numbers are centered and easy to read</strong>
                                    </li>
                                    <li>
                                        <strong>Reprint any checks that are misaligned, too light or cut off</strong>
                                    </li>
                                </ul>
                            </td>
                            <td
                                style="font-size: 20px; border: 2px solid #b2c6cd; padding: 20px; width: 30%; vertical-align: top">
                                <ul style="margin: 0">
                                    <li>
                                        <strong>Cut, endorse, and deposit! </strong>
                                    </li>
                                    <li>
                                        <strong>Deposit like you normally would with any check: In-person at your bank,
                                            mobile deposit or check scanner</strong>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="padding: 20px" colspan="3">
                                <span style="color: #000; font-size: 18px">Need help? For any questions visit us at
                                    <strong style="color: #000; font-size: 20px"><a
                                            href="https://www.echecksystems.com" target="_blank"
                                            style="color: #000; font-size: 20px; text-decoration: none">www.echecksystems.com</a></strong></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="height: 30px"></td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="width: 50%">
                            <span style="font-size: 30px">Your Receipt - Save for your records</span>
                            <br />
                            <br />
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Payment Date: </strong>
                                <span>{{ $data['check_date'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Check number: </strong>
                                <span>{{ $checkNo }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>From: </strong>
                                <span>{{ $data['payor_name'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Amount: </strong>
                                <span>${{ $data['amount'] }}</span>
                            </div>
                            @if (isset($send_check) && $send_check != 1)
                                <div style="margin-bottom: 5px; font-size: 20px">
                                    <strong>Service Fee: </strong>
                                    <span>${{ $data['service_fee'] }}</span>
                                </div>
                                <div style="margin-bottom: 5px; font-size: 20px">
                                    <strong>Total: </strong>
                                    <span>${{ $data['total'] }}</span>
                                </div>
                            @endif
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Payable to: </strong>
                                <span>{{ $data['payee_name'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Delivery email: </strong>
                                <span>{{ $data['email'] }}</span>
                            </div>
                            <div style="font-size: 20px">
                                <strong>Memo: </strong>
                                <span>{{ $data['memo'] }}</span>
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 20px">
                            <span style="font-size: 20px; line-height: 1.3; width: 100%; display: inline-block">
                                Looking to save money and time? Get paid quicker and make payments with Echeck Systems.
                                Visit us at <a href="https://www.echecksystems.com"
                                    target="_blank">www.echecksystems.com</a> for more info.
                            </span>
                            <br />
                            <br />
                            <div style="text-align: left">
                                <img src="https://echecksystems.com/wp-content/uploads/elementor/thumbs/echeck-systems-logo-r3qzixzultt1pr9mur1kl8ksvlbpbynxzcx5fso11c.png"
                                    alt="company logo" style="width: 200px" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
