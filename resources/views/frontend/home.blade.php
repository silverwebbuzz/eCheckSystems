@extends('frontend/layouts')
@section('content')
    <div class="button-container">
        <a href="{{ route('user.login') }}" class="btn btn-primary">Login</a>
        <a href="{{ route('user.register') }}" class="btn btn-success">Signup</a>
    </div>
@endsection
