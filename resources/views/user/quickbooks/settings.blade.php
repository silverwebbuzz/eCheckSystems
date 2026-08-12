@extends('layouts/layoutMaster')

@section('title', 'Settings — QuickBooks')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($accountError)
        <div class="alert alert-warning">Could not load QuickBooks accounts: {{ $accountError }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">QuickBooks Online</h5>
                <small class="text-muted">Sandbox first. Connect your QBO company, map bank identity. New/updated checks arrive via <strong>webhook</strong> (real-time). Use Sync now only for a one-time catch-up.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('qbo.connect') }}" class="btn btn-primary">
                    <i class="ti ti-plug me-1"></i> Connect QuickBooks
                </a>
                @if ($active)
                    <a href="{{ route('qbo.sync', $active->id) }}" class="btn btn-success"
                       onclick="return confirm('Sync all checks from the active QuickBooks company now?')">
                        <i class="ti ti-refresh me-1"></i> Sync now
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <p class="mb-0">
                Environment:
                <strong>{{ config('quickbooks.environment') === 'development' ? 'Sandbox' : 'Production' }}</strong>
                · Inbound:
                <strong>Webhook + queue</strong>
                <code class="ms-1">POST {{ url('/api/quickbooks/webhook') }}</code>
                · Queue: <code>quickbooks</code>
                @if ($active && $active->last_sync_at)
                    · Last sync: {{ $active->last_sync_at->format('m/d/Y H:i') }}
                @endif
            </p>
            @if (!config('quickbooks.webhook_verifier_token'))
                <div class="alert alert-warning mt-3 mb-0">
                    Set <code>QBO_WEBHOOK_VERIFIER_TOKEN</code> in <code>.env</code> (Intuit Developer → Webhooks → Show token)
                    and subscribe to entity <strong>Purchase</strong> (Create / Update / Delete / Void).
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Connected companies</h5>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>QBO Company</th>
                        <th>Realm</th>
                        <th>Status</th>
                        <th>Mapped local company</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <div class="small text-muted">{{ $company->address }}</div>
                            </td>
                            <td>{{ $company->realm_id }}</td>
                            <td>
                                @if ($company->status === 'connected')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $company->localCompany->Name ?? '— not mapped —' }}</td>
                            <td class="d-flex gap-2 flex-wrap">
                                @if ($company->status !== 'connected')
                                    <a href="{{ route('qbo.setActive', $company->id) }}" class="btn btn-sm btn-outline-success">Set active</a>
                                @endif
                                <a href="{{ route('qbo.disconnect', $company->id) }}" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Disconnect this QuickBooks company?')">Disconnect</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No QuickBooks companies connected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($active)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Mapping for active company: {{ $active->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('qbo.updateMapping', $active->id) }}">
                    @csrf
                    <div class="row mb-4">
                        <label class="col-md-3 col-form-label">Our Company (bank on PDF)</label>
                        <div class="col-md-9">
                            <select name="company_id" class="form-select" required>
                                <option value="">Select company…</option>
                                @foreach ($localCompanies as $lc)
                                    <option value="{{ $lc->CompanyID }}" @selected((string) $active->company_id === (string) $lc->CompanyID)>
                                        {{ $lc->Name }} @if($lc->BankName) ({{ $lc->BankName }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Used as the bank identity when printing imported QuickBooks checks.
                                @if ($localCompanies->isEmpty())
                                    <a href="{{ route('user.company.add') }}">Add a company first</a>.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label class="col-md-3 col-form-label">Default bank account (QBO)</label>
                        <div class="col-md-9">
                            <select name="default_bank_account_id" id="default_bank_account_id" class="form-select" required>
                                <option value="">Select bank account…</option>
                                @foreach ($accounts['bank'] as $acct)
                                    <option value="{{ $acct['id'] }}"
                                        data-name="{{ $acct['name'] }}"
                                        @selected((string) $active->default_bank_account_id === (string) $acct['id'])>
                                        {{ $acct['name'] }} ({{ $acct['type'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="default_bank_account_name" id="default_bank_account_name"
                                   value="{{ $active->default_bank_account_name }}">
                            <div class="form-text">Account checks are paid from when pushing from Echeck → QuickBooks.</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <label class="col-md-3 col-form-label">Default expense / category account</label>
                        <div class="col-md-9">
                            <select name="default_expense_account_id" id="default_expense_account_id" class="form-select" required>
                                <option value="">Select expense account…</option>
                                @foreach ($accounts['expense'] as $acct)
                                    <option value="{{ $acct['id'] }}"
                                        data-name="{{ $acct['name'] }}"
                                        @selected((string) $active->default_expense_account_id === (string) $acct['id'])>
                                        {{ $acct['name'] }} ({{ $acct['type'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="default_expense_account_name" id="default_expense_account_name"
                                   value="{{ $active->default_expense_account_name }}">
                            <div class="form-text">Used for single-line checks and as fallback when a line has no category.</div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save mapping</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('page-script')
<script>
    function bindAccountName(selectId, hiddenId) {
        const select = document.getElementById(selectId);
        const hidden = document.getElementById(hiddenId);
        if (!select || !hidden) return;
        const sync = () => {
            const opt = select.options[select.selectedIndex];
            hidden.value = opt ? (opt.getAttribute('data-name') || '') : '';
        };
        select.addEventListener('change', sync);
        sync();
    }
    bindAccountName('default_bank_account_id', 'default_bank_account_name');
    bindAccountName('default_expense_account_id', 'default_expense_account_name');
</script>
@endsection
