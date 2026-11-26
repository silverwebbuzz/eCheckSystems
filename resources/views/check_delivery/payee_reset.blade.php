@extends('check_delivery.layout')

@section('title', 'Set a new password')

@section('content')
    <div class="ec-card p-4 p-md-5 mx-auto" style="max-width: 460px;">
        <h3 class="fw-bold mb-1">Set a new password</h3>
        <p class="text-secondary small mb-4">Choose a new password for your account.</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('payee.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password">New password</label>
                <input type="password" name="password" id="password" class="form-control" minlength="8" placeholder="At least 8 characters" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password_confirmation">Confirm new password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-ec w-100">Save new password</button>
        </form>
    </div>
@endsection
