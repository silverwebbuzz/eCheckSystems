@extends('layouts/layoutMaster')

@section('title', 'Quickbooks - Companies')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])

@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title">Quickbooks - Companies</h5>
            <a class="btn btn-primary" href="{{ route('qbo.connect') }}">Connect</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->address }}</td>
                        <td>
                            <a class="@if($company->status == 'connected') text-danger @else text-success @endif" href="{{ route('qbo.connect.company', $company->id) }}">
                                {!! ($company->status == 'connected') ? '<i class="ti ti-unlink"></i> Disconnect' : '<i class="ti ti-plug"></i> Connect' !!}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No companies found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection