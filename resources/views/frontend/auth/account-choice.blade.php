<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Account Type</title>
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

        .heading {
            margin-bottom: 30px;
            color: #333;
        }

        .choice-table {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .choice-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            width: 260px;
        }

        .choice-card h3 {
            background-color: #00a7cf;
            color: #ffffff;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .choice-card p {
            color: #555;
            line-height: 1.5;
            min-height: 72px;
        }

        .choice-button {
            display: inline-block;
            background-color: #000000;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: 500;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1 class="heading">Welcome! How would you like to get started?</h1>
    <div class="choice-table">
        @if ($trialPackage)
            <div class="choice-card">
                <h3>Start Free Trial</h3>
                <p>Try eCheckSystems with limited demo access while you explore the platform.</p>
                <form action="{{ route('user.account-choice.trial') }}" method="POST">
                    @csrf
                    <button type="submit" class="choice-button">Start Trial</button>
                </form>
            </div>
        @endif

        <div class="choice-card">
            <h3>Select Paid Plan</h3>
            <p>Choose a subscription plan and start using the full service right away.</p>
            <a href="{{ route('user.account-choice.paid') }}" class="choice-button">View Plans</a>
        </div>
    </div>
</body>

</html>
