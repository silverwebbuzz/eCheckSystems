<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Form</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 40px;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            display: flex;
            gap: 30px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
        }

        .form-section {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-section label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-section input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .form-row .form-group {
            flex: 1;
        }

        .pay-btn {
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .pay-btn:hover {
            background-color: #0056b3;
        }

        .summary-section {
            flex: 1;
            border-left: 1px solid #e0e0e0;
            padding-left: 20px;
        }

        .summary-section h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .summary-section p {
            margin: 6px 0;
            font-size: 15px;
        }

        .total {
            font-weight: bold;
            margin-top: 15px;
            font-size: 16px;
        }

        .error {
            color: red;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .summary-section {
                border-left: none;
                border-top: 1px solid #e0e0e0;
                padding-top: 20px;
            }
        }
    </style>

    <script src="https://js.stripe.com/v3/"></script>
</head>


<body>
    <div class="container">
        <div id="card-element"></div>
        <form id="payment-form" novalidate>
            <div class="form-section">
                <div class="form-group">
                    <label for="card-number">Card Number *</label>
                    <input type="text" id="card-number" placeholder="**** **** **** 4242" />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Expiration Date *</label>
                        <input type="text" id="expiry" placeholder="MM/YYYY" />
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV *</label>
                        <input type="text" id="cvv" placeholder="e.g. 548" />
                    </div>
                </div>

                <button class="pay-btn">Pay</button>
            </div>
        </form>

        <div class="summary-section">
            <h3>Plan</h3>
            <p>{{ $package->Name }}</p>
            <p class="total">Total (USD): ${{ $package->Price }}</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('payment-form');

        const fields = {
            'card-number': {
                regex: /^[\d\s]{16,19}$/,
                message: 'Enter a valid 16-digit card number'
            },
            'expiry': {
                regex: /^(0[1-9]|1[0-2])\/\d{4}$/,
                custom: function(val) {
                    const [month, year] = val.split('/');
                    const now = new Date();
                    const inputDate = new Date(`${year}-${month}-01`);
                    return inputDate >= new Date(now.getFullYear(), now.getMonth(), 1);
                },
                message: 'Enter a valid future date in MM/YYYY format'
            },
            'cvv': {
                regex: /^\d{3,4}$/,
                message: 'Enter a valid 3 or 4-digit CVV'
            },
            'address': {
                regex: /.+/,
                message: 'Address is required'
            },
            'city': {
                regex: /.+/,
                message: 'City is required'
            },
            'state': {
                regex: /.+/,
                message: 'State is required'
            },
            'zipcode': {
                regex: /^\d{5}$/,
                message: 'Enter a valid 5-digit zipcode'
            }
        };

        const showError = (input, message) => {
            removeError(input); // Ensure no duplicate errors
            const error = document.createElement('div');
            error.className = 'error';
            error.innerText = message;
            input.parentNode.appendChild(error);
        };

        const removeError = (input) => {
            const next = input.parentNode.querySelector('.error');
            if (next) next.remove();
        };

        Object.keys(fields).forEach(id => {
            const input = document.getElementById(id);
            input.addEventListener('input', () => {
                const val = input.value.trim();
                const rule = fields[id];
                let isValid = rule.regex.test(val);
                if (rule.custom) {
                    isValid = isValid && rule.custom(val);
                }
                if (isValid) {
                    removeError(input);
                }
            });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let valid = true;

            Object.keys(fields).forEach(id => {
                const input = document.getElementById(id);
                const val = input.value.trim();
                const rule = fields[id];

                let isValid = rule.regex.test(val);
                if (rule.custom) {
                    isValid = isValid && rule.custom(val);
                }

                if (!isValid) {
                    showError(input, rule.message);
                    valid = false;
                } else {
                    removeError(input);
                }
            });

            if (valid) {
                alert('Payment submitted successfully!');
                form.reset();
                document.querySelectorAll('.error').forEach(e => e.remove());
            }
        });
    </script>
</body>

</html>
