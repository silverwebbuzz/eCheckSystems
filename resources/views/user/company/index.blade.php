@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Company')

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
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Companies</h5>
            <a href="{{ route('user.company.add') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;">
                <i class="fa-solid fa-plus"></i> &nbsp; Add Company
            </a>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="companyTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Logo</th>
                        <th>Email</th>
                        <th>Routing Number</th>
                        <th>Status</th>
                        <th>CreatedAt</th>
                        <th>UpdatedAt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#companyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user.company') }}",
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
                        data: 'logo',
                        name: 'logo',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'RoutingNumber',
                        name: 'RoutingNumber'
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
