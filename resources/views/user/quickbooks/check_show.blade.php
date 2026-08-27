@extends('layouts/layoutMaster')

@section('title', 'QuickBooks Check #' . $check->CheckNumber)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Check #{{ $check->CheckNumber }}</h4>
            <div class="text-muted">
                Status: <strong>{{ $check->Status }}</strong>
                @if ($check->check_number_conflict)
                    <span class="badge bg-label-warning ms-1">Check number conflict</span>
                @endif
                @if ($check->qbo_print_later)
                    <span class="badge bg-label-primary ms-1">Print later in QBO</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('qbo.checks') }}" class="btn btn-outline-secondary">Back</a>
            @if ($check->Status !== 'generated')
                <a href="{{ route('check_generate', $check->CheckID) }}" class="btn btn-primary">Generate / Print</a>
            @endif
        </div>
    </div>

    @if ($check->check_number_conflict)
        <div class="alert alert-warning">
            Warning: another check in your account already uses number <strong>{{ $check->CheckNumber }}</strong>.
            Review before printing.
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Details</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Payee</dt>
                        <dd class="col-sm-8">{{ $check->payee->Name ?? '—' }}</dd>
                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">${{ number_format((float) $check->Total, 2) }}</dd>
                        <dt class="col-sm-4">Issue date</dt>
                        <dd class="col-sm-8">{{ $check->IssueDate ? date('m/d/Y', strtotime($check->IssueDate)) : '—' }}</dd>
                        <dt class="col-sm-4">Memo</dt>
                        <dd class="col-sm-8">{{ $check->Memo ?: '—' }}</dd>
                        <dt class="col-sm-4">QBO Id</dt>
                        <dd class="col-sm-8">{{ $check->qbo_id ?: '—' }}</dd>
                        <dt class="col-sm-4">QBO company</dt>
                        <dd class="col-sm-8">{{ $check->qboCompany->name ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Category / line items</h5></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($check->lineItems as $line)
                                <tr>
                                    <td>{{ $line->line_no }}</td>
                                    <td>{{ $line->account_name ?: '—' }}</td>
                                    <td>{{ $line->description ?: '—' }}</td>
                                    <td class="text-end">${{ number_format((float) $line->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No line items synced.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
