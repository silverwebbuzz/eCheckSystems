@extends('check_delivery.layout')

@section('title', 'Check from ' . $details['sender'])

@section('top-right')
    @if ($payeeLoggedIn)
        <a href="{{ route('payee.checks') }}" class="btn btn-sm btn-outline-ec">My Checks</a>
    @else
        <a href="{{ route('payee.login', ['email' => $delivery->payee_email]) }}" class="btn btn-sm btn-outline-ec">Log in</a>
    @endif
@endsection

@section('content')
    {{-- Check summary --}}
    <div class="ec-card p-4 p-md-5 mb-4">
        <div class="ec-label mb-1">You have a check from</div>
        <h2 class="fw-bold mb-4">{{ $details['sender'] }}</h2>

        <div class="row g-4 align-items-end">
            <div class="col-md-5">
                <div class="ec-label">Amount</div>
                <div class="ec-amount">${{ $details['amount'] }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ec-label">Check No.</div>
                <div class="fw-bold">{{ $details['check_number'] }}</div>
            </div>
            <div class="col-6 col-md-4">
                <div class="ec-label">Pay to</div>
                <div class="fw-bold">{{ $details['payee'] }}</div>
            </div>
            @if ($details['memo'] !== '')
                <div class="col-12">
                    <div class="ec-label">Memo</div>
                    <div>{{ $details['memo'] }}</div>
                </div>
            @endif
        </div>
    </div>

    <h5 class="fw-bold mb-3">How would you like to get your check?</h5>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        {{-- Option 1: guest --}}
        <div class="col-md-6">
            <div class="ec-option bg-white p-4">
                <h5>Continue as guest</h5>
                <p class="text-secondary small">Print the check now and deposit it like any other check. No account needed.</p>

                <form method="POST" action="{{ route('check_delivery.print', ['token' => $token]) }}" target="_blank" id="guest-form">
                    @csrf
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirm" required>
                        <label class="form-check-label small" for="confirm">
                            I am <strong>{{ $details['payee'] ?: 'the payee' }}</strong> and I will print this check on plain white paper.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-ec w-100" id="print-btn" disabled>Print now</button>
                    <div class="form-text" id="print-hint">Tick the box above to enable printing.</div>
                </form>

                <div class="small text-secondary mt-3">
                    <strong>Printing tips:</strong> the check opens in a new tab. Click the printer icon,
                    use <strong>Letter</strong> paper and <strong>Actual size / 100%</strong> scale.
                    You can print it again if something goes wrong.
                </div>
            </div>
        </div>

        {{-- Option 2: account --}}
        <div class="col-md-6">
            <div class="ec-option bg-white p-4">
                <h5>Create an account to get your payment</h5>

                @if ($payeeLoggedIn)
                    <p class="text-secondary small">You are logged in. This check is saved in your account.</p>
                    <a href="{{ route('payee.checks') }}" class="btn btn-outline-ec w-100">Go to My Checks</a>
                @elseif ($hasAccount)
                    <p class="text-secondary small">You already have an account for <strong>{{ $delivery->payee_email }}</strong>. Log in to see this check and all your other checks.</p>
                    <a href="{{ route('payee.login', ['email' => $delivery->payee_email]) }}" class="btn btn-outline-ec w-100">Log in</a>
                @else
                    <p class="text-secondary small">Keep this check and all future checks sent to <strong>{{ $delivery->payee_email }}</strong> in one place. Free.</p>
                    <form method="POST" action="{{ route('check_delivery.account', ['token' => $token]) }}">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control" placeholder="Your name"
                                value="{{ old('name', $details['payee']) }}" required>
                        </div>
                        <div class="mb-2">
                            <input type="email" class="form-control" value="{{ $delivery->payee_email }}" disabled>
                        </div>
                        <div class="mb-2">
                            <input type="password" name="password" class="form-control" placeholder="Password (min. 8 characters)" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-outline-ec w-100">Create account</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var box = document.getElementById('confirm');
            var btn = document.getElementById('print-btn');
            var hint = document.getElementById('print-hint');
            if (!box || !btn) return;
            var sync = function () {
                btn.disabled = !box.checked;
                hint.style.display = box.checked ? 'none' : '';
            };
            box.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
