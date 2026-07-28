<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web-Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <style>
        @font-face {
            font-family: "Public Sans";
            src: url("../../font/PublicSans-Regular.woff2") format("woff2"),
                /* Modern browsers */
                url("../../font/PublicSans-Regular.woff") format("woff"),
                /* Most fallback */
                url("../../font/PublicSans-Regular.ttf") format("truetype");
            /* Legacy fallback */
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Public Sans", sans-serif;
        }

        .container_area {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        header,
        .main_content {
            padding: 20px;
        }

        header .container_area {
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }

        .main_content {
            margin-top: 30px;
        }

        header .logo img {
            width: auto;
            max-width: 100%;
            object-fit: contain;
            height: 70px;
        }

        .content_left {
            width: 40%;
        }

        .content_right {
            width: calc(60% - 50px);
        }

        .content_right img {
            width: 100%;
            margin-bottom: 30px;
        }

        .content_left form {
            padding: 20px 20px 30px;
            border-radius: 10px;
            background-color: #F4F4F4;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0.1875rem 0.75rem #2f2b3d24;
        }

        form .w-50 {
            width: calc(50% - 10px);
        }

        form .check_num_date,
        form .city_state_zip {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        form label {
            color: #444050;
            font-size: 15px;
            line-height: 20px;
            display: block;
            margin-bottom: 5px;
        }

        form input {
            height: 40px;
            width: 100%;
            padding: 8px 15px;
            font-size: 15px;
            font-weight: 400;
            line-height: 20px;
            font-family: "Public Sans", sans-serif !important;
            color: #444050;
            background-color: #ffffff;
            border: 1px solid #d1d0d4;
            border-radius: 5px;
            outline: none !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        form input:hover {
            border-color: #444050 !important;
        }

        form input:focus,
        form input:focus-visible,
        form input:focus-within {
            border-color: #7367f0 !important;
            border-width: 2px !important;
            box-shadow: 0 2px 6px #7367f04d !important;
        }

        form .input_row {
            margin-bottom: 15px;
        }

        form .pay_to {
            padding: 10px;
            border-top: 1px solid #d1d0d4;
            border-bottom: 1px solid #d1d0d4;
        }

        form .pay_from {
            text-align: center;
            padding: 20px 10px 10px;
        }

        form .city_state_zip .w-33 {
            width: calc(33.33% - 13px);
        }

        .submit_btn {
            margin-top: 20px;
        }

        .submit_btn input {
            box-shadow: 0 0.125rem 0.375rem #7367f04d !important;
            color: #fff;
            background-color: #7367f0 !important;
            border: none !important;
            width: auto;
            padding: 8px 30px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.135s ease-in-out;
        }

        .submit_btn input:hover {
            background-color: #685dd8 !important;
        }

        .error {
            color: red;
        }

        @media (max-width: 1024px) {
            .content_left {
                width: 50%;
            }

            .content_right {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 900px) {
            .main_content .container_area {
                gap: 50px;
                flex-direction: column-reverse;
            }

            .content_left,
            .content_right {
                width: 100%;
            }

            .content_right {
                text-align: center;
            }

            .company_desc {
                text-align: left;
            }

            .content_right img {
                max-width: 600px;
            }
        }

        @media (max-width: 480px) {

            form .check_num_date,
            form .city_state_zip {
                align-items: flex-start;
                flex-direction: column;
                gap: 15px;
            }

            form .w-50,
            form .city_state_zip .w-33 {
                width: 100%;
            }

            address {
                font-size: 14px;
            }
        }

        /* For Chrome, Safari, Edge, Opera */
        .no-spinner::-webkit-inner-spin-button,
        .no-spinner::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* For Firefox */
        .no-spinner {
            -moz-appearance: textfield;
        }

        .footer-section {
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px
        }

        .footer-section p {
            width: 100%;
            text-align: center;
            margin: 30px 0 0;
            padding: 20px;
        }

        .footer-section a {
            text-decoration: none;
            font-weight: 600;
            color: #7367f0;
        }
    </style>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css"
        integrity="sha512-34s5cpvaNG3BknEWSuOncX28vz97bRI59UnVtEEpFX536A7BtZSJHsDyFoCl8S7Dt2TPzcrCEoHBGeM4SUBDBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.standalone.min.css"
        integrity="sha512-D5/oUZrMTZE/y4ldsD6UOeuPR4lwjLnfNMWkjC0pffPTCVlqzcHTNvkn3dhL7C0gYifHQJAIrRTASbMvLmpEug=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#check_date", {
            dateFormat: "m/d/Y",
        });
    </script> --}}
</head>

<body>
    @php
        $logo_img_path = asset($data->Logo);
    @endphp
    <header>
        <div class="container_area">
            <div class="logo">
                <img src="{{ $logo_img_path }}" alt="brand logo img" />
            </div>
            <div class="address">
                <address style="font-style: normal; text-align: right; font-weight: bold">
                    @if (!empty($company->Address1))
                        {{ $company->Address1 }}<br>
                    @endif
                    {{ $company->City }}, {{ $company->State }} {{ $company->Zip }}<br>
                    @if (!empty($company->PhoneNumber))
                        Phone Number: {{ $company->PhoneNumber }}
                    @endif
                </address>
            </div>
        </div>
    </header>
    <section class="main_content">
        @if (session('error'))
            <div class="alert alert-danger my-3" style="max-width: 1400px;
    margin: 0 auto;">
                {!! session('error') !!}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success my-3" style="max-width: 1400px;
    margin: 0 auto;">
                {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-danger">
                {{ session('info') }}
            </div>
        @endif
        <div class="container_area">
            <div class="content_left">
                <form id="webForm" method="POST" action="{{ route('store_web_form_data') }}">
                    @csrf
                    <input type="hidden" id="company_id" name="company_id" value="{{ $company->EntityID }}">

                    <div class="check_num_date input_row">
                        <div class="check_num w-50">
                            <label for="check_number">Check Number</label>
                            <input type="text" id="check_number" name="check_number" onkeypress="return /^[0-9]+$/.test(event.key)"
                                value="{{ old('check_number') }}" tabindex="1" class="no-spinner" autofocus />
                            @error('check_number')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="check_date w-50">
                            <label for="check_date">Check Date</label>
                            <input type="text" id="check_date" name="check_date" value="{{ old('check_date') }}"
                                tabindex="2" placeholder="mm-dd-yyyy" readonly />
                            @error('check_date')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="check_amt input_row">
                        <label for="amount">Amount $</label>
                        <input type="text" id="amount" name="amount" value="{{ old('amount') }}" tabindex="3"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                            autocomplete="off" />
                        @error('amount')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($data->service_fees_type != null)
                        
                        @php
                            $service_fee = '';

                            if(old('amount')){
                                $amount = old('amount');
                                if($data->service_fees_type == 'percentage'){
                                    $fees = $amount * $data->service_fees / 100;
                                    $service_fee_amount =  '$ '.$fees;
                                }else if($data->service_fees_type == 'amount'){
                                    $fees = $data->service_fees;
                                    $service_fee_amount = '$ '.$fees;
                                }
                                 $total = $amount + $fees;
                            }

                            if($data->service_fees_type == 'percentage'){
                                $service_fee = $data->service_fees.' %';
                            }else if($data->service_fees_type == 'amount'){
                                $service_fee = '$ '.$data->service_fees;
                            }
                            

                        @endphp

                        <div class="row input_row text-end">
                                <div class="col-8 align-content-center">
                                    <label for="service_fee">
                                        Service Fee
                                        @if($data->service_fees_type == 'percentage')
                                            ( {{ $service_fee }} )
                                        @endif
                                    </label>
                                </div>
                                <div class="col-4"> 
                                    <input type="text" style="background-color: #F4F4F4;" disabled id="service_fee" name="service_fee" 
                                    value="{{ isset($service_fee_amount) ? $service_fee_amount : (($data->service_fees_type == 'percentage') ? '$ 0' : $data->service_fees) }}">
                                </div>
                        </div>

                        <div class="row input_row text-end">
                                <div class="col-8 align-content-center">
                                    <label for="total">Total</label>
                                </div>
                                <div class="col-4 d-flex align-items-center">
                                    <input type="text" style="background-color: #F4F4F4;" disabled id="total" name="total" value="{{ isset($total) ? '$ '.$total : '$ 0'}}">
                                </div>
                        </div>
                        
                    @endif

                    <div class="pay_to">Pay To: {{ $company->Name }}</div>

                    <div class="pay_from">Pay From:</div>

                    <div class="company_name input_row">
                        <label for="name">Company Name or First/Last Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            tabindex="4" />
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="email input_row">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" value="{{ old('email') }}"
                            tabindex="5" />
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="address input_row">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                            tabindex="6" />
                        @error('address')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="city_state_zip input_row">
                        <div class="city w-33">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                tabindex="7" />
                            @error('city')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="state w-33">
                            <label for="state">State</label>
                            <select tabindex="8" name="state" id="state" class="form-control">
                                <option value="">-- Select State --</option>
                                @php
                                    $states = [
                                        'Alabama',
                                        'Alaska',
                                        'Arizona',
                                        'Arkansas',
                                        'California',
                                        'Colorado',
                                        'Connecticut',
                                        'Delaware',
                                        'Florida',
                                        'Georgia',
                                        'Hawaii',
                                        'Idaho',
                                        'Illinois',
                                        'Indiana',
                                        'Iowa',
                                        'Kansas',
                                        'Kentucky',
                                        'Louisiana',
                                        'Maine',
                                        'Maryland',
                                        'Massachusetts',
                                        'Michigan',
                                        'Minnesota',
                                        'Mississippi',
                                        'Missouri',
                                        'Montana',
                                        'Nebraska',
                                        'Nevada',
                                        'New Hampshire',
                                        'New Jersey',
                                        'New Mexico',
                                        'New York',
                                        'North Carolina',
                                        'North Dakota',
                                        'Ohio',
                                        'Oklahoma',
                                        'Oregon',
                                        'Pennsylvania',
                                        'Rhode Island',
                                        'South Carolina',
                                        'South Dakota',
                                        'Tennessee',
                                        'Texas',
                                        'Utah',
                                        'Vermont',
                                        'Virginia',
                                        'Washington',
                                        'West Virginia',
                                        'Wisconsin',
                                        'Wyoming',
                                    ];
                                @endphp

                                @foreach ($states as $state)
                                    <option value="{{ $state }}"
                                        {{ old('state') == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="zip w-33">
                            <label for="zip">Zip</label>
                            <input type="text" id="zip" name="zip" value="{{ old('zip') }}"
                                tabindex="9" />
                            @error('zip')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="address input_row">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number"
                            value="{{ old('phone_number') }}" tabindex="10" />
                        @error('phone_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="bank_name input_row">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                            tabindex="11" />
                        @error('bank_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="routing_num input_row">
                        <label for="routing_number">Routing Number</label>
                        <input type="number" id="routing_number" name="routing_number"
                            value="{{ old('routing_number') }}" tabindex="12" class="no-spinner" maxlength="9"
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0,9);" />
                        @error('routing_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="account_num input_row">
                        <label for="account_number">Account Number</label>
                        <input type="number" id="account_number" name="account_number" class="no-spinner"
                            value="{{ old('account_number') }}" tabindex="13" />
                        @error('account_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="account_num_verify input_row">
                        <label for="account_number_verify">Account Number (re-verify)</label>
                        <input type="number" id="account_number_verify" name="account_number_verify"
                            class="no-spinner" value="{{ old('account_number_verify') }}" tabindex="14" />
                        <span id="error_verify" class="error" style="display:none;">Account number does not
                            match</span>
                        @error('account_number_verify')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="memo input_row">
                        <label for="Memo">Memo (optional)</label>
                        <input type="text" id="Memo" name="Memo" value="{{ old('Memo') }}"
                            tabindex="15" />
                        @error('Memo')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="submit_btn">
                        <input type="submit" id="Button2" name="Submit" value="Submit" tabindex="16" />
                    </div>

                    {{-- <input type="hidden" name="g-recaptcha-token" id="g-recaptcha-token"> --}}

                </form>
            </div>
            <div class="content_right d-flex flex-column">
                <img src="{{ asset('assets/img/check-sample.jpg') }}" alt="check format img" />
                <div class="company_desc my-4">
                    @if (!empty($data->page_desc) && $data->page_desc != '<p><br></p>')
                        {!! $data->page_desc !!}
                    @else
                        <strong> What is a Virtual Check? </strong><br><br>
                        <p>
                            Using special software and the information provided, you authorize us to produce a legal
                            check
                            for
                            this transaction only. Our software prints this check that we deposit into our account just
                            as
                            we
                            would one you mailed us. The difference is time. Instead of waiting days for the mail - we
                            can
                            process your order in moments. You will then receive the check we produced with your other
                            cancelled
                            checks in your normal bank statement.

                            The requested information is not confidential as it is located on the front of your check.
                        </p>
                        <br>
                        <strong>How do I do this? Very simple. First, get out your check book.</strong><br><br>
                        <p>
                            Go to the next available check you would normally write. For your records, fill it out to us
                            in
                            the
                            amount due. Next, write VOID across this check. You will provide us with the actual check
                            number
                            -
                            but not the actual check. Next, use the graphic below to locate the information we will
                            need.
                            You
                            can place the numbers in red on your voided check for reference when you complete the Secure
                            Virtual
                            Check
                        </p>
                    @endif
                </div>
                <a href="https://echecksystems.com/"><img src="{{ asset('assets/img/echeck-banner.jpg') }}"
                        alt="echeck banner img" /></a>
            </div>
        </div>
    </section>
    <footer class="footer-section">
        <p>Power By <a href="https://echecksystems.com/" target="_blank">Echeck Systems</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"
        integrity="sha512-LsnSViqQyaXpD4mBBdRYeP6sRwJiJveh2ZIbW41EBrNmKxgr/LFZIiWT6yr+nycvhvauz8c2nYMhrP80YhG7Cw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const input = document.getElementById('account_number_verify');

        // Prevent paste
        input.addEventListener('paste', (e) => {
            e.preventDefault();
        });

        // Prevent copy
        input.addEventListener('copy', (e) => {
            e.preventDefault();
        });

        // Prevent cut
        input.addEventListener('cut', (e) => {
            e.preventDefault();
        });

        // Optionally prevent right-click context menu on the input
        input.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });

        // Optionally prevent keyboard shortcuts Ctrl+V, Ctrl+C, Ctrl+X
        input.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && ['v', 'c', 'x'].includes(e.key.toLowerCase())) {
                e.preventDefault();
            }
        });

        $(document).ready(function() {
            $('#check_date').datepicker({
                format: 'mm-dd-yyyy',
                autoclose: true,
                todayHighlight: true
            });

            @if($data->service_fees > 0)
                $('#amount').on('input', function() {
                    let amount = $(this).val();
                    let service_fee = "{{ $data->service_fees ?? 0 }}";
                    let service_fee_type = "{{ $data->service_fees_type }}";
                    let total = $('#total');
                    let service_fee_input = $('#service_fee');
                    let service_fee_amount = 0;

                    if(amount > 0){
                        amount = parseFloat(amount) || 0;
                        service_fee = parseFloat(service_fee) || 0;

                        if(service_fee_type == 'percentage'){
                            service_fee_amount = (amount * service_fee) / 100;
                            amount = amount + service_fee_amount;
                        }else if(service_fee_type == 'amount'){
                            service_fee_amount = service_fee;
                            amount = amount + service_fee;
                        }

                        total.val('$ '+Number(amount).toFixed(2));
                        service_fee_input.val('$ '+Number(service_fee_amount).toFixed(2));
                    }
                });
            @endif
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        Inputmask({
            mask: "999-999-9999",
            placeholder: "", // No placeholders
            showMaskOnHover: false, // Don't show mask on hover
            showMaskOnFocus: false, // Don't show mask on focus
        }).mask("#phone_number");
    </script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script>
        const validation = new JustValidate('#webForm', {
            errorLabelCssClass: 'text-danger'
        });

        validation
            .addField('#account_number', [{
                rule: 'required',
                errorMessage: 'Account number is required',
            }, ])
            .addField('#account_number_verify', [{
                    rule: 'required',
                    errorMessage: 'Please verify the account number',
                },
                {
                    validator: (value, fields) => {
                        return value === fields['#account_number'].elem.value;
                    },
                    errorMessage: 'Account numbers do not match',
                },
            ]).onSuccess((event) => {
                event.target.submit();
            });
    </script> --}}
    <script>
        function checkMatch() {
            const acc = $('#account_number').val();
            const verify = $('#account_number_verify').val();

            if (!acc.startsWith(verify)) {
                $('#error_verify').show();
                return false;
            } else {
                $('#error_verify').hide();
                return true;
            }
        }

        function checkExactMatch() {
            const acc = $('#account_number').val();
            const verify = $('#account_number_verify').val();

            if (acc != verify) {
                $('#error_verify').show();
                return false;
            } else {
                $('#error_verify').hide();
                return true;
            }
        }

        $(document).ready(function() {
 // Hide server-side validation errors when user starts typing
            
            $('#webForm input, #webForm select').on('input change', function() {
                // Find the error div - it's typically a sibling or within the same parent container
                const $input = $(this);
                const inputId = $input.attr('id');
                
                // For account_number_verify, we need to hide the server-side error div, not the client-side span
                if (inputId === 'account_number_verify') {
                    // Find the server-side error div (exclude the #error_verify span)
                    $input.parent().find('div.error').hide();
                } else {
                    // For other fields, try to find error as next sibling first
                    let $errorDiv = $input.next('.error');
                    // If not found, look in the parent container (for nested structures)
                    if ($errorDiv.length === 0) {
                        $errorDiv = $input.parent().find('.error').first();
                    }
                    // Hide the error if found (but not the #error_verify span)
                    if ($errorDiv.length && !$errorDiv.is('#error_verify')) {
                        $errorDiv.hide();
                    }
                }
            });

            // Hide server-side validation errors when user starts typing
            $('#webForm input, #webForm select').on('input change', function() {
                // Find the error div - it's typically a sibling or within the same parent container
                const $input = $(this);
                const inputId = $input.attr('id');
                
                // For account_number_verify, we need to hide the server-side error div, not the client-side span
                if (inputId === 'account_number_verify') {
                    // Find the server-side error div (exclude the #error_verify span)
                    $input.parent().find('div.error').hide();
                } else {
                    // For other fields, try to find error as next sibling first
                    let $errorDiv = $input.next('.error');
                    // If not found, look in the parent container (for nested structures)
                    if ($errorDiv.length === 0) {
                        $errorDiv = $input.parent().find('.error').first();
                    }
                    // Hide the error if found (but not the #error_verify span)
                    if ($errorDiv.length && !$errorDiv.is('#error_verify')) {
                        $errorDiv.hide();
                    }
                }
            });

            // Live check while typing
            $('#account_number_verify').on('input', function() {
                checkMatch();
            });

            $('#account_number_verify').on('blur', function() {
                checkExactMatch();
            });

            $('#account_number').on('input', function() {
                checkMatch();
            });

            // Prevent form submission if not matching
            $('#webForm').on('submit', function(e) {
                const acc = $('#account_number').val();
                const verify = $('#account_number_verify').val();
                if (acc !== verify) {
                    $('#error_verify').show();
                    e.preventDefault();
                    return false;
                } else {
                    $('#error_verify').hide();
                    return true;
                }
            });
        });
    </script>
    {{-- <script src="https://www.google.com/recaptcha/api.js?render={{ env('CAPTCHA_SITE_KEY') }}"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ env('CAPTCHA_SITE_KEY') }}', {
                action: 'form_submit'
            }).then(function(token) {
                document.getElementById('g-recaptcha-token').value = token;
            }).catch(function(err) {
                console.error('reCAPTCHA execute error:', err);
            });
        });
    </script> --}}
</body>

</html>
