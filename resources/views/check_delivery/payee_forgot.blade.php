@extends('check_delivery.layout')

@section('title', 'Forgot password')

@section('content')
    <div class="ec-card p-4 p-md-5 mx-auto" style="max-width: 460px;">
        <h3 class="fw-bold mb-1">Forgot your password?</h3>
        <p class="text-secondary small mb-4">Enter the email where you receive your checks. We will send you a link to set a new password.</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('payee.password.email') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-ec w-100">Send reset link</button>
        </form>

        <p class="small text-secondary mt-4 mb-0 text-center">
            <a href="{{ route('payee.login') }}">Back to log in</a>
        </p>
    </div>
@endsection
