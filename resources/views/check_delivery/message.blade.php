@extends('check_delivery.layout')

@section('title', $title)

@section('content')
    <div class="ec-card p-4 p-md-5 text-center">
        <h3 class="fw-bold mb-3">{{ $title }}</h3>
        <p class="mb-0 text-secondary">{{ $message }}</p>
    </div>
@endsection
