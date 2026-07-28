@extends('layouts/layoutMaster')

@section('title', 'Plan Expired')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    <style>
        #card-element {
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
            transition: border 0.3s;
        }

        #card-element.StripeElement--focus {
            border-color: #6366f1;
            background-color: #fff;
        }

        #card-element.StripeElement--invalid {
            border-color: #dc3545;
        }

        #card-errors {
            margin-top: 8px;
            font-size: 0.875rem;
            color: #dc3545;
        }

        .btn-custom {
            background-color: #6366f1;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #4f46e5;
        }
    </style>
@endsection
<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/pages-pricing.js', 'resources/assets/js/pages-account-settings-billing.js', 'resources/assets/js/app-invoice-list.js', 'resources/assets/js/modal-edit-cc.js', 'resources/assets/js/ui-modals.js'])

    <script>
        $(document).ready(function() {
            $('#invoice_data').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_invoice') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'PaymentHistoryID', // Hidden ID column for sorting
                        name: 'PaymentHistoryID',
                        visible: false // Hides the ID column
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'PaymentStatus',
                        name: 'PaymentStatus',
                    },
                    {
                        data: 'PaymentAmount',
                        name: 'PaymentAmount',
                    },
                    {
                        data: 'PaymentDate',
                        name: 'PaymentDate'
                    },
                ]
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="col-md-12">
            <div class="card mb-6">
                @if (session('success_card'))
                    <div class="alert alert-success">
                        {{ session('success_card') }}
                    </div>
                @endif
                @if (session('error_card'))
                    <div class="alert alert-danger">
                        {{ session('error_card') }}
                    </div>
                @endif
                <h5 class="card-header">Your Subscription Expired</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                    <p>Your subscription has expired. Please renew your plan to continue enjoying our services!</p>
                    <a class="btn btn-primary mt-2"
                        href="{{ route('user.package', ['user_id' => Auth()->user()->UserID]) }}">Subscribe Plan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-6">
                <!-- Current Plan -->
                <h5 class="card-header">Current Plan</h5>
                <div class="card-body">
                    <div class="row row-gap-6">
                        <div class="col-md-6 mb-1">
                            <div class="mb-6">
                                <h6 class="mb-1">Your Current Plan is
                                    {{ $package_id == '-1' ? 'Trial' : $package->Name }} <span class="text-muted"> - {{ $package_id == '-1' ? 'Free' : '$'.$package->Price }}</span></h6>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Current Plan -->
            </div>
      @if ($package_id != '-1')
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Payments</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table" id="invoice_data">
                        <thead>
                            <tr>
                                <th class="d-none">ID</th>
                                <th>#</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
