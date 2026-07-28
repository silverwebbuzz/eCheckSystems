@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Payee')
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
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Manage Payees (Pay To)
                @if(isset($how_it_works['Manage Payees']))
                        <a href="{{ $how_it_works['Manage Payees'] }}"
                            class="ms-2 text-primary fs-6" target="_blank">
                            <i class="ti ti-help-circle"></i> Click to see how it works?
                        </a>
                @endif
            </h5>
            {{-- <a href="{{ route('user.payee.add') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;">
                <i class="fa-solid fa-plus"></i> &nbsp; Add Pay TO
            </a> --}}
            <!-- <div class="d-flex justify-content-end mt-2">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                                                    <label class="form-check-label" for="flexSwitchCheckChecked">Checked switch checkbox input</label>
                                                </div>
                                            </div> -->
        </div>
        <div class="card-datatable table-responsive pt-0">
            <ul class="nav nav-tabs gap-2" id="myTab" role="tablist">
                <li class="nav-item" role="presentation" style="margin-left:5px;">
                    <button class="nav-link active" id="active-client-tab" data-bs-toggle="tab"
                        data-bs-target="#activeClientTab" type="button" role="tab" aria-controls="home"
                        aria-selected="true">Active</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-client-tab" data-bs-toggle="tab"
                        data-bs-target="#inactiveClientTab" type="button" role="tab" aria-controls="profile"
                        aria-selected="false">Inactive</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="activeClientTab" role="tabpanel"
                    aria-labelledby="active-client-tab">
                    <table id="activeClientTable" class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>CreatedAt</th>
                                <th>UpdatedAt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="inactiveClientTab" role="tabpanel" aria-labelledby="inactive-client-tab">
                    <table id="inactiveClientTable" class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>CreatedAt</th>
                                <th>UpdatedAt</th>
                                <th>Actions</th>
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
            $('#activeClientTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('user.Payee') }}",
                    data: function(d) {
                        d.status = 'Active';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'CreatedAt',
                        name: 'CreatedAt'
                    },
                    {
                        data: 'UpdatedAt',
                        name: 'UpdatedAt'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#inactiveClientTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ajax: {
                    url: "{{ route('user.Payee') }}",
                    data: function(d) {
                        d.status = 'Inactive';
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'CreatedAt',
                        name: 'CreatedAt'
                    },
                    {
                        data: 'UpdatedAt',
                        name: 'UpdatedAt'
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
