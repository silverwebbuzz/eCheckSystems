@extends('check_delivery.layout')

@section('title', 'Log in')

@section('content')
    <div class="ec-card p-4 p-md-5 mx-auto" style="max-width: 460px;">
        <h3 class="fw-bold mb-1">Log in</h3>
        <p class="text-secondary small mb-4">See and print the checks sent to you.</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('payee.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                <label class="form-check-label small" for="remember">Keep me logged in</label>
            </div>
            <button type="submit" class="btn btn-ec w-100">Log in</button>
        </form>

        <p class="small text-secondary mt-4 mb-0">
            No account yet? Open the <strong>Get Check</strong> link from your check email and choose
            "Create an account".
        </p>
    </div>
@endsection
