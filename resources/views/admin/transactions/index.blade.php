@extends('layouts/layoutMaster')

@section('title', 'Transactions')

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
        <div class="d-flex gap-2 align-items-center">
            <h5 class="card-header">Transactions</h5>
             <select id="dateFilter" class="form-select w-25">
                    <option value="">All</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="custom">Custom Range</option>
                </select>
                <div class="col-md-3 d-none" id="startDateDiv">
                <input type="text" id="startDate" class="form-control" placeholder="Start Date">
            </div>

            <div class="col-md-3 d-none" id="endDateDiv">
                <input type="text" id="endDate" class="form-control" placeholder="End Date">
            </div>
        </div>
        

        <div class="card-datatable table-responsive pt-0">
            <table id="transactions-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payment Date</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@section('page-script')
    <script>
        $(document).ready(function () {

            flatpickr("#startDate", {
                dateFormat: "m/d/Y"
            });

            flatpickr("#endDate", {
                dateFormat: "m/d/Y"
            });

            var table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.transactions.list') }}",
                    data: function (d) {
                        d.filter = $('#dateFilter').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                    }
                },
                //  columnDefs: [
                //     { width: "5%", targets: 0 },  // first column width 20%
                //     { width: "25%", targets: 1 },  // first column width 20%
                //     { width: "40%", targets: 2 },  // second column width 50%
                //      { width: "15%", targets: 3 },
                //     // ... adjust as needed
                // ],
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'paymentDate',
                    name: 'paymentDate',
                },
                {
                    data: 'firstName',
                    name: 'firstName',
                },
                {
                    data: 'lastName',
                    name: 'lastName',
                },
                {
                    data: 'email',
                    name: 'email',
                },
                {
                    data: 'details',
                    name: 'details',
                },
                {
                    data: 'PaymentAmount',
                    name: 'PaymentAmount',
                },
                {
                    'data': 'status',
                    'name': 'status',
                },
                    // {
                    //     data: 'actions',
                    //     name: 'actions',
                    //     orderable: false,
                    //     searchable: false
                    // }
                ]
            });

            $('#dateFilter').change(function () {
                if ($(this).val() === 'custom') {
                    $('#startDateDiv, #endDateDiv').removeClass('d-none');
                } else {
                    $('#startDateDiv, #endDateDiv').addClass('d-none');
                }
                table.draw();
            });

            $('#startDate, #endDate').change(function () {
                table.draw();
            });
        });
    </script>
@endsection