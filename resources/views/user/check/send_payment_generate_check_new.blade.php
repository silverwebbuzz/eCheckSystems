@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

@php
    $base_url = url('/');
@endphp

<!-- Vendor Styles -->
@section('vendor-style')
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script&family=Allura&family=Pacifico&family=Sacramento&family=Alex+Brush&family=Parisienne&family=Marck+Script&family=Tangerine&family=Pinyon+Script&family=Courgette&family=Kaushan+Script&family=Yellowtail&family=Satisfy&family=Italianno&family=Arizonia&family=Cookie&family=Meddon&family=Bilbo&family=Norican&family=Herr+Von+Muellerhoff&family=Rochester&family=Fondamento&family=Euphoria+Script&family=Bad+Script&family=Over+the+Rainbow&family=Calligraffitti&family=Homemade+Apple&family=Patrick+Hand&family=Indie+Flower&family=Gloria+Hallelujah&family=Reenie+Beanie&family=La+Belle+Aurore&family=Rock+Salt&family=Waiting+for+the+Sunrise&family=Allan&family=Shadows+Into+Light&family=Shadows+Into+Light+Two&family=Loved+by+the+King&family=Give+You+Glory&family=Mr+Dafoe&family=Mr+De+Haviland&family=Mrs+Saint+Delafield&family=Petit+Formal+Script&family=Rouge+Script&family=Ruthie&family=Seaweed+Script&family=Stalemate&family=Nanum+Pen+Script&family=Caveat&family=Covered+By+Your+Grace&family=Amatic+SC&family=Architects+Daughter&family=Patrick+Hand+SC&family=Chewy&family=Sue+Ellen+Francisco&family=Just+Another+Hand&family=Pangolin&family=Kalam&family=Cedarville+Cursive&family=Zeyada&family=Nothing+You+Could+Do&family=Just+Me+Again+Down+Here&family=The+Girl+Next+Door&family=Square+Peg&family=Charmonman&family=Dekko&family=Gaegu&family=Birthstone+Bounce&family=Comforter+Brush&family=Yomogi&family=Moon+Dance&family=Swanky+and+Moo+Moo&family=Delius+Swash+Caps&family=Sunshiney&family=Edu+SA+Beginner&family=Water+Brush&family=Twinkle+Star&family=Ms+Madi&family=Grand+Hotel&family=Send+Flowers&family=Playwrite&family=Niconne&family=Kristi&display=swap"
        rel="stylesheet">
    <style>
        .kbw-signature {
            width: 350px;
            height: 100px;
            border: none !important;
            /* margin: 20px; */
        }

        #sig canvas {
            width: 350px;
            height: 100px;
            border: 1px solid #555;
        }

        #sign img {
            width: 330px !important;
            height: 130px !important;
        }

        #old_sign img {
            width: 330px !important;
            height: 130px !important;
        }

        input,
        select {
            border: 1px solid !important;
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
    </style>
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
    @vite(['resources/assets/js/ui-modals.js'])
    <script type="text/javascript">
        var sig = $('#sig').signature({
            syncField: '#signature64',
            syncFormat: 'PNG'
        });

        var existingSignature = {!! json_encode(!empty($old_sign->Sign) ? asset('sign/' . $old_sign->Sign) : '') !!};

        if (existingSignature) {
            var img = new Image();
            img.crossOrigin = "Anonymous"; // Prevent CORS issues when converting to Base64
            img.src = existingSignature;

            img.onload = function () {
                var canvas = $('#sig canvas')[0];
                var ctx = canvas.getContext("2d");

                // Draw existing signature on canvas
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                // Convert canvas content to Base64
                var base64Signature = canvas.toDataURL("image/png");

                // Save Base64 signature to hidden field
                $("#signature64").val(base64Signature);
            };
        }


        $('#clear').click(function (e) {

            e.preventDefault();

            sig.signature('clear');

            $("#signature64").val('');

        });
    </script>

    <script>
        var base_url = "{{ $base_url }}";

        function toggleItemization() {

            // Get the hidden input element
            var itemizationInput = document.getElementById('itemization');

            // Toggle the value of the hidden field
            if (itemizationInput.value === '0') {
                itemizationInput.value = '1';
            } else {
                itemizationInput.value = '0';
            }

            // Toggle the visibility of the table (gridTable)
            $('#gridTable').toggle();
        }

        $(document).ready(function () {

            $(document).find('.mydatepicker').flatpickr({
                dateFormat: 'm-d-Y',
                monthSelectorType: 'static'
            });
            $('#payee').on('change', function () {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue == 'add_other_company') {
                    $('#payee-edit').addClass('d-none');
                    $('#payee_id').val('');
                    $('#payee-name').val('');
                    $('#payee-email').val('');
                    $('#payee_h').text('Add');
                    $('#payeeModel').modal('show');
                } else {
                    $.ajax({
                        url: "{{ route('get_payee', ':id') }}".replace(':id', id) + '?type=SP',
                        method: 'GET',
                        success: function (response) {
                            $('#payee-edit').removeClass('d-none');

                            $('#payee_id').val(response.payee.EntityID);
                            $('#payee-name').val(response.payee.Name);
                            $('#payee-email').val(response.payee.Name);
                        }
                    });
                }
            });


            $('#add-payee-btn').on('click', function (event) {
                event.preventDefault();
                var id = $('#payee_id').val();

                // Collect form data manually
                let formData = {
                    _token: "{{ csrf_token() }}", // Include CSRF token manually
                    name: $('#payee-name').val(),
                    email: $('#payee-email').val(),
                    type: 'Payee',
                    category: 'SP',
                    id: id
                };


                // Clear any previous error messages
                $('.text-danger').remove();

                // Send Ajax request
                $.ajax({
                    url: "{{ route('user.add-payee') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function (key, value) {
                                console.log('#add-payee #' + key);

                                $('#payee-' + key).closest('.col-md-6').append(
                                    '<span class="text-danger">' + value[0] +
                                    '</span>'
                                );
                            });
                        } else if (response.success) {
                            $('#payeeModel').modal('hide');
                            // Success message

                            if (id) {
                                $('#payee option:selected').text(response.payee.Name);
                            } else {
                                let newOption =
                                    `<option value="${response.payee.EntityID}" selected>${response.payee.Name}</option>`;
                                $('#payee').append(newOption).val(response.payee.EntityID);
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            $('#payor').on('change', function () {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue === 'add_other_payor') {
                    $('#payor-edit').addClass('d-none');
                    $('#payorModel').modal('show');
                    $('#payor_id').val('');
                    $('#add-payor #name').val('');
                    $('#add-payor #email').val('');
                    $('#add-payor #address1').val('');
                    $('#add-payor #city').val('');
                    $('#add-payor #state').val('');
                    $('#add-payor #zip').val('');
                    $('#add-payor #bank_name').val('');
                    $('#add-payor #account_number').val('');
                    $('#add-payor #routing_number').val('');
                    ('#payor_h').text('Add');
                } else {
                    $.ajax({
                        url: "{{ route('get_payor', ':id') }}".replace(':id', id) + '?type=SP',
                        method: 'GET',
                        success: function (response) {
                            $('#payor-edit').removeClass('d-none');

                            var address = response.payor.Address1;

                            $('#payor_id').val(response.payor.EntityID);
                            $('#address').val(address);
                            $('#city').val(response.payor.City);
                            $('#state').val(response.payor.State);
                            $('#zip').val(response.payor.Zip);
                            $('#account_number').val(response.payor.AccountNumber);
                            $('#routing_number').val(response.payor.RoutingNumber);
                            $('#confirm_account_number').val(response.payor.AccountNumber);

                            $('#add-payor #name').val(response.payor.Name);
                            $('#add-payor #email').val(response.payor.Email);
                            $('#add-payor #address1').val(response.payor.Address1);
                            $('#add-payor #city').val(response.payor.City);
                            $('#add-payor #state').val(response.payor.State);
                            $('#add-payor #zip').val(response.payor.Zip);
                            $('#add-payor #bank_name').val(response.payor.BankName);
                            $('#add-payor #account_number').val(response.payor.AccountNumber);
                            $('#add-payor #routing_number').val(response.payor.RoutingNumber);
                        }
                    });
                }
            });

            $('#add-payor-btn').on('click', function (event) {
                event.preventDefault();
                var id = $('#payor_id').val();

                // Collect form data manually
                let formData = {
                    _token: "{{ csrf_token() }}", // Include CSRF token manually
                    name: $('#add-payor #name').val(),
                    email: $('#add-payor #email').val(),
                    address1: $('#add-payor #address1').val(),
                    city: $('#add-payor #city').val(),
                    state: $('#add-payor #state').val(),
                    zip: $('#add-payor #zip').val(),
                    bank_name: $('#add-payor #bank_name').val(),
                    account_number: $('#add-payor #account_number').val(),
                    routing_number: $('#add-payor #routing_number').val(),
                    type: 'Payor',
                    category: 'SP',
                    id: id
                };

                // Clear any previous error messages
                $('.text-danger').remove();

                // Send Ajax request
                $.ajax({
                    url: "{{ route('user.add-payor') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function (key, value) {
                                $('#add-payor #' + key).closest('.col-md-6').append(
                                    '<span class="text-danger">' + value[0] +
                                    '</span>'
                                );
                            });
                        } else if (response.success) {

                            $('#payorModel').modal('hide');
                            // Success message

                            if (id) {
                                $('#payor option:selected').text(response.payor.Name);
                            } else {
                                let newOption =
                                    `<option value="${response.payor.EntityID}" selected>${response.payor.Name}</option>`;
                                $('#payor').append(newOption).val(response.payor.EntityID);
                            }

                            var address = response.payor.Address1;

                            $('#address').val(address);
                            $('#city').val(response.payor.City);
                            $('#state').val(response.payor.State);
                            $('#zip').val(response.payor.Zip);
                            $('#account_number').val(response.payor.AccountNumber);
                            $('#routing_number').val(response.payor.RoutingNumber);
                            $('#confirm_account_number').val(response.payor.AccountNumber);

                            $('#payor_id').val(response.payor.EntityID);
                            $('#add-payor #name').val(response.payor.Name);
                            $('#add-payor #email').val(response.payor.Email);
                            $('#add-payor #address1').val(response.payor.Address1);
                            $('#add-payor #city').val(response.payor.City);
                            $('#add-payor #state').val(response.payor.State);
                            $('#add-payor #zip').val(response.payor.Zip);
                            $('#add-payor #bank_name').val(response.payor.BankName);
                            $('#add-payor #account_number').val(response.payor.AccountNumber);
                            $('#add-payor #routing_number').val(response.payor.RoutingNumber);
                            $('#add-payor')[0].reset(); // Reset form
                        }
                    },
                    error: function (xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            //Print value on check
            $("#check_date").on("change", function () {
                const selectedDate = $(this).val();
                $("#c_check_date").text(selectedDate || "XX-XX-XXXX");
            });

            $("#check_number").on("input", function () {
                const check_number = $(this).val();
                $("#verify_check_number").val(check_number);
            });

            $("#amount").on("input", function () {
                const amount = $(this).val();

                $.ajax({
                    url: "{{ route('amount_word') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        amount: amount,
                    },
                    success: function (response) {
                        $("#c_amount").text(amount || "XXXX.XX");
                        $("#c_amount_word").text(response.word || "XXXXX XXXX XXXX");
                    }
                });
            });

            $("#memo").on("input", function () {
                const memo = $(this).val();
                $("#c_memo").text(memo || "XXXXXXX XXXX XXXX XX");
            });

            $('#payor_close').on('click', function (e) {
                event.preventDefault();
                $('#payorModel').modal('hide');
                $("#payor").val("");
            });
            $('#payor-edit').on('click', function (e) {
                event.preventDefault();
                $('#payorModel').modal('show');
                $('#payor_h').text('Edit');
            });
            $('#signature-edit').on('click', function (e) {
                event.preventDefault();
                $('#signModel').modal('show');
                $('.sign_h').text('Edit');
            });

            $('#payee-edit').on('click', function (e) {
                event.preventDefault();
                $('#payeeModel').modal('show');
                $('#payee_h').text('Edit');
            });

            $('#is_sign').change(function (e) {
                e.preventDefault();
                if ($(this).is(':checked')) {
                    $('.sing-box').removeClass('d-none'); // Show the signature field
                } else {
                    $('.sing-box').addClass('d-none'); // Hide the signature field
                }
            });

            $('#signature').on('change', function () {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if(selectedValue == '') {
                    $('#sign').addClass('d-none');
                    $('#sign').html('');
                    return;
                }
                if (selectedValue == 'add_new_signature') {
                    $('#signature-edit').addClass('d-none');
                    $('#signModel').modal('show');
                    $('#sign_id').val('');
                    $('#name').val('');
                    $('.sign_h').text('Add');
                } else {
                    $.ajax({
                        url: "{{ route('get_signature', ':id') }}".replace(':id', id),
                        method: 'GET',
                        success: function (response) {
                            $('#signature-edit').removeClass('d-none');
                            $('#sign').html('');
                            var existingSignature = base_url + '/sign/' + response.signature
                                .Sign;

                            $('#sign').removeClass('d-none');
                            $('#old_sign').addClass('d-none');

                            $('#sign').html(
                                '<img src="' + existingSignature + '" alt="sign" />');

                            $('#sign-name').val(response.signature.Name);
                            $('#sign_id').val(response.signature.Id);

                            if (existingSignature) {
                                var img = new Image();
                                img.crossOrigin =
                                    "Anonymous"; // Prevent CORS issues when converting to Base64
                                img.src = existingSignature;

                                img.onload = function () {
                                    var canvas = $('#sig canvas')[0];
                                    var ctx = canvas.getContext("2d");

                                    // Draw existing signature on canvas
                                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                                    // Convert canvas content to Base64
                                    var base64Signature = canvas.toDataURL("image/png");

                                    // Save Base64 signature to hidden field
                                    $("#signature64").val(base64Signature);
                                };
                            }
                        }

                    });
                }
            });

            $('#add-sign-btn').on('click', function (event) {
                event.preventDefault();
                var id = $('#sign_id').val();

                // Collect form data manually
                let formData = {
                    _token: "{{ csrf_token() }}", // Include CSRF token manually
                    name: $('#sign-name').val(),
                    signature: $('#signature64').val(),
                    id: id
                };


                // Clear any previous error messages
                $('.text-danger').text('');

                // Send Ajax request
                $.ajax({
                    url: "{{ route('store_sign') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.errors) {
                            $.each(response.errors, function (key, value) {
                                $('#error-' + key).text(value[0]);
                            });
                        } else if (response.success) {
                            $('#signModel').modal('hide');
                            // Success message

                            if (id) {
                                $('#signature option:selected').text(response.signature.Name);
                            } else {
                                let newOption =
                                    `<option value="${response.signature.Id}" selected>${response.signature.Name}</option>`;
                                $('#signature').append(newOption).val(response.signature.Id);
                            }

                            $('#sign').html('');
                            var existingSignature = base_url + '/sign/' + response.signature
                                .Sign;

                            $('#sign').removeClass('d-none');
                            $('#old_sign').addClass('d-none');

                            $('#sign').html(
                                '<img src="' + existingSignature + '" alt="sign" />');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            $('#check_number').on('input', function () {
                const check_number = $(this).val();

                $.ajax({
                    url: "{{ route('check.check_number_exists') }}",
                    method: 'GET',
                    data: {
                        check_number: check_number
                    },
                    success: function (response) {
                        if (response.exists) {
                            $('#check_number_error').text('Check number already exists.');
                        } else {
                            $('#check_number_error').text('');
                        }
                    }
                })
            });

            $(document).on('click', '.removeRow', function () {
                $(this).closest('tr').remove();
            });
        });

        var grid_row_count = 0;

        function addRow(gridHistoryIDs) {

            $.ajax({
                url: "{{ url('/get-grids') }}",
                method: 'GET',
                data: {
                    _token: "{{ csrf_token() }}",
                    grid_row_count: grid_row_count,
                    grid_history_ids: gridHistoryIDs
                },
                success: function (response) {

                    if (response.status == true) {
                        $('#gridTable tbody').append(response.html);
                        $(document).find('.mydatepicker').flatpickr({
                            dateFormat: 'm-d-Y',
                            monthSelectorType: 'static'
                        });

                        grid_row_count++;
                    }
                }
            })
        }

        const fonts = [
            'Great Vibes', 'Dancing Script', 'Allura', 'Pacifico', 'Sacramento', 'Alex Brush', 'Parisienne',
            'Marck Script', 'Tangerine', 'Pinyon Script', 'Courgette', 'Kaushan Script', 'Yellowtail',
            'Satisfy', 'Italianno', 'Arizonia', 'Cookie', 'Meddon', 'Bilbo', 'Norican',
            'Herr Von Muellerhoff', 'Rochester', 'Fondamento', 'Euphoria Script', 'Bad Script',
            'Over the Rainbow', 'Calligraffitti', 'Homemade Apple', 'Patrick Hand', 'Indie Flower',
            'Gloria Hallelujah', 'Reenie Beanie', 'La Belle Aurore', 'Rock Salt', 'Waiting for the Sunrise',
            'Allan', 'Shadows Into Light', 'Shadows Into Light Two', 'Loved by the King', 'Give You Glory',
            'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Petit Formal Script', 'Rouge Script',
            'Ruthie', 'Seaweed Script', 'Stalemate', 'Nanum Pen Script', 'Caveat', 'Covered By Your Grace',
            'Amatic SC', 'Architects Daughter', 'Patrick Hand SC', 'Covered By Your Grace', 'Chewy',
            'Sue Ellen Francisco', 'Just Another Hand', 'Pangolin', 'Kalam', 'Cedarville Cursive', 'Zeyada',
            'Nothing You Could Do', 'Just Me Again Down Here', 'The Girl Next Door', 'Square Peg',
            'Charmonman', 'Dekko', 'Gaegu', 'Birthstone Bounce', 'Comforter Brush', 'Yomogi', 'Moon Dance',
            'Swanky and Moo Moo', 'Delius Swash Caps', 'Sunshiney', 'Edu SA Beginner', 'Water Brush',
            'Twinkle Star', 'Ms Madi', 'Grand Hotel', 'Send Flowers', 'Playwrite', 'Niconne', 'Kristi'
        ];

        let selectedSignature = null;
        let nameInput = '';

        function generatePreviews() {

            const name = document.getElementById('nameInput').value.trim();
            const container = document.getElementById('previewContainer');

            if (!name) {
                $('.alert-danger').text('Please enter name').fadeIn().delay(4000).fadeOut();
                return;
            }

            selectedSignature = null;
            container.innerHTML = '';
            container.classList.add('row', 'g-2');

            fonts.forEach(font => {
                const col = document.createElement('div');
                col.classList.add('col-md-4', 'col-sm-6', 'col-12', 'd-flex');

                const card = document.createElement('div');
                card.classList.add('card', 'signature-preview', 'shadow', 'mb-2', 'h-100', 'w-100');
                card.style.cursor = 'pointer';

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body', 'p-2', 'd-flex', 'align-items-center', 'justify-content-center');
                card.appendChild(cardBody);

                const div = document.createElement('div');
                div.classList.add('text-center');
                div.style.fontFamily = font;
                div.style.fontSize = '60px';
                div.style.lineHeight = 'normal';
                div.style.overflowWrap = 'anywhere';
                div.innerText = name;

                card.onclick = () => {
                    selectedSignature = { text: name, font: font, size: 45 };

                    // Draw initial canvas preview
                    drawModalCanvas();

                    // Initialize slider and value text
                    document.getElementById('fontSizeSlider').value = selectedSignature.size;
                    document.getElementById('fontSizeValue').innerText = selectedSignature.size + 'px';

                    // Show modal
                    const myModal = new bootstrap.Modal(document.getElementById('fontSizeModal'));
                    myModal.show();
                };

                cardBody.appendChild(div);
                col.appendChild(card);
                container.appendChild(col);
            });
        }

        document.getElementById('fontSizeSlider').addEventListener('input', function () {
            selectedSignature.size = parseInt(this.value);
            document.getElementById('fontSizeValue').innerText = this.value + 'px';
            drawModalCanvas();
        });

        function drawModalCanvas() {
            const canvas = document.getElementById('signaturePreviewModalCanvas');
            const ctx = canvas.getContext('2d');

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // White background
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Draw the signature text
            ctx.font = `${selectedSignature.size}px '${selectedSignature.font}'`;
            ctx.fillStyle = '#000';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(selectedSignature.text, canvas.width / 2, canvas.height / 2);
        }

        document.getElementById('confirmSaveBtn').addEventListener('click', function () {
            nameInput = document.getElementById('nameInput').value.trim();

            if (!selectedSignature) {
                $('.alert-danger').text('Please select signature').fadeIn().delay(4000).fadeOut();
                return;
            }

            const canvas = document.getElementById('signaturePreviewModalCanvas');
            const dataUrl = canvas.toDataURL('image/png');

            fetch("{{ route('store_sign') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    signature: dataUrl,
                    name: nameInput
                })
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        $('#sign').html('');
                        $('#signature').append(
                            `<option value="${data.signature.Id}" selected>${data.signature.Name}</option>`
                        );
                        $('#sign').append(
                            `<img src="${base_url}/sign/${data.signature.Sign}" alt="sign" />`
                        );
                        $('#signature option#add_new_signature').appendTo('#signature');
                        $('#sign').removeClass('d-none');
                        $('#fontSizeModal').modal('hide');
                        $('#signModel').modal('hide');
                        $('html, body').scrollTop(0);
                        $('.alert-success').text('Signature saved successfully!').fadeIn().delay(4000).fadeOut();
                        
                    }
                });
        });
    </script>

@endsection

@section('content')
    @if (session('grid_error'))
        <div class="alert alert-danger">
            {{ session('grid_error') }}
        </div>

        <script>
            $(document).ready(function () {
                $('#gridTable').show();
            });
        </script>
    @endif
    <div class="alert alert-danger" style="display: none;"></div>
    <div class="alert alert-success" style="display: none;"></div>
    <form action="{{ route('check.send_payment_check_generate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mb-6" style="background: #d0dfff">
            <div class="card-header d-flex align-items-center justify-content-between mb-5">
                <h5 class="mb-0">Create Send Payment Check</h5>
                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                    &nbsp;&nbsp;
                    <a href="{{ route('check.send_payment') }}" class="btn btn-primary mr-4">
                        {{-- &nbsp; --}}
                        Back</a>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" id="id" name="id" value="{{ !empty($check->CheckID) ? $check->CheckID : '' }}">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            {{-- <label class="col-sm-12 col-form-label" for="account-name">Account Holder's Name:</label>
                            --}}
                            <div class="col-sm-8 d-flex align-items-center gap-1">
                                <select id="payor" name="payor" class="form-control">
                                    <option value="" selected>Select Pay From</option>
                                    @foreach ($payors as $payor)
                                        @php
                                            if (!empty($payor->Email)) {
                                                $name = $payor->Name . ' (' . $payor->Email . ')';
                                            } else {
                                                $name = $payor->Name;
                                            }
                                        @endphp
                                        <option value="{{ $payor->EntityID }}" {{ old('payor', $check->PayorID ?? '') == $payor->EntityID ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                    <option value="" id="add_other_payor" style="font-weight: bold;">Add New Payors
                                    </option>
                                </select>
                                <span id="payor-edit" class="{{ !empty($check->PayorID) ? '' : 'd-none' }}"><i
                                        class="ti ti-pencil me-1"></i></span>
                            </div>
                            @if ($errors->has('payor'))
                                <span class="text-danger">
                                    {{ $errors->first('payor') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            <div class="col-sm-5 p-0">
                                @php
                                    $checkNumber = '1000';

                                    if (old('check_number')) {
                                        $checkNumber = old('check_number');
                                    } elseif (!empty($check->CheckNumber) && $check->CheckNumber) {
                                        $checkNumber = $check->CheckNumber;
                                    } elseif ($lastCheck) {
                                        if ($lastCheck->CheckNumber) {
                                            $checkNumber = $lastCheck->CheckNumber + 1;
                                        }
                                    }
                                @endphp
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-black"
                                        style="pointer-events: none; border:1px solid;">EC</span>
                                    <input type="text" id="check_number" name="check_number" class="form-control no-spinner"
                                        placeholder="Check Number" maxlength="10" oninput="" value="{{ $checkNumber }}"
                                        autocomplete="off">
                                </div>

                                @if ($errors->has('check_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col text-end">
                                <span id="check_number_error" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-6">
                        <div class="row">
                            {{-- <label class="col-sm-12 col-form-label" for="street-address">Your Street Address:</label>
                            --}}
                            <div class="col-sm-8">
                                <input type="text" id="address" name="address" class="form-control"
                                    placeholder="Your Street Address" readonly
                                    value="{{ old('address', $old_payor->Address1 ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            {{-- <label class="col-sm-12 col-form-label" for="check_date">Date:</label> --}}
                            <div class="col-sm-4 p-0">
                                <input type="text" id="check_date" name="check_date" class="mydatepicker form-control"
                                    placeholder="MM-DD-YYYY"
                                    value="{{ old('check_date', !empty($check->ExpiryDate) ? $check->ExpiryDate : now()->format('m-d-Y')) }}" />
                                @if ($errors->has('check_date'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_date') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="city" name="city" class="form-control" placeholder="City"
                                            readonly
                                            value="{{ !empty($old_payor->City) && $old_payor->City ? $old_payor->City : old('city') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="state" name="state" class="form-control" placeholder="State"
                                            readonly
                                            value="{{ !empty($old_payor->State) && $old_payor->State ? $old_payor->State : old('state') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="zip" name="zip" class="form-control" placeholder="Zip"
                                            readonly
                                            value="{{ !empty($old_payor->Zip) && $old_payor->Zip ? $old_payor->Zip : old('zip') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 46px !important;">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4 col-form-label" for="account-name"
                                style="font-size: 15px;font-weight: bold;">Pay to the
                                Order
                                of:</label>
                            <div class="col-sm-8 d-flex align-items-center gap-1">
                                <select id="payee" name="payee" class="form-control" style="font-size: 16px;">
                                    <option value="" selected>Select Pay To</option>
                                    @foreach ($payees as $payee)
                                        @php
                                            if (!empty($payee->Email)) {
                                                $name = $payee->Name . ' (' . $payee->Email . ')';
                                            } else {
                                                $name = $payee->Name;
                                            }
                                        @endphp
                                        @if (!empty($payee->Email))
                                        @endif
                                        <option value="{{ $payee->EntityID }}" {{ old('payee', $check->PayeeID ?? '') == $payee->EntityID ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                    <option value="" id="add_other_company" style="font-weight: bold;">Add New
                                        Payee
                                    </option>
                                </select>
                                <span id="payee-edit" class="{{ !empty($check->PayeeID) ? '' : 'd-none' }}"><i
                                        class="ti ti-pencil me-1"></i></span>
                                @if ($errors->has('payee'))
                                    <br>
                                    <span class="text-danger">
                                        {{ $errors->first('payee') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4 col-form-label" for="amount"
                                style="font-size: 15px;font-weight: bold;text-align: right;">Amount: $</label>
                            <div class="col-sm-8">
                                <input type="text" id="amount" name="amount" style="font-size: 16px;"
                                    onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" autocomplete="off"
                                    value="{{ !empty($check->Amount) && $check->Amount ? $check->Amount : old('amount') }}">
                                @if ($errors->has('amount'))
                                    <br>
                                    <span class="text-danger">
                                        {{ $errors->first('amount') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 40px">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-8">
                                <input type="text" id="memo" name="memo" placeholder="Memo" class="form-control"
                                    value="{{ !empty($check->Memo) && $check->Memo ? $check->Memo : old('memo') }}">
                                @if ($errors->has('memo'))
                                    <span class="text-danger">
                                        {{ $errors->first('memo') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            <div class="col-sm-8 d-flex align-items-center gap-1">
                                <select id="signature" name="signature_id" class="form-control" style="font-size: 16px;">
                                    <option value="" selected>Select Signature</option>
                                    @foreach ($userSignatures as $userSignature)
                                        <option value="{{ $userSignature->Id }}" {{ old('signature_id', $old_sign?->Id ?? '') == $userSignature->Id ? 'selected' : '' }}>
                                            {{ $userSignature->Name }}
                                        </option>
                                    @endforeach
                                    <option value="" id="add_new_signature" style="font-weight: bold;">Add New
                                        Signature</option>
                                </select>
                                <span id="signature-edit" class="{{ !empty($old_sign->Id) ? '' : 'd-none' }}"><i
                                        class="ti ti-pencil me-1"></i></span>
                            </div>
                            @if ($errors->has('signature_id'))
                                <br>
                                <span class="text-danger">
                                    {{ $errors->first('signature_id') }}
                                </span>
                            @endif
                            <div class="col-sm-12 mt-3">
                                <div class="col-sm-12 @if (!old('signature_id')) d-none @endif" id="sign">
                                    @if (old('signature_id'))
                                        <img src="{{ asset('sign/' . \App\Models\UserSignature::find(old('signature_id'))->Sign) }}"
                                            alt="Sign">
                                    @endif
                                </div>
                                @if (!empty($old_sign) && !old('signature_id'))
                                    <div class="col-sm-12" id="old_sign">
                                        <img src="{{ asset('sign/' . $old_sign->Sign) }}" alt="Sign">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center" style="margin-top: 30px">
                    <div class="col-sm-3">
                        <input type="text" id="verify_check_number" name="verify_check_number" placeholder="Check Number"
                            class="form-control" readonly value="{{ $checkNumber }}">
                    </div>
                    <div class="col-sm-3">
                        <input type="number" id="routing_number" name="routing_number" class="form-control"
                            placeholder="Routing Number" readonly
                            value="{{ !empty($old_payor->RoutingNumber) && $old_payor->RoutingNumber ? $old_payor->RoutingNumber : old('routing_number') }}">
                    </div>
                    <div class="col-sm-3">
                        <input type="number" id="account_number" name="account_number" class="form-control"
                            placeholder="Account Number" readonly
                            value="{{ !empty($old_payor->AccountNumber) && $old_payor->AccountNumber ? $old_payor->AccountNumber : old('account_number') }}">
                    </div>
                </div>
                <div class="modal fade" id="payorModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span id="payor_h">Add</span>
                                    Payor
                                </h5>
                                <button type="button" class="btn-close" id="payor_close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="payor_id" id="payor_id" value={{ !empty($old_payor->EntityID) ? $old_payor->EntityID : '' }} />
                            <div class="modal-body">
                                <div class="row g-6" id="add-payor">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">Name</label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ !empty($old_payor->Name) ? $old_payor->Name : old('name') }}" />
                                        @if ($errors->has('name'))
                                            <span class="text-danger">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="text" name="email" id="email" class="form-control"
                                            value="{{ !empty($old_payor->Email) ? $old_payor->Email : old('email') }}" />
                                        @if ($errors->has('email'))
                                            <span class="text-danger">
                                                {{ $errors->first('email') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="address1">Address</label>
                                        <textarea id="address1" name="address1"
                                            class="form-control">{{ !empty($old_payor->Address1) ? $old_payor->Address1 : old('address1') }}</textarea>
                                        @if ($errors->has('address1'))
                                            <span class="text-danger">
                                                {{ $errors->first('address1') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" name="city" id="city" class="form-control"
                                            value="{{ !empty($old_payor->City) ? $old_payor->City : old('city') }}" />
                                        @if ($errors->has('city'))
                                            <span class="text-danger">
                                                {{ $errors->first('city') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="state">State</label>
                                        <select name="state" id="state" class="form-control">
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
                                                <option value="{{ $state }}" {{ !empty($old_payor->state) && $old_payor->state ? 'selected' : '' }}>
                                                    {{ $state }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="zip">Zip</label>
                                        <input type="text" name="zip" id="zip" class="form-control"
                                            value="{{ !empty($old_payor->Zip) ? $old_payor->Zip : old('zip') }}" />
                                        @if ($errors->has('zip'))
                                            <span class="text-danger">
                                                {{ $errors->first('zip') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="bank_name">Bank Name</label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                                            value="{{ !empty($old_payor->BankName) ? $old_payor->BankName : old('bank_name') }}" />
                                        @if ($errors->has('bank_name'))
                                            <span class="text-danger">
                                                {{ $errors->first('bank_name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_number">Account Number</label>
                                        <input type="text" inputmode="numeric" pattern="[0-9]*" name="account_number"
                                            id="account_number" class="form-control"
                                            value="{{ !empty($old_payor->AccountNumber) ? $old_payor->AccountNumber : old('account_number') }}" />
                                        @if ($errors->has('account_number'))
                                            <span class="text-danger">
                                                {{ $errors->first('account_number') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="routing_number">Routing Number</label>
                                        <input type="text" name="routing_number" id="routing_number" class="form-control"
                                            value="{{ !empty($old_payor->RoutingNumber) ? $old_payor->RoutingNumber : old('routing_number') }}"
                                            maxlength="9"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0,9);" />
                                        @if ($errors->has('routing_number'))
                                            <span class="text-danger">
                                                {{ $errors->first('routing_number') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="type" id="type" value="Payor" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                <button id="add-payor-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="payeeModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span class="payee_h">Add</span>
                                    Payee
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="payee_id" id="payee_id"
                                value="{{ !empty($old_payee->EntityID) ? $old_payee->EntityID : '' }}">
                            <div class="modal-body">
                                <div class="row g-6" id="add-payee">
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-name">Name</label>
                                        <input type="text" name="name" id="payee-name" class="form-control"
                                            value="{{ !empty($old_payee->Name) ? $old_payee->Name : old('name') }}" />
                                        @if ($errors->has('name'))
                                            <span class="text-danger">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-email">Email</label>
                                        <input type="text" name="payee-email" id="payee-email" class="form-control"
                                            value="{{ !empty($old_payee->Email) ? $old_payee->Email : old('email') }}" />
                                        @if ($errors->has('payee-email'))
                                            <span class="text-danger">
                                                {{ $errors->first('payee-email') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="type" id="type" value="Payee" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                <button id="add-payee-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="signModel" tabindex="-1" aria-hidden="true" style="z-index: 1100;">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span class="sign_h">Add </span>Signature
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="sign_id" id="sign_id"
                                value="{{ !empty($old_sign->Id) ? $old_sign->Id : '' }}">
                            <div class="modal-body">
                                <div class="card mb-6">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-8">
                                                <input type="text" id="nameInput" class="form-control"
                                                    placeholder="Enter your name" autocomplete="off">
                                            </div>
                                            <div class="col-4">
                                                <button type="button" class="btn btn-primary"
                                                    onclick="generatePreviews()">Generate</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <div class="preview-container text-center" id="previewContainer">
                                            Enter your name and click Generate to preview
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                <button id="add-sign-btn" type="button" class="btn btn-primary">Save</button>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Font Size Modal -->
        <div class="modal fade" id="fontSizeModal" tabindex="-1" aria-hidden="true" style="z-index: 1200; display:none;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adjust Font Size</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <canvas id="signaturePreviewModalCanvas" width="350" height="100"
                            style="border:1px solid #ccc;"></canvas>
                        <div class="mt-3">
                            <input type="range" id="fontSizeSlider" min="20" max="100" value="45" class="form-range">
                            <div class="mt-1">Font Size: <span id="fontSizeValue">45px</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmSaveBtn">Confirm &
                            Save</button>
                    </div>
                </div>
            </div>
        </div>
        @if (isset($grid_histories) && $grid_histories->IsNotEmpty())
            <div class="card">
                {{-- <div class="card"> --}}
                    <div class="card-header">
                        <button type="button" class="mb-0 btn btn-primary" onclick="toggleItemization()">Line
                            itemization</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <input type="hidden" name="itemization" id="itemization" @if(isset($grid_items) && !empty($grid_items)) value="1" @else value="0" @endif>
                            <table id="gridTable" class="table table-bordered" @if(isset($grid_items) && !empty($grid_items))
                            @else style="display: none" @endif>
                                <thead>
                                    <tr>
                                        @foreach ($grid_histories as $key => $item)
                                            <th>{{ ucwords($item->Title) }}</th>
                                        @endforeach
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if (!isset($check))
                                        @php
                                            $date = false;
                                        @endphp
                                        @foreach ($grid_histories as $key => $item)
                                            @if ($item->Status == 1)
                                                @php
                                                    $inputContent = '';
                                                    if ($item->Type == 'text') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" autocomplete="off" value="' . old('grid_items.' . $item->id . '.' . $key) . '"';
                                                    } elseif ($item->Type == 'number') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off"';
                                                    } elseif ($item->Type == 'date') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" id="test1" type="text" class="form-control mydatepicker" autocomplete="off"';
                                                        $date = true;
                                                    }
                                                @endphp
                                                <td>
                                                    <input {!! $inputContent !!}>
                                                </td>
                                            @endif
                                        @endforeach
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                    class="ti ti-plus"></i></button>
                                        </td>
                                    @else
                                        @php
                                            $date = false;
                                            $inputContent = '';
                                        @endphp

                                        @if (isset($grid_items) && !empty($grid_items))
                                            @foreach ($grid_items as $key => $item)
                                                @php
                                                    foreach ($item as $key => $val) {
                                                        $type = $val->grid_history->Type;

                                                        if ($type == 'date') {
                                                            $inputContent .=
                                                                '<td><input name="grid_items[' .
                                                                $val->grid_history->id .
                                                                '][]" type="text" class="form-control mydatepicker" value="' .
                                                                $val->Value .
                                                                '"></td>';
                                                        } elseif ($type == 'number') {
                                                            $inputContent .=
                                                                '<td><input name="grid_items[' .
                                                                $val->grid_history->id .
                                                                '][]" type="text" class="form-control" value="' .
                                                                $val->Value .
                                                                '" onkeypress="return /^[0-9.]+$/.test(event.key)"></td>';
                                                        } elseif ($type == 'text') {
                                                            $inputContent .=
                                                                '<td><input name="grid_items[' .
                                                                $val->grid_history->id .
                                                                '][]" type="text" class="form-control" value="' .
                                                                $val->Value .
                                                                '"></td>';
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    {!! $inputContent !!}
                                                    <td class="text-center">
                                                        @if ($loop->iteration == 1)
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                    class="ti ti-plus"></i></button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                    class="ti ti-plus"></i></button>
                                                            <button type="button" class="btn btn-sm btn-danger removeRow"><i
                                                                    class="ti ti-trash"></i></button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @php
                                                    $inputContent = '';
                                                @endphp
                                            @endforeach
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{--
                </div> --}}
            </div>
        @endif
    </form>
@endsection