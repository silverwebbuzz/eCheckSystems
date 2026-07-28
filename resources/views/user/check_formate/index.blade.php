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

<body style="padding: 20px; font-family: Arial, sans-serif">
    <table style="background-color: #ecedf6; padding: 10px 30px; border: 1px solid #000; height: 2.75in" width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5">
                    <tr>
                        <td style="text-align: left" colspan="2">
                            <table>
                                <tr>
                                    <td>
                                        <span
                                            style="display: inline-block; vertical-align: top; font-size: 18px; line-height: 20px">
                                            <span class="company name" style="font-size: 20px; font-weight: bold">
                                                {{ $data['payor_name'] }}</span>
                                            <br />
                                            @if (!empty($data['address1']))
                                                {{ $data['address1'] }}<br />
                                            @endif
                                            @if (!empty($data['address2']))
                                                {{ $data['address2'] }}<br />
                                            @endif
                                            {{ $data['city'] }}, {{ $data['state'] }} {{ $data['zip'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td></td>
                        <td style="text-align: right; width: 350px">
                            <span
                                style="font-size: 22px; font-weight: bold">{{ (isset($send_check) && $send_check==1) ? 'EC'.$data['check_number'] : $data['check_number'] }}</span><br /><br /><span
                                style="font-size: 16px">DATE: <span
                                    style="border-bottom: 1px solid #000; font-size: 22px; font-weight: 500;">{{ $data['check_date'] }}</span>
                                <br />
                                void after 90 days</span>
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="text-align: right; height: 24px"></td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="padding: 0; width: 105px; color: #000"><span
                                style="font-size: 20px; line-height: 21px">PAY </span><span
                                style="font-size: 16px; line-height: 17px">TO THE ORDER OF</span></td>
                        <td
                            style="border-bottom: 1px solid black; padding: 0; font-size: 22px; padding: 2px 10px 7px 10px; vertical-align: bottom">
                            <span style="font-weight: 500;">{{ $data['payee_name'] }}</span>
                        </td>
                        <td style="width: 15px; text-align: right; font-size: 20px; vertical-align: middle"></td>
                        <td
                            style="width: 250px; background-color: #fff; vertical-align: middle; text-align: left; padding: 10px 10px; font-size: 24px; font-weight: 500;">
                            $ {{ isset($send_check) && $send_check == 1 ? $data['amount'] : $data['total'] }}</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr>
                        <td
                            style="border-bottom: 1px solid black; padding: 20px 0 7px 5px; font-size: 22px; line-height: 25px; vertical-align: bottom; height: 45px; font-weight: 500;">
                            {{ $data['amount_word'] }}</td>
                        <td
                            style="padding: 0; font-size: 17px; width: 65px; text-align: right; vertical-align: bottom; height: 45px">
                            Dollars</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr style="height: 50px; font-size: 18px">
                        <td style="vertical-align: bottom; padding-top: 10px">{{ $data['bank_name'] }}</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr style="height: 50px; font-size: 12px">
                        <td style="width: 55px; height: 80px; vertical-align: bottom; font-size: 18px">Memo</td>
                        <td
                            style="height: 80px; border-bottom: 1px solid black; padding: 0; font-size: 18px; padding: 2px 10px 7px 7px; vertical-align: bottom; width: 40%">
                            <span>{{ $data['memo'] }}</span>
                        </td>
                        <td style="width: 200px;height: 80px;"></td>
                        <td
                            style="font-size: 12px; padding: 10px; background-color: #fff; border-radius: 10px;height: 80px;">
                            @if (!empty($data['signature']))
                                <div style="text-align:center;">
                                    <img width="300px" style="text-align:center; display:block; margin:0 auto;"
                                        src="{{ asset('sign/' . $data['signature']) }}" alt="signature img" />
                                </div>
                            @else
                                SIGNATURE NOT REQUIRED<br />
                                Your depositor has authorized this payment to payee.<br />
                                Payee to hold you harmless for payment of this document.<br />
                                This document shall be deposited only to the credit of payee.
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td style="height: 20px"></td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: 'MICRCheckPrixa'">
                    <tr style="height: 50px; font-size: 15px">
                        <td style="font-size: 30px; padding: 10px; text-align: center; font-family: 'MICRCheckPrixa'">
                            @if ($data['package'] == -1)
                                ;VOID;
                                :VOID:
                                VOID;
                            @else
                                ;{{ str_pad($data['check_number'], 6, '0', STR_PAD_LEFT) }};
                                :{{ $data['routing_number'] }}:
                                {{ $data['account_number'] }};
                            @endif
                        </td>
                    </tr>
                </table>
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
                    <img src="{{ asset('assets/cut_here.png') }}" alt="" width="100%" />
                </span>
            </td>
        </tr>
    </table>
    @if (isset($data['grid_items']) && !empty($data['grid_items']))
        <table width="100%" style="font-size:18px; text-align: center; border: 2px solid #b2c6cd; margin: 40px auto;" width="100%" cellspacing="0" cellpadding="5">
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
                        @foreach($item as $val)
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
                                <span>{{ (isset($send_check) && $send_check==1) ? 'EC'.$data['check_number'] : $data['check_number'] }}</span>
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
