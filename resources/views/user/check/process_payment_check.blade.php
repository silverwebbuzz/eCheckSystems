@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Receive Payment')
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-danger">
                {{ session('info') }}
            </div>
        @endif
        <div id="alert-message">

        </div>
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Receive Payment
                @if(isset($how_it_works['Receive Payment']))
                        <a href="{{ $how_it_works['Receive Payment'] }}"
                            class="ms-2 text-primary fs-6" target="_blank">
                            <i class="ti ti-help-circle"></i> Click to see how it works?
                        </a>
                @endif
            </h5>
            <div>
                <a href="{{ route('check.process_payment.check') }}" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">
                    <i class="fa-solid fa-plus"></i> &nbsp; Create Check
                </a>
                <button id="bulk-generate-checks" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">
                    <i class="menu-icon tf-icons ti ti-files"></i>Batch Generate
                </button>
                <button id="bulk-download-checks" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">
                    <i class="menu-icon tf-icons ti ti-download"></i>Batch Download
                </button>
            </div>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="receive_payment_checks" class="table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all">
                        </th>
                        <th style="d-none">ID</th>
                        <th>#</th>
                        <th style="width: 50px;!important">Check Number</th>
                        <th>Payee</th>
                        <th>Payor</th>
                        <th>Amount</th>
                        <th>Service Fee</th>
                        <th>Total</th>
                        <th>Print Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('page-script')
    <script>
        $(document).ready(function () {
            receive_payment_table = $('#receive_payment_checks').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: "{{ route('check.process_payment') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                    data: 'CheckID', // Checkbox column uses CheckID for value
                    name: 'CheckID',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                    }

                },
                {
                    data: 'CheckID', // Hidden ID column for sorting
                    name: 'CheckID',
                    visible: false // Hides the ID column
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'CheckNumber',
                    name: 'CheckNumber'
                },
                {
                    data: 'CompanyID',
                    name: 'CompanyID'
                },
                {
                    data: 'EntityID',
                    name: 'EntityID'
                },
                {
                    data: 'Amount',
                    name: 'Amount',
                },
                 {
                    data: 'ServiceFee',
                    name: 'ServiceFee',
                },
                 {
                    data: 'Total',
                    name: 'Total',
                },
                {
                    data: 'IssueDate',
                    name: 'IssueDate'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                },
                ]
            });

            $('body').on('change', '#change_status', function () {
                var selectedValue = $(this).val();
                var id = $(this).data('id');

                $.ajax({
                    url: "{{ route('change_status') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {
                        value: selectedValue,
                        id: id,
                        page: 1
                    },
                    success: function (response) {
                        sessionStorage.setItem('success', response.message);

                        // Redirect to the appropriate URL
                        window.location.href = response.redirectUrl;
                    },
                    error: function () {
                        console.log('Error fetching data');
                    }
                });
            });

            $('#select-all').on('click', function () {
                let checked = this.checked;
                $('.row-checkbox').prop('checked', checked);
            });

            // Handle single checkbox change to update "Select All" status
            $(document).on('change', '.row-checkbox', function () {
                let total = $('.row-checkbox').length;
                let checked = $('.row-checkbox:checked').length;
                $('#select-all').prop('checked', total === checked);
            });

            // Optional: Function to get all selected CheckIDs
            window.getSelectedCheckIDs = function () {
                let selected = [];
                $('.row-checkbox:checked').each(function () {
                    selected.push($(this).val());
                });
                return selected;
            };

            // Check for success message
            if (sessionStorage.getItem('success')) {
                showAlert('success', sessionStorage.getItem('success'));
                sessionStorage.removeItem('success');
            }

            // Check for error message
            if (sessionStorage.getItem('error')) {
                showAlert('danger', sessionStorage.getItem('error'));
                sessionStorage.removeItem('error');
            }

            $(document).on('click', '.view_pdf', function () {

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: "GET",
                    success: function (response) {
                        if (response.status == true) {
                            receive_payment_table.ajax.reload(null,false);
                            window.open(response.url, '_blank');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });
        });

        $('#bulk-generate-checks').on('click', function () {
            let selected = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                showAlert('danger', 'Please select at least one check.');
                return;
            }

            // Send AJAX POST to Laravel route
            $.ajax({
                url: "{{ route('bulk_generate') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    check_ids: selected
                },
                success: function (response) {
                    if (response.status == true) {

                        showAlert('success', 'Checks generated successfully!');
                    } else {
                        showAlert('danger', 'Something went wrong. Please try again.');
                    }
                    receive_payment_table.ajax.reload() // Reload table after processing
                    $('#select-all').prop('checked', false); // Reset "select all"
                },
                error: function (xhr) {
                    showAlert('danger', 'Something went wrong. Please try again.');
                }
            });
        });

        $('#bulk-download-checks').on('click', function () {
            let selected = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selected.length === 0) {
                showAlert('danger', 'Please select at least one check.');
                return;
            }

            // Create a hidden form
            let form = $('<form>', {
                method: 'POST',
                action: "{{ route('bulk_download') }}"
            });

            // Add CSRF token
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: "{{ csrf_token() }}"
            }));

            // Add selected check IDs
            selected.forEach(function (id) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'check_ids[]',
                    value: id
                }));
            });

            $('body').append(form);
            form.submit();
        });



        function showAlert(type, message) {
            let alertDiv = $(`
                <div class="alert alert-${type}" style="margin: 10px;">
                    ${message}
                </div>
            `);
            $('#alert-message').prepend(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.fadeOut(500, function () {
                    $(this).remove();
                });
            }, 1000);
        }
    </script>
@endsection