@extends('layouts/layoutMaster')

@section('title', 'Clients')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')
    <style>
        .nav-tabs .nav-link {
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
            font-weight: 600;
            padding: 10px 20px;
            color: #555;
            border: 2px solid transparent;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid currentColor;
        }
        .tab-green .nav-link { background-color: #e6f4ea; color: #28a745; }
        .tab-green .nav-link.active { background-color: #d4edda; color: #1e7e34; border-color: #28a745; }
        .tab-grey .nav-link { background-color: #f0f0f0; color: #6c757d; }
        .tab-grey .nav-link.active { background-color: #e2e3e5; color: #495057; border-color: #6c757d; }
        .tab-orange .nav-link { background-color: #fff3e0; color: #fd7e14; }
        .tab-orange .nav-link.active { background-color: #ffe0b2; color: #e65100; border-color: #fd7e14; }
        .tab-red .nav-link { background-color: #fde8e8; color: #dc3545; }
        .tab-red .nav-link.active { background-color: #f8d7da; color: #bd2130; border-color: #dc3545; }
    </style>
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <h5 class="card-header">Clients</h5>
        <div class="card-datatable table-responsive pt-0">
            <ul class="nav nav-tabs gap-2 px-2" id="clientTabs" role="tablist">
                <li class="nav-item tab-green" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab"
                        data-bs-target="#activeTab" type="button" role="tab">
                        <!-- <i class="ti ti-circle-check me-1"></i>  -->
                        Active
                    </button>
                </li>
                <li class="nav-item tab-grey" role="presentation">
                    <button class="nav-link" id="inactive-tab" data-bs-toggle="tab"
                        data-bs-target="#inactiveTab" type="button" role="tab">
                        <!-- <i class="ti ti-circle-minus me-1"></i>  -->
                        Inactive
                    </button>
                </li>
                <li class="nav-item tab-orange" role="presentation">
                    <button class="nav-link" id="trial-tab" data-bs-toggle="tab"
                        data-bs-target="#trialTab" type="button" role="tab">
                        <!-- <i class="ti ti-clock me-1"></i> -->
                         Trial
                    </button>
                </li>
                <li class="nav-item tab-red" role="presentation">
                    <button class="nav-link" id="fraud-tab" data-bs-toggle="tab"
                        data-bs-target="#fraudTab" type="button" role="tab">
                        <!-- <i class="ti ti-alert-triangle me-1"></i> -->
                         Fraud
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="clientTabContent">
                {{-- GREEN - Active --}}
                <div class="tab-pane fade show active" id="activeTab" role="tabpanel">
                    <table class="table" id="active-users-table">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:10%">First Name</th>
                                <th style="width:10%">Last Name</th>
                                <th style="width:10%">Sign Up Date</th>
                                <th style="width:10%">Phone Number</th>
                                <th style="width:10%">Subscription Plan</th>
                                <th style="width:8%">Plan Price</th>
                                <th style="width:7%">Status</th>
                                <th style="width:8%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                {{-- GREY - Inactive --}}
                <div class="tab-pane fade" id="inactiveTab" role="tabpanel">
                    <table class="table" id="inactive-users-table">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:10%">First Name</th>
                                <th style="width:10%">Last Name</th>
                                <th style="width:10%">Sign Up Date</th>
                                <th style="width:10%">Phone Number</th>
                                <th style="width:10%">Subscription Plan</th>
                                <th style="width:8%">Plan Price</th>
                                <th style="width:7%">Status</th>
                                <th style="width:7%">Reason</th>
                                <th style="width:8%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                {{-- ORANGE - Trial / Pending Approval --}}
                <div class="tab-pane fade" id="trialTab" role="tabpanel">
                    <table class="table" id="trial-users-table">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:10%">First Name</th>
                                <th style="width:10%">Last Name</th>
                                <th style="width:10%">Sign Up Date</th>
                                <th style="width:10%">Phone Number</th>
                                <th style="width:10%">Subscription Plan</th>
                                <th style="width:8%">Plan Price</th>
                                <th style="width:7%">Status</th>
                                <th style="width:12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                {{-- RED - Fraud --}}
                <div class="tab-pane fade" id="fraudTab" role="tabpanel">
                    <table class="table" id="fraud-users-table">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:10%">First Name</th>
                                <th style="width:10%">Last Name</th>
                                <th style="width:10%">Sign Up Date</th>
                                <th style="width:10%">Phone Number</th>
                                <th style="width:10%">Subscription Plan</th>
                                <th style="width:8%">Plan Price</th>
                                <th style="width:7%">Status</th>
                                <th style="width:7%">Reason</th>
                                <th style="width:8%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        function buildColumns(includeReason) {
            var cols = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, orderable: false },
                { data: 'FirstName', name: 'FirstName' },
                { data: 'LastName', name: 'LastName' },
                { data: 'created_at', name: 'CreatedAt' },
                { data: 'PhoneNumber', name: 'PhoneNumber' },
                { data: 'package', name: 'package' },
                { data: 'package_price', name: 'package_price' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
            ];
            if (includeReason) {
                cols.push({ data: 'reason', name: 'reason', orderable: false });
            }
            cols.push({ data: 'actions', name: 'actions', orderable: false, searchable: false });
            return cols;
        }

        function initTable(tableId, statusValue, includeReason) {
            return $('#' + tableId).DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.users') }}",
                    data: function(d) { d.status = statusValue; }
                },
                columns: buildColumns(includeReason),
                columnDefs: [{ targets: [0], orderable: false }]
            });
        }

        $(document).ready(function() {
            var activeTable = initTable('active-users-table', 'Active', false);
            var inactiveTable = null;
            var trialTable = null;
            var fraudTable = null;

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).data('bs-target');
                if (target === '#inactiveTab' && !inactiveTable) {
                    inactiveTable = initTable('inactive-users-table', 'Inactive', true);
                }
                if (target === '#trialTab' && !trialTable) {
                    trialTable = initTable('trial-users-table', 'Trial', false);
                }
                if (target === '#fraudTab' && !fraudTable) {
                    fraudTable = initTable('fraud-users-table', 'Fraud', true);
                }
                // Adjust columns on tab show
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            // Approve button
            $(document).on('click', '.approve-btn', function() {
                var userId = $(this).data('id');
                if (!confirm('Are you sure you want to APPROVE this client?')) return;
                $.ajax({
                    url: "{{ route('admin.approveClient') }}",
                    type: 'POST',
                    data: { id: userId, _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        alert(res.message);
                        reloadAllTables();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'An error occurred.');
                    }
                });
            });

            // Reject button
            $(document).on('click', '.reject-btn', function() {
                var userId = $(this).data('id');
                if (!confirm('Are you sure you want to REJECT this client as FRAUD?')) return;
                $.ajax({
                    url: "{{ route('admin.rejectClient') }}",
                    type: 'POST',
                    data: { id: userId, _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        alert(res.message);
                        reloadAllTables();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'An error occurred.');
                    }
                });
            });

            function reloadAllTables() {
                if (activeTable) activeTable.ajax.reload(null, false);
                if (inactiveTable) inactiveTable.ajax.reload(null, false);
                if (trialTable) trialTable.ajax.reload(null, false);
                if (fraudTable) fraudTable.ajax.reload(null, false);
            }
        });
    </script>
@endsection
