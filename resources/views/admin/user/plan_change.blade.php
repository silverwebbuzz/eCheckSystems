<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plan </title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            /* min-height: 100vh; */
        }

        .pricing-table {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 50px 0;
        }

        .pricing-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            width: 220px;
            overflow: hidden;
            position: relative;
        }

        .pricing-card.popular::before {
            content: "POPULAR";
            background-color: #000000;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 10px;
            position: absolute;
            top: 18px;
            right: -31px;
            border-radius: 3px;
            transform: rotate(45deg);
            width: 100px;
        }

        .pricing-card h3 {
            background-color: #00a7cf;
            color: #ffffff;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .price {
            font-size: 40px;
            color: #7e57c2;
            margin: 10px 0;
            font-weight: 600;
        }

        .price span {
            font-size: 16px;
            color: #555;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
        }

        .features li {
            margin: 15px 0;
            color: #555;
        }

        .features li::before {
            content: "\2713";
            color: #4caf50;
            font-weight: bold;
            margin-right: 10px;
        }

        .pricing-card:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }

        .pricing-card .plan-button {
            background-color: #7367f0 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 5px !important;
            padding: 10px 15px !important;
            font-size: 1em !important;
            cursor: pointer !important;
            transition: 0.3s !important;
            text-decoration: none !important;
            margin-top: 20px !important;
        }

        .pricing-card .plan-button:hover {
            background-color: #7367f0 !important;
        }

        .selected-plan {
            border: 5px solid green !important;
            border-radius: 20px !important;
        }

        .current-plan {
            font-weight: bold;
            color: green;
            font-size: 16px;
            border: 1px solid green;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="pricing-table">
        @foreach ($packages as $package)
            <div
                class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}{{ $user->CurrentPackageID == $package->PackageID ? ' selected-plan' : '' }}">
                <h3>{{ $package->Name }}</h3>
                <p class="price">${{ $package->Price }} <span>monthly</span></p>
                <ul class="features">
                    <li>Up to {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }} checks
                        / month</li>
                    <li>Email Support</li>
                    <li>Unlimited Users</li>
                    @if ($package->Name != 'BASIC')
                        <li>Clients Webform*</li>
                    @endif
                    <li>3 mos History Storage</li>
                </ul>
                @if ($user->CurrentPackageID == $package->PackageID)
                    <p class="current-plan">Current Plan</p>
                @else
                    <a href="{{ route('admin.user.select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                        class="plan-button">Select Plan</a>
                @endif
            </div>
        @endforeach
    </div>
</body>

</html>
