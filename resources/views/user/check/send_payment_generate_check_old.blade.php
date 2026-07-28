@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

@php
    $base_url = url('/');
@endphp

<!-- Vendor Styles -->
@section('vendor-style')
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

        /* Select2 dropdown styling to match other form inputs */
        .select2-container--default .select2-selection--single {
            border: 1px solid !important;
            height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
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

            img.onload = function() {
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


        $('#clear').click(function(e) {

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

        $(document).ready(function() {

            // Function to initialize Select2 for a dropdown
            function initSelect2(selector, placeholder) {
                var $select = $(selector);
                if ($select.length && $select.find('option').length > 0) {
                    // Destroy existing Select2 instance if any
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }
                    
                    // Get the current selected value
                    // Check all options to find the one with selected attribute (excluding placeholder)
                    var currentVal = null;
                    $select.find('option').each(function() {
                        if ($(this).attr('selected') && $(this).val() !== '') {
                            currentVal = $(this).val();
                            return false; // Break the loop
                        }
                    });
                    
                    // If no selected option found (other than placeholder), use the select's value
                    if (!currentVal || currentVal === '') {
                        currentVal = $select.val();
                    }
                    
                    // Initialize Select2
                    $select.select2({
                        placeholder: placeholder,
                        allowClear: false,
                        width: '100%',
                        minimumResultsForSearch: 0
                    });
                    
                    // Ensure Select2 displays the selected value if present (important for edit mode)
                    if (currentVal && currentVal !== '' && currentVal !== 'add_new_payor' && currentVal !== 'add_new_payee') {
                        // Set the value and trigger change to update Select2 display
                        $select.val(currentVal).trigger('change.select2');
                    } else {
                        // If no valid value, set to empty to show placeholder
                        $select.val('').trigger('change.select2');
                    }
                }
            }

            // Initialize Select2 for both dropdowns
            // Use a small delay to ensure all DOM manipulations are complete (especially for edit mode)
            setTimeout(function() {
                initSelect2('#payor', 'Select Pay From');
                initSelect2('#payee', 'Select Pay To');
            }, 100);

            $(document).find('.mydatepicker').flatpickr({
                dateFormat: 'm-d-Y',
                monthSelectorType: 'static'
            });
            $('#payee').on('change', function() {
                id = $(this).val();
                if (id == 'add_new_payee') {
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
                        success: function(response) {
                            $('#payee-edit').removeClass('d-none');

                            $('#payee_id').val(response.payee.EntityID);
                            $('#payee-name').val(response.payee.Name);
                            $('#payee-email').val(response.payee.Name);
                        }
                    });
                }
            });


            $('#add-payee-btn').on('click', function(event) {
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
                    success: function(response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function(key, value) {
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
                                // Format name with email if available
                                let displayName = response.payee.Name;
                                if (response.payee.Email && response.payee.Email.trim() !== '') {
                                    displayName = response.payee.Name + ' (' + response.payee.Email + ')';
                                }
                                $('#payee option:selected').text(displayName);
                                $('#payee').trigger('change.select2');
                            } else {
                                // Format name with email if available
                                let displayName = response.payee.Name;
                                if (response.payee.Email && response.payee.Email.trim() !== '') {
                                    displayName = response.payee.Name + ' (' + response.payee.Email + ')';
                                }
                                let newOption =
                                    `<option value="${response.payee.EntityID}" selected>${displayName}</option>`;
                                // Insert before the last "Add New Payee" option
                                const $addNewOptions = $('#payee option[value="add_new_payee"]');
                                if ($addNewOptions.length > 0) {
                                    // Insert before the last "Add New Payee" option
                                    $addNewOptions.last().before(newOption);
                                } else {
                                    $('#payee').append(newOption);
                                }
                                // Refresh Select2 and set value
                                $('#payee').val(response.payee.EntityID).trigger('change.select2');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            $('#payor').on('change', function() {
                id = $(this).val();

                    $('#address').val('');
                    $('#city').val('');
                    $('#state').val('');
                    $('#zip').val('');

                if (id === 'add_new_payor') {
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
                    $('#payor_h').text('Add');
                } else {
                    $.ajax({
                        url: "{{ route('get_payor', ':id') }}".replace(':id', id) + '?type=SP',
                        method: 'GET',
                        success: function(response) {
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

            $('#add-payor-btn').on('click', function(event) {
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
                    success: function(response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function(key, value) {
                                $('#add-payor #' + key).closest('.col-md-6').append(
                                    '<span class="text-danger">' + value[0] +
                                    '</span>'
                                );
                            });
                        } else if (response.success) {

                            $('#payorModel').modal('hide');
                            // Success message

                            if (id) {
                                // Format name with email if available
                                let displayName = response.payor.Name;
                                if (response.payor.Email && response.payor.Email.trim() !== '') {
                                    displayName = response.payor.Name + ' (' + response.payor.Email + ')';
                                }
                                $('#payor option:selected').text(displayName);
                                $('#payor').trigger('change.select2');
                            } else {
                                // Format name with email if available
                                let displayName = response.payor.Name;
                                if (response.payor.Email && response.payor.Email.trim() !== '') {
                                    displayName = response.payor.Name + ' (' + response.payor.Email + ')';
                                }
                                let newOption =
                                    `<option value="${response.payor.EntityID}" selected>${displayName}</option>`;
                                // Insert before the last "Add New Payors" option
                                const $addNewOptions = $('#payor option[value="add_new_payor"]');
                                if ($addNewOptions.length > 0) {
                                    // Insert before the last "Add New Payors" option
                                    $addNewOptions.last().before(newOption);
                                } else {
                                    $('#payor').append(newOption);
                                }
                                $('#payor').val(response.payor.EntityID).trigger('change.select2');
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
                    error: function(xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            //Print value on check
            $("#check_date").on("change", function() {
                const selectedDate = $(this).val();
                $("#c_check_date").text(selectedDate || "XX-XX-XXXX");
            });

            $("#check_number").on("input", function() {
                const check_number = $(this).val();
                $("#verify_check_number").val(check_number);
            });

            $("#amount").on("input", function() {
                const amount = $(this).val();

                $.ajax({
                    url: "{{ route('amount_word') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        amount: amount,
                    },
                    success: function(response) {
                        $("#c_amount").text(amount || "XXXX.XX");
                        $("#c_amount_word").text(response.word || "XXXXX XXXX XXXX");
                    }
                });
            });

            $("#memo").on("input", function() {
                const memo = $(this).val();
                $("#c_memo").text(memo || "XXXXXXX XXXX XXXX XX");
            });

            $('#payor_close').on('click', function(e) {
                event.preventDefault();
                $('#payorModel').modal('hide');
                if ($('#payor').val() === 'add_new_payor') {
                    $("#payor").val("").trigger('change.select2');
                }
            });

            // Reset dropdown if modal is closed without submitting
            $('#payorModel').on('hidden.bs.modal', function() {
                if ($('#payor').val() === 'add_new_payor') {
                    $('#payor').val('').trigger('change.select2');
                }
            });

            $('#payeeModel').on('hidden.bs.modal', function() {
                if ($('#payee').val() === 'add_new_payee') {
                    $('#payee').val('').trigger('change.select2');
                }
            });
            $('#payor-edit').on('click', function(e) {
                event.preventDefault();
                $('#payorModel').modal('show');
                $('#payor_h').text('Edit');
            });
            $('#signature-edit').on('click', function(e) {
                event.preventDefault();
                $('#signModel').modal('show');
                $('.sign_h').text('Edit');
            });

            $('#payee-edit').on('click', function(e) {
                event.preventDefault();
                $('#payeeModel').modal('show');
                $('#payee_h').text('Edit');
            });

            $('#is_sign').change(function(e) {
                e.preventDefault();
                if ($(this).is(':checked')) {
                    $('.sing-box').removeClass('d-none'); // Show the signature field
                } else {
                    $('.sing-box').addClass('d-none'); // Hide the signature field
                }
            });

            // Clear invalid values before form submission
            $('form').on('submit', function(e) {
                if ($('#payor').val() === 'add_new_payor') {
                    $('#payor').val('').trigger('change.select2');
                }
                if ($('#payee').val() === 'add_new_payee') {
                    $('#payee').val('').trigger('change.select2');
                }
            });

            $('#signature').on('change', function() {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
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
                        success: function(response) {
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

                                img.onload = function() {
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

            $('#add-sign-btn').on('click', function(event) {
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
                    success: function(response) {
                        if (response.errors) {
                            $.each(response.errors, function(key, value) {
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
                    error: function(xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            $('#check_number').on('input', function() {
                const check_number = $(this).val();

                $.ajax({
                    url: "{{ route('check.check_number_exists') }}",
                    method: 'GET',
                    data: {
                        check_number: check_number
                    },
                    success: function(response) {
                        if (response.exists) {
                            $('#check_number_error').text('Check number already exists.');
                        } else {
                            $('#check_number_error').text('');
                        }
                    }
                })
            });

            $(document).on('click', '.removeRow', function() {
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
                success: function(response) {

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

        // function addDefaultRow(gridHistoryIDs) {

        //     $.ajax({
        //         url: "{{ url('/get-default-grids') }}",
        //         method: 'GET',
        //         data : {
        //             _token: "{{ csrf_token() }}",
        //             grid_row_count: grid_row_count,
        //             grid_history_ids : gridHistoryIDs
        //         },
        //         success: function(response) {

        //             if (response.status == true) {
        //                 $('#gridTable tbody').append(response.html);
        //                 $(document).find('.mydatepicker').flatpickr({
        //                     dateFormat: 'm-d-Y',
        //                     monthSelectorType: 'static'
        //                 });

        //                 grid_row_count++;
        //             }
        //         }
        //     })
        // }
    </script>

@endsection

@section('content')
    @if (session('grid_error'))
        <div class="alert alert-danger">
            {{ session('grid_error') }}
        </div>

        <script>
            $(document).ready(function() {
                $('#gridTable').show();
            });
        </script>
    @endif
    @if (old('itemization') == 1)
        <script>
            $(document).ready(function() {
                $('#gridTable').show();
            });
        </script>
    @endif
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
                <input type="hidden" id="id" name="id"
                    value="{{ !empty($check->CheckID) ? $check->CheckID : '' }}">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            {{-- <label class="col-sm-12 col-form-label" for="account-name">Account Holder's Name:</label>
                            --}}
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payor" name="payor" class="form-control">
                                        <option value="" selected>Select Pay From</option>
                                        @if(count($payors) > 0)
                                            <option value="add_new_payor" id="add_other_payor" style="font-weight: bold;">Add New Payors</option>
                                            @foreach ($payors as $payor)
                                                @php
                                                    if (!empty($payor->Email)) {
                                                        $name = $payor->Name . ' (' . $payor->Email . ')';
                                                    } else {
                                                        $name = $payor->Name;
                                                    }
                                                @endphp
                                                <option value="{{ $payor->EntityID }}"
                                                    {{ old('payor', $check->PayorID ?? '') == $payor->EntityID ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payor" id="add_other_payor_bottom" style="font-weight: bold;">Add New Payors</option>
                                        @else
                                            <option value="add_new_payor" id="add_other_payor" style="font-weight: bold;">Add New Payors</option>
                                        @endif
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
                                    <input type="text" id="check_number" name="check_number"
                                        class="form-control no-spinner" placeholder="Check Number" maxlength="10"
                                        oninput="" value="{{ $checkNumber }}" autocomplete="off">
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
                                        <input type="text" id="city" name="city" class="form-control"
                                            placeholder="City" readonly
                                            value="{{ !empty($old_payor->City) && $old_payor->City ? $old_payor->City : old('city') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="state" name="state" class="form-control"
                                            placeholder="State" readonly
                                            value="{{ !empty($old_payor->State) && $old_payor->State ? $old_payor->State : old('state') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="zip" name="zip" class="form-control"
                                            placeholder="Zip" readonly
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
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center gap-1">
                                    <select id="payee" name="payee" class="form-control" style="font-size: 16px;">
                                        <option value="" selected>Select Pay To</option>
                                        @if(count($payees) > 0)
                                            <option value="add_new_payee" id="add_other_company" style="font-weight: bold;">Add New Payee</option>
                                            @foreach ($payees as $payee)
                                                @php
                                                    if (!empty($payee->Email)) {
                                                        $name = $payee->Name . ' (' . $payee->Email . ')';
                                                    } else {
                                                        $name = $payee->Name;
                                                    }
                                                @endphp
                                                <option value="{{ $payee->EntityID }}"
                                                    {{ old('payee', $check->PayeeID ?? '') == $payee->EntityID ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                            <option value="add_new_payee" id="add_other_company_bottom" style="font-weight: bold;">Add New Payee</option>
                                        @else
                                            <option value="add_new_payee" id="add_other_company" style="font-weight: bold;">Add New Payee</option>
                                        @endif
                                    </select>
                                    <span id="payee-edit" class="{{ !empty($check->PayeeID) ? '' : 'd-none' }}"><i
                                            class="ti ti-pencil me-1"></i></span>
                                </div>
                                @if ($errors->has('payee'))
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
                                    onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control"
                                    autocomplete="off"
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
                                <input type="text" id="memo" name="memo" placeholder="Memo"
                                    class="form-control"
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
                                <select id="signature" name="signature_id" class="form-control"
                                    style="font-size: 16px;">
                                    <option value="" selected>Select Signature</option>
                                    @foreach ($userSignatures as $userSignature)
                                        <option value="{{ $userSignature->Id }}"
                                            {{ old('signature_id', $old_sign?->Id ?? '') == $userSignature->Id ? 'selected' : '' }}>
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
                        <input type="text" id="verify_check_number" name="verify_check_number"
                            placeholder="Check Number" class="form-control" readonly value="{{ $checkNumber }}">
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
                            <input type="hidden" name="payor_id" id="payor_id"
                                value={{ !empty($old_payor->EntityID) ? $old_payor->EntityID : '' }} />
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
                                        <textarea id="address1" name="address1" class="form-control">{{ !empty($old_payor->Address1) ? $old_payor->Address1 : old('address1') }}</textarea>
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
                                                <option value="{{ $state }}"
                                                    {{ !empty($old_payor->state) && $old_payor->state ? 'selected' : '' }}>
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
                                        <input type="text" name="routing_number" id="routing_number"
                                            class="form-control"
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
                                <button type="button" class="btn btn-label-secondary"
                                    data-bs-dismiss="modal">Close</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                                <button type="button" class="btn btn-label-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button id="add-payee-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="signModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span class="sign_h">Add </span>Signature
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="sign_id" id="sign_id"
                                value="{{ !empty($old_sign->Id) ? $old_sign->Id : '' }}">
                            <div class="modal-body">
                                <div class="row g-6">
                                    <div class="col-md-12">
                                        <label class="form-label" for="sign-name">Name</label>
                                        <input type="text" name="name" id="sign-name" class="form-control"
                                            value="{{ !empty($old_sign->Name) ? $old_sign->Name : old('name') }}" />
                                        <span id="error-name" class="text-danger"></span>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="signature">Signature</label>
                                        <div class="col-sm-10">
                                            <div id="sig"></div>
                                            <br />
                                            <button id="clear" class="btn btn-sm btn-danger">Clear</button>
                                            <input type="hidden" name="signature" id="signature64">
                                            <br>
                                            <span id="error-signature" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button id="add-sign-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
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
                        <input type="hidden" name="itemization" id="itemization"
                            @if ((isset($grid_items) && $grid_items->IsNotEmpty()) || session('grid_error') || old('itemization')) value="1" @else value="0" @endif>
                        <table id="gridTable" class="table table-bordered"
                            @if ((isset($grid_items) && $grid_items->IsNotEmpty()) || session('grid_error') || old('itemization')) @else style="display: none" @endif>
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

                                    @if (old('itemization'))
                                        @php
                                            $old_items = old('grid_items');
                                            $old_items = reset($old_items);

                                        @endphp

                                        @foreach ($old_items as $key => $item)
                                            @php
                                                $loop_index = $loop->iteration;
                                            @endphp
                                            <tr>
                                                @foreach ($grid_histories as $row_key => $val)
                                                    @if ($val->Status == 1)
                                                        @php

                                                            if ($val->Type == 'text') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" type="text" class="form-control" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                            } elseif ($val->Type == 'number') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                            } elseif ($val->Type == 'date') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" id="test1" type="text" class="form-control mydatepicker" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                                $date = true;
                                                            }

                                                        @endphp
                                                        <td>
                                                            <input {!! $inputContent !!}>
                                                        </td>
                                                    @endif
                                                @endforeach
                                                @if ($loop_index == 1)
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                class="ti ti-plus"></i></button>
                                                    </td>
                                                @else
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                class="ti ti-plus"></i></button>
                                                        <button type="button" class="btn btn-sm btn-danger removeRow"><i
                                                                class="ti ti-trash"></i></button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach ($grid_histories as $key => $item)
                                            @if ($item->Status == 1)
                                                @php
                                                    $inputContent = '';
                                                    if ($item->Type == 'text') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
                                                    } elseif ($item->Type == 'number') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
                                                    } elseif ($item->Type == 'date') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" id="test1" type="text" class="form-control mydatepicker" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
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
                                    @endif
                                @else
                                    @php
                                        $date = false;
                                        $inputContent = '';
                                    @endphp
                                    @if (old('itemization') && old('grid_items'))
                                        @php
                                            $old_items = old('grid_items');
                                            $old_items = reset($old_items);
                                        @endphp

                                        @foreach ($old_items as $key => $item)
                                            @php
                                                $loop_index = $loop->iteration;
                                            @endphp
                                            <tr>
                                                @foreach ($grid_histories as $row_key => $val)
                                                    @if ($val->Status == 1)
                                                        @php

                                                            if ($val->Type == 'text') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" type="text" class="form-control" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                            } elseif ($val->Type == 'number') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                            } elseif ($val->Type == 'date') {
                                                                $inputContent =
                                                                    'name="grid_items[' .
                                                                    $val->id .
                                                                    '][]" id="test1" type="text" class="form-control mydatepicker" autocomplete="off" value="' .
                                                                    old('grid_items.' . $val->id . '.' . $key) .
                                                                    '"';
                                                                $date = true;
                                                            }

                                                        @endphp
                                                        <td>
                                                            <input {!! $inputContent !!}>
                                                        </td>
                                                    @endif
                                                @endforeach
                                                @if ($loop_index == 1)
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                class="ti ti-plus"></i></button>
                                                    </td>
                                                @else
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            onclick="addRow('{{ implode(',', $grid_history_ids) }}')"><i
                                                                class="ti ti-plus"></i></button>
                                                        <button type="button" class="btn btn-sm btn-danger removeRow"><i
                                                                class="ti ti-trash"></i></button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @elseif (isset($grid_items) && $grid_items->IsNotEmpty())
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
                                    @else
                                          @foreach ($grid_histories as $key => $item)
                                            @if ($item->Status == 1)
                                                @php
                                                    $inputContent = '';
                                                    if ($item->Type == 'text') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
                                                    } elseif ($item->Type == 'number') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
                                                    } elseif ($item->Type == 'date') {
                                                        $inputContent =
                                                            'name="grid_items[' .
                                                            $item->id .
                                                            '][]" id="test1" type="text" class="form-control mydatepicker" autocomplete="off" value="' .
                                                            old('grid_items.' . $item->id . '.' . $key) .
                                                            '"';
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
