@extends('layouts/layoutMaster')

@section('title', 'How it works')

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
            <h5 class="card-header">How it works ?</h5>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="how-it-works-table" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@section('page-script')
    <script>
        $(document).ready(function () {
            $('#how-it-works-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.how_it_works.list') }}",
                 columnDefs: [
                    { width: "5%", targets: 0 },  // first column width 20%
                    { width: "25%", targets: 1 },  // first column width 20%
                    { width: "40%", targets: 2 },  // second column width 50%
                     { width: "15%", targets: 3 },
                    // ... adjust as needed
                ],
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'section',
                },
                {
                    data: 'link',
                    render: function (data, type, row) {
                        // // // Create a temporary DOM element to strip HTML
                        // // const div = document.createElement('div');
                        // // div.innerHTML = data;
                        // // const text = div.textContent || div.innerText || '';

                        // // Limit to 100 characters
                        // if (data.length > 100) {
                        //     return data.substring(0, 250) + '...';
                        // }
                        return data;
                    }
                },
                {
                    'data': 'status',
                    'name': 'status',
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