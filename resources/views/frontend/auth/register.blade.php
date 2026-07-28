@php
    $customizerHidden = 'customizer-hide';
    $configData['layout'] = 'blank';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login Basic - Pages')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')

    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
    <style>
        .authentication-wrapper.authentication-basic .authentication-inner {
            max-width: 800px;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-auth.js'])
    <script>
        $(document).ready(function () {
            $.getJSON("https://ipapi.co/json/", function (data) {
                if (data && data.timezone) {
                    $('#timezone').val(data.timezone);
                    console.log("Time zone set to:", data.timezone);
                } else {
                    $('#timezone').val('UTC');
                    console.warn("Timezone not found. Defaulting to UTC.");
                }
            }).fail(function () {
                $('#timezone').val('UTC');
                console.error("Failed to fetch IP-based timezone. Defaulted to UTC.");
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">

                <!-- Register Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-6">
                            <a href="{{ url('/') }}" class="app-brand-link">
                                @include('_partials.macros')
                            </a>
                        </div>
                        <!-- /Logo -->
                        <p class="mb-6 fw-bold text-danger">
                            To prevent fraud and protect our customers, all information provided during registration will be
                            thoroughly reviewed and verified.
                            Accounts will not be activated until verification is successfully completed.
                        </p>
                        <p class="mb-6">Please create your account.</p>
                        <form id="formAuthentication" class="mb-6" action="{{ route('register.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="firstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname"
                                        placeholder="First name" value="{{ old('firstname') }}" autofocus>
                                    @if ($errors->has('firstname'))
                                        <span class="text-danger">
                                            {{ $errors->first('firstname') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="lastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname"
                                        placeholder="Last name" value="{{ old('lastname') }}">
                                    @if ($errors->has('lastname'))
                                        <span class="text-danger">
                                            {{ $errors->first('lastname') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- <div class="mb-6">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Enter your username" value="{{ old('username') }}">
                                    @if ($errors->has('username'))
                                    <span class="text-danger">
                                        {{ $errors->first('username') }}
                                    </span>
                                    @endif
                                </div> --}}
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        placeholder="Enter your email" value="{{ old('email') }}">
                                    @if ($errors->has('email'))
                                        <span class="text-danger">
                                            {{ $errors->first('email') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 mb-6">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                                        placeholder="Enter your phone number" value="{{ old('phone_number') }}">
                                    @if ($errors->has('phone_number'))
                                        <span class="text-danger">
                                            {{ $errors->first('phone_number') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 mb-6">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                        placeholder="Enter your company name" value="{{ old('company_name') }}">
                                    @if ($errors->has('company_name'))
                                        <span class="text-danger">
                                            {{ $errors->first('company_name') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 mb-6">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address"
                                        placeholder="Enter your address">{{ old('address') }}</textarea>

                                    @if ($errors->has('address'))
                                        <span class="text-danger">
                                            {{ $errors->first('address') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        placeholder="Enter your city" value="{{ old('city') }}">
                                    @if ($errors->has('city'))
                                        <span class="text-danger">
                                            {{ $errors->first('city') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="state" class="form-label">State</label>
                                    <select name="state" id="state" class="form-control">
                                        <option value="">-- Select State --</option>
                                        @php
                                            $states = [
                                                'Alabama',
                                                'Alaska',
                                                'Arizona',
                                                'Arkansas',
                                                'California',
                                                'Colorado',
                                                'Connecticut',
                                                'Delaware',
                                                'Florida',
                                                'Georgia',
                                                'Hawaii',
                                                'Idaho',
                                                'Illinois',
                                                'Indiana',
                                                'Iowa',
                                                'Kansas',
                                                'Kentucky',
                                                'Louisiana',
                                                'Maine',
                                                'Maryland',
                                                'Massachusetts',
                                                'Michigan',
                                                'Minnesota',
                                                'Mississippi',
                                                'Missouri',
                                                'Montana',
                                                'Nebraska',
                                                'Nevada',
                                                'New Hampshire',
                                                'New Jersey',
                                                'New Mexico',
                                                'New York',
                                                'North Carolina',
                                                'North Dakota',
                                                'Ohio',
                                                'Oklahoma',
                                                'Oregon',
                                                'Pennsylvania',
                                                'Rhode Island',
                                                'South Carolina',
                                                'South Dakota',
                                                'Tennessee',
                                                'Texas',
                                                'Utah',
                                                'Vermont',
                                                'Virginia',
                                                'Washington',
                                                'West Virginia',
                                                'Wisconsin',
                                                'Wyoming',
                                            ];
                                        @endphp

                                        @foreach ($states as $state)
                                            <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>
                                                {{ $state }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('state'))
                                        <span class="text-danger">
                                            {{ $errors->first('state') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 col-lg-4 mb-6">
                                    <label for="zip" class="form-label">Zip</label>
                                    <input type="text" class="form-control" id="zip" name="zip" placeholder="Enter your zip"
                                        value="{{ old('zip') }}">
                                    @if ($errors->has('zip'))
                                        <span class="text-danger">
                                            {{ $errors->first('zip') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6 mb-6 form-password-toggle">
                                    <label class="form-label" for="password">Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" class="form-control" name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password" value="{{ old('password') }}" />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                        @if ($errors->has('password'))
                                            <span class="text-danger">
                                                {{ $errors->first('password') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-6 form-password-toggle">
                                    <label class="form-label" for="confirm-password">Confirm Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="confirm-password" class="form-control"
                                            name="confirm-password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="confirm-password" value="{{ old('confirm-password') }}" />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                </div>
                                {{-- <div class="mb-6">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select name="timezone" id="timezone" class="form-control">
                                        <option value="America/Chicago" selected>Central Time (CT)</option>
                                        <option value="America/New_York">Eastern Time (ET)</option>
                                        <option value="America/Denver">Mountain Time (MT)</option>
                                        <option value="America/Los_Angeles">Pacific Time (PT)</option>
                                    </select>
                                </div> --}}
                                <input type="hidden" id="timezone" name="timezone">
                                <div class="my-8">
                                    <div class="form-check mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms">
                                        <label class="form-check-label" for="terms-conditions">
                                            I agree to
                                            <a href="https://echecksystems.com/privacy/" target="_blank">privacy policy &
                                                terms</a>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary d-grid w-100">
                                Sign up
                            </button>
                        </form>

                        <p class="text-center">
                            <span>Already have an account?</span>
                            <a href="{{ route('user.login') }}">
                                <span>Sign in instead</span>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        Inputmask({
            mask: "999-999-9999",
            placeholder: "",             // No placeholders
            showMaskOnHover: false,      // Don't show mask on hover
            showMaskOnFocus: false,      // Don't show mask on focus
        }).mask("#phone_number");
    </script>
@endsection