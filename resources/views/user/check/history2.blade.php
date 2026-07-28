@extends('layouts/layoutMaster')

@section('title', 'Check History')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    {{-- @vite('resources/assets/js/app-invoice-list.js') --}}
@endsection

@section('content')
    <!-- Invoice List Widget -->

    <div class="card mb-6">
        <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
                <div class="row gy-4 gy-sm-1">
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-center card-widget-1 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">24</h4>
                                <p class="mb-0">Clients</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-user ti-26px"></i>
                                </span>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none me-6">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div
                            class="d-flex justify-content-between align-items-center card-widget-2 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">165</h4>
                                <p class="mb-0">Invoices</p>
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
                        <div
                            class="d-flex justify-content-between align-items-center border-end pb-4 pb-sm-0 card-widget-3">
                            <div>
                                <h4 class="mb-0">$2.46k</h4>
                                <p class="mb-0">Paid</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-checks ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">$876</h4>
                                <p class="mb-0">Unpaid</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="ti ti-circle-off ti-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table id="check_history" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="width: 50px;!important">Check Number</th>
                        <th>Payee</th>
                        <th>Payor</th>
                        <th>Amount</th>
                        <th>Issue Date</th>
                        <th style="width: 90px;!important">Expiry Date</th>
                        <th style="width: 140px !important;">Status</th>
                        <th style="width: 50px;!important">Check Preview</th>
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
        $(document).ready(function() {
            $('#check_history').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('check_history') }}",
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
                        name: 'Amount',
                    },
                    {
                        data: 'IssueDate',
                        name: 'IssueDate'
                    },
                    {
                        data: 'ExpiryDate',
                        name: 'ExpiryDate'
                    },
                    {
                        data: 'Status',
                        name: 'Status',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endsection
