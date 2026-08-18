@extends('layouts/layoutMaster')

@section('title', 'QuickBooks Checks')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">QuickBooks Checks</h5>
                <small class="text-muted">Only QuickBooks <strong>Check</strong> transactions (not credit card or cash expenses).</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('qbo.settings') }}" class="btn btn-outline-secondary">Settings</a>
                <a href="{{ route('qbo.sync') }}" class="btn btn-primary"
                   onclick="return confirm('Sync checks from active QuickBooks company?')">
                    <i class="ti ti-refresh me-1"></i> Sync now
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="qbo-checks-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Check #</th>
                            <th>Payee</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Lines</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    $(function () {
        $('#qbo-checks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('qbo.checks') }}',
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'CheckNumber' },
                { data: 'payee_name' },
                { data: 'amount_fmt' },
                { data: 'issue_date' },
                { data: 'status_badge' },
                { data: 'lines_count' },
                { data: 'actions', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endsection
