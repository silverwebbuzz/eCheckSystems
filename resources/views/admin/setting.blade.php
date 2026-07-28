@extends('layouts/layoutMaster')

@section('title', 'Setting')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js', 'resources/assets/js/forms-editors.js'])
@endsection

@section('content')
    <div class="row">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{ route('admin.update_setting') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Settings</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Mail Host -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_host">MAIL HOST</label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_host" id="mail_host" class="form-control"
                                    value="{{ old('mail_host', $settings['mail_host']) }}" />
                                @if ($errors->has('mail_host'))
                                    <span class="text-danger">{{ $errors->first('mail_host') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mail Port -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_port">MAIL PORT</label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_port" id="mail_port" class="form-control"
                                    value="{{ old('mail_port', $settings['mail_port']) }}" />
                                @if ($errors->has('mail_port'))
                                    <span class="text-danger">{{ $errors->first('mail_port') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mail Username -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_username">MAIL USERNAME</label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_username" id="mail_username" class="form-control"
                                    value="{{ old('mail_username', $settings['mail_username']) }}" />
                                @if ($errors->has('mail_username'))
                                    <span class="text-danger">{{ $errors->first('mail_username') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mail Password -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_password">MAIL PASSWORD</label>
                            <div class="col-sm-10">
                                <input type="password" name="mail_password" id="mail_password" class="form-control"
                                    value="{{ old('mail_password', $settings['mail_password']) }}" />
                                @if ($errors->has('mail_password'))
                                    <span class="text-danger">{{ $errors->first('mail_password') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mail Encryption -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_encryption">MAIL ENCRYPTION</label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_encryption" id="mail_encryption" class="form-control"
                                    value="{{ old('mail_encryption', $settings['mail_encryption']) }}" />
                                @if ($errors->has('mail_encryption'))
                                    <span class="text-danger">{{ $errors->first('mail_encryption') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Mail From Address -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="mail_from_address">MAIL FROM ADDRESS</label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_from_address" id="mail_from_address" class="form-control"
                                    value="{{ old('mail_from_address', $settings['mail_from_address']) }}" />
                                @if ($errors->has('mail_from_address'))
                                    <span class="text-danger">{{ $errors->first('mail_from_address') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Stripe Public Key -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="stripe_public">STRIPE PUBLIC KEY</label>
                            <div class="col-sm-10">
                                <input type="text" name="stripe_public" id="stripe_public" class="form-control"
                                    value="{{ old('stripe_public', $settings['stripe_public']) }}" />
                                @if ($errors->has('stripe_public'))
                                    <span class="text-danger">{{ $errors->first('stripe_public') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Stripe Secret Key -->
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="stripe_secret">STRIPE SECRET KEY</label>
                            <div class="col-sm-10">
                                <input type="text" name="stripe_secret" id="stripe_secret" class="form-control"
                                    value="{{ old('stripe_secret', $settings['stripe_secret']) }}" />
                                @if ($errors->has('stripe_secret'))
                                    <span class="text-danger">{{ $errors->first('stripe_secret') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="admin_email">Admin Notification Email</label>
                            <div class="col-sm-10">
                                <input type="text" name="admin_email" id="admin_email" class="form-control"
                                    value="{{ old('admin_email', $settings['admin_email']) }}" />
                                @if ($errors->has('admin_email'))
                                    <span class="text-danger">{{ $errors->first('admin_email') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </form>
    </div>
@endsection
