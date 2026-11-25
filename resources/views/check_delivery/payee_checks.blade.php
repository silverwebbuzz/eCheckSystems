@extends('check_delivery.layout')

@section('title', 'My Checks')

@section('top-right')
    <div class="d-flex align-items-center gap-3">
        <span class="small text-secondary d-none d-md-inline">{{ $account->email }}</span>
        <form method="POST" action="{{ route('payee.logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-ec">Log out</button>
        </form>
    </div>
@endsection

@section('content')
    <h3 class="fw-bold mb-1">My Checks</h3>
    <p class="text-secondary mb-4">Hello {{ $account->name }}. All checks sent to {{ $account->email }} appear here.</p>

    @if (empty($rows))
        <div class="ec-card p-4 text-center text-secondary">No checks yet.</div>
    @else
        <div class="ec-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>From</th>
                            <th>Check No.</th>
                            <th class="text-end">Amount</th>
                            <th>Received</th>
                            <th>Status</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php($d = $row['delivery'])
                            <tr>
                                <td>
                                    <strong>{{ $row['details']['sender'] }}</strong>
                                    @if ($row['details']['memo'] !== '')
                                        <div class="small text-secondary">{{ $row['details']['memo'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $row['details']['check_number'] }}</td>
                                <td class="text-end fw-bold">${{ $row['details']['amount'] }}</td>
                                <td>{{ optional($d->sent_at)->format('m/d/Y') }}</td>
                                <td>
                                    @if ($d->isExpired())
                                        <span class="badge text-bg-secondary">Expired</span>
                                    @elseif ($d->print_count > 0)
                                        <span class="badge text-bg-success">Printed</span>
                                    @else
                                        <span class="badge text-bg-primary">New</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if (!$d->isExpired())
                                        <a href="{{ route('payee.checks.print', ['id' => $d->id]) }}" target="_blank" class="btn btn-sm btn-ec">
                                            {{ $d->print_count > 0 ? 'Print again' : 'Print' }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <p class="small text-secondary mt-3">
            Print on plain white <strong>Letter</strong> paper at <strong>Actual size / 100%</strong>, then deposit like any other check.
        </p>
    @endif
@endsection
