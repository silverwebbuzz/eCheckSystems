@extends('layouts/layoutMaster')

@section('title', 'user Profile')

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
    @php
        $progress = ($package_data['remainingDays'] * 100) / $package_data['total_days'];
    @endphp
    <div class="col-xxl">
        <div class="card mb-6">
            <h5 class="card-header">Current Plan</h5>
            <div class="card-body">
                <div class="row row-gap-4 row-gap-xl-0">
                    <div class="col-xl-6 order-1 order-xl-0">
                        <div class="mb-4">
                            <h6 class="mb-1">Your Current Plan is {{ $package_data['package_name'] }}</h6>
                            <p>A simple start for everyone</p>
                        </div>
                        <div class="mb-4">
                            <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                            <p>We will send you a notification upon Subscription expiration</p>
                        </div>
                    </div>
                    <div class="col-xl-6 order-0 order-xl-0">
                        <div class="plan-statistics">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">Days</h6>
                                <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                    {{ $package_data['total_days'] }} Days</h6>
                            </div>
                            <div class="progress mb-1 bg-label-primary" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                    aria-valuemax="100" style="width: {{ $progress }}%"></div>
                            </div>
                            <small>Your plan requires update</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl">
        <div class="card mb-6">
            @if (session('pass_success'))
                <div class="alert alert-success">
                    {{ session('pass_success') }}
                </div>
            @endif
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Change Plan</h5>
            </div>
            <div class="card-body">
                <form id="change-plan" action="{{ route('admin.user.plan') }}" method="POST">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id" value="{{ $user->UserID }}">
                    <input type="hidden" id="old_plan" name="old_plan" value="{{ $user->CurrentPackageID }}">
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="status">Status</label>
                        <div class="col-sm-10">
                            <select id="plan" name="plan" class="form-control form-select">
                                @foreach ($packages as $package)
                                    <option value="{{ $package->PackageID }}"
                                        {{ $user->CurrentPackageID == $package->PackageID ? 'selected' : '' }}>
                                        {{ $package->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Change</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
