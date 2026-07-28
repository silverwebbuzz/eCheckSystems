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
            min-height: 100vh;
            flex-direction: column;
        }

        .pricing-table {
            display: flex;
            gap: 20px;
        }

        .pricing-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            width: 200px;
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

        .heading {
            margin-bottom: 50px;
        }

        .trial_btn {
            background-color: #000000;
            color: #ffffff;
            padding: 12px 63px;
            text-decoration: none;
            margin: 25px 30px;
            font-weight: 500;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1 class="heading">Please Select Package</h1>
    <div class="pricing-table">
        @foreach ($packages as $package)
            @if ($package->Name == 'Trial')
                <div class="pricing-card">
                    <h3>{{ $package->Name }}</h3>
                    @if($package->Duration < 30)
                        <p class="price">${{ $package->Price }} <span>({{ $package->Duration }} days)</span></p>
                    @else
                        <p class="price">${{ $package->Price }} <span>monthly</span></p>
                    @endif
                    <ul class="features">
                        @if($package->Duration < 30)
                            <li>Up to {{ $package->CheckLimitPerMonth }} checks / {{ $package->Duration }} days</li>
                        @else
                            <li>Up to {{ $package->CheckLimitPerMonth }} checks / month</li>
                        @endif
                        <li>Email Support</li>
                        <li>Unlimited Users</li>
                        <li>3 mos History Storage</li>
                    </ul>
                    <a href="{{ route('user-select-free-package', ['id' => $userId, 'plan' => $package->PackageID]) }}"
                        class="plan-button">Select Plan</a>
                </div>
            @endif
        @endforeach

        {{-- Show all other packages after Trial --}}
        @foreach ($packages as $package)
            @if ($package->Name != 'Trial')
                <div
                    class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}">
                    <h3>{{ $package->Name }}</h3>
                    <p class="price">${{ $package->Price }} <span>monthly</span></p>
                    <ul class="features">
                        <li>
                            Up to {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited' }}
                            checks / month
                        </li>
                        <li>Email Support</li>
                        <li>Unlimited Users</li>
                        @if ($package->Name != 'BASIC')
                            <li>Custom Webform</li>
                        @endif
                        <li>3 mos History Storage</li>
                    </ul>
                    <a href="{{ route('user-select-package', ['id' => $userId, 'plan' => $package->PackageID]) }}"
                        class="plan-button">Select Plan</a>
                </div>
            @endif
        @endforeach
    </div>
</body>

</html>
