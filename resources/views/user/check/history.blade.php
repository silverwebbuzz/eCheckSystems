@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Check History')
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
                <div class="row gy-4 gy-sm-1">
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-center card-widget-1 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">{{ $total_receive_check }}</h4>
                                <p class="mb-0">Check Payments Received</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-file-invoice ti-26px"></i>
                                </span>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none me-6">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div
                            class="d-flex justify-content-between align-items-center border-end pb-4 pb-sm-0 card-widget-3">
                            <div>
                                <h4 class="mb-0">{{ $total_receive_check_amount }}</h4>
                                <p class="mb-0">Total Amount Received</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-checks ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div
                            class="d-flex justify-content-between align-items-center card-widget-2 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">{{ $total_send_check }}</h4>
                                <p class="mb-0">Check Payments Sent</p>
                            </div>
                            <div class="avatar me-lg-6">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-file-invoice ti-26px"></i>
                                </span>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">{{ $total_send_check_amount }}</h4>
                                <p class="mb-0">Total Amount Sent</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-checks ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-12 col-lg-12 order-0 order-md-1">
        <!-- User Pills -->

    </div>
    <div class="d-flex justify-content-end mt-2">
        <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2" style="margin-right:10px;gap: 10px;background: #e5dfdf;">
            <li class="nav-item">
                <button class="nav-link active" id="send-payment" style="border-radius: 0;">Payments Sent</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="receive-payment" style="border-radius: 0;">Payments Received</button>
            </li>
        </ul>
    </div>
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
        <div class="card-datatable table-responsive pt-0">
            <table id="check_history" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="width: 50px;!important">Check Number</th>
                        <th>Payee</th>
                        <th>Payor</th>
                        <th>Amount</th>
                        <th>Service Fee</th>
                        <th>Total</th>
                        <th>Print Date</th>
                        <th>Status</th>
                        <th>Check Preview</th>
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
        var filterType = 'Make Payment'; // Default filter type

        $(document).ready(function() {
            var table = $('#check_history').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('check_history') }}", // Make sure the route is correct
                    data: function(d) {
                        d.type = filterType; // Send the filter type to the server
                         d.entity_id = '{{ $_GET['entity_id'] ?? '' }}';
                    }
                },
                columns: [{
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
                        name: 'Amount'
                    },
                    {
                        data: 'ServiceFees',
                        name: 'ServiceFees'
                    },
                     {
                        data: 'Total',
                        name: 'Total'
                    },
                    {
                        data: 'IssueDate',
                        name: 'IssueDate'
                    },
                    {
                        data: 'Status',
                        name: 'Status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Click event for 'Send Payment' button
            $('#send-payment').on('click', function() {
                filterType = 'Make Payment'; // Set filter type to 'Make Payment'
                $('#send-payment').addClass('active'); // Highlight the active button
                $('#receive-payment').removeClass('active'); // Remove highlight from the other button
                table.ajax.reload(); // Reload the table data with the new filter
            });

            // Click event for 'Receive Payment' button
            $('#receive-payment').on('click', function() {
                filterType = 'Process Payment'; // Set filter type to 'Process Payment'
                $('#send-payment').removeClass('active'); // Remove highlight from 'Send Payment'
                $('#receive-payment').addClass('active'); // Highlight 'Receive Payment'
                table.ajax.reload(); // Reload the table data with the new filter
            });
        });
    </script>
@endsection
