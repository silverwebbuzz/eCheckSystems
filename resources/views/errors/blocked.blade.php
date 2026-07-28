<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Restricted</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f6fa; /* Soft dashboard background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background: #ffffff;
            padding: 60px 50px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(88, 79, 255, 0.15);
            max-width: 520px;
            width: 90%;
            animation: fadeIn 0.5s ease-in-out;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #7367f0, #5a4fff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
        }

        .icon {
            font-size: 36px;
            color: #ffffff;
        }

        h1 {
            font-size: 28px;
            color: #444;
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 15px;
            color: #777;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 8px;
            background: linear-gradient(135deg, #7367f0, #5a4fff);
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s ease;
            box-shadow: 0 8px 20px rgba(115, 103, 240, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(115, 103, 240, 0.4);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 40px 25px;
            }

            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-wrapper">
            <div class="icon">🔒</div>
        </div>

        <h1>Access Restricted</h1>

        <p>
            Your access has been restricted due to security policies.
            <br>
            If you believe this is an error, please contact our support team.
        </p>

        <a href="https://echecksystems.com/contact/" class="btn">
            Contact Support
        </a>
    </div>
</body>
</html>
