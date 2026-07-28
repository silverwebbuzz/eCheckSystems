@extends('layouts/layoutMaster')

@section('title', 'Admin Profile')

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
    <div class="col-xxl">
        <div class="card mb-6">
            @if (session('profile_success'))
                <div class="alert alert-success">
                    {{ session('profile_success') }}
                </div>
            @endif
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Admin Profile</h5>
            </div>
            <div class="card-body">
                <form id="profile-form" action="{{ route('admin.update_profile') }}" method="POST">
                    @csrf
                    <input type="hidden" id="admin_id" name="admin_id" value="{{ $admin->AdminID }}">
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">User name</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                        class="ti ti-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="John Doe" aria-label="John Doe" value="{{ $admin->Username }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            @if ($errors->has('username'))
                                <span class="text-danger">
                                    {{ $errors->first('username') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-email">Email</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="text" id="email" name="email" class="form-control"
                                    value="{{ $admin->Email }}" placeholder="john.doe" aria-label="john.doe"
                                    aria-describedby="basic-icon-default-email2" />
                            </div>
                            @if ($errors->has('email'))
                                <span class="text-danger">
                                    {{ $errors->first('email') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
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
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form id="change-password" action="{{ route('admin.change-password') }}" method="POST">
                    @csrf
                    <input type="hidden" id="admin_id" name="admin_id" value="{{ $admin->AdminID }}">
                    <div class="row form-password-toggle mb-3">
                        <label class="col-sm-3 col-form-label" for="old_password">Old Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" id="old_password" name="old_password" class="form-control"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                        class="ti ti-eye-off"></i></span>
                            </div>
                            @if ($errors->has('old_password'))
                                <span class="text-danger">
                                    {{ $errors->first('old_password') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row form-password-toggle mb-3">
                        <label class="col-sm-3 col-form-label" for="new_password">New Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" id="new_password" name="new_password" class="form-control"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                        class="ti ti-eye-off"></i></span>
                            </div>
                            @if ($errors->has('new_password'))
                                <span class="text-danger">
                                    {{ $errors->first('new_password') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row form-password-toggle mb-3">
                        <label class="col-sm-3 col-form-label" for="new_password_confirmation">Confirm Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input type="password" iid="new_password_confirmation" name="new_password_confirmation"
                                    class="form-control"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                        class="ti ti-eye-off"></i></span>
                            </div>
                            @if ($errors->has('new_password_confirmation'))
                                <span class="text-danger">
                                    {{ $errors->first('new_password_confirmation') }}
                                </span>
                            @endif
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
