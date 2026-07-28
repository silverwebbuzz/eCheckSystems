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
            background-color: buttonface;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }

        .nav-tabs .nav-link.active {
            background-color: buttonface;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
    </style>
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('profile_success'))
            <div class="alert alert-success">
                {{ session('profile_success') }}
            </div>
        @endif
        @if (session('pass_success'))
            <div class="alert alert-success">
                {{ session('pass_success') }}
            </div>
        @endif
        <h5 class="card-header">Clients</h5>
        <div class="card-datatable table-responsive pt-0">
            <ul class="nav nav-tabs gap-2" id="myTab" role="tablist">
                <li class="nav-item" role="presentation" style="margin-left:5px;">
                    <button class="nav-link active" id="active-user-tab" data-bs-toggle="tab"
                        data-bs-target="#activeUserTab" type="button" role="tab" aria-controls="home"
                        aria-selected="true">Active</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-user-tab" data-bs-toggle="tab" data-bs-target="#inactiveUserTab"
                        type="button" role="tab" aria-controls="profile" aria-selected="false">Inactive</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="activeUserTab" role="tabpanel" aria-labelledby="active-user-tab">
                    <table class="table" id="active-users-table">
                        <thead>
                            <tr>
                                <th style="width: 5% !important;">#</th>
                                <th style="width: 10% !important;">First Name</th>
                                <th style="width: 10% !important;">Last Name</th>
                                <th style="width: 10% !important;">Sign Up Date</th>
                                <th style="width: 10% !important;">Phone Number</th>
                                <th style="width: 10% !important;">Subscription Plan</th>
                                <th style="width: 8% !important;">Plan Price</th>
                                <th style="width: 7% !important;">Status</th>
                                <th style="width: 8% !important;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="inactiveUserTab" role="tabpanel" aria-labelledby="inactive-user-tab">
                    <table class="table" id="inactive-users-table">
                        <thead>
                            <tr>
                                <th style="width: 5% !important;">#</th>
                                <th style="width: 10% !important;">First Name</th>
                                <th style="width: 10% !important;">Last Name</th>
                                <th style="width: 10% !important;">Sign Up Date</th>
                                <th style="width: 10% !important;">Phone Number</th>
                                <th style="width: 10% !important;">Subscription Plan</th>
                                <th style="width: 8% !important;">Plan Price</th>
                                <th style="width: 7% !important;">Status</th>
                                <th style="width: 7% !important;">Reason</th>
                                <th style="width: 8% !important;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#active-users-table').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.users') }}",
                    data: function(d) {
                        d.status = 'Active';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    }, // Automatically generated index column
                    {
                        data: 'FirstName',
                        name: 'FirstName',
                    },
                    {
                        data: 'LastName',
                        name: 'LastName',
                    },
                    {
                        data: 'created_at',
                        name: 'CreatedAt',
                    },
                    {
                        data: 'PhoneNumber',
                        name: 'PhoneNumber',
                    },
                    {
                        data: 'package',
                        name: 'package',
                    },
                    {
                        data: 'package_price',
                        name: 'package_price',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [{
                    targets: [0, 7],
                    orderable: false
                }]
            });

            $('#inactive-users-table').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.users') }}",
                    data: function(d) {
                        d.status = 'Inactive';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    }, // Automatically generated index column
                    {
                        data: 'FirstName',
                        name: 'FirstName',
                    },
                    {
                        data: 'LastName',
                        name: 'LastName',
                    },
                    {
                        data: 'created_at',
                        name: 'CreatedAt',
                    },
                    {
                        data: 'PhoneNumber',
                        name: 'PhoneNumber',
                    },
                    {
                        data: 'package',
                        name: 'package',
                    },
                    {
                        data: 'package_price',
                        name: 'package_price',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'reason',
                        name: 'reason',
                        orderable: false,
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [{
                    targets: [0, 7],
                    orderable: false
                }]
            });
        });
    </script>
@endsection
