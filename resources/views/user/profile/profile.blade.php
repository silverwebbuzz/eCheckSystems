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
        $progress = $package != '-1' ? ($package_data['remainingDays'] * 100) / $package_data['total_days'] : 0;
    @endphp
    <div class="col-xxl">
        <div class="card mb-6">
            @if (session('profile_success'))
                <div class="alert alert-success">
                    {{ session('profile_success') }}
                </div>
            @endif
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">User Profile</h5>
            </div>
            <div class="card-body">
                <form id="profile-form" action="{{ route('user.update_profile') }}" method="POST">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id" value="{{ $user->UserID }}">
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">First name</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                        class="ti ti-user"></i></span>
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                    placeholder="John Doe" aria-label="John Doe" value="{{ $user->FirstName }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            @if ($errors->has('firstname'))
                                <span class="text-danger">
                                    {{ $errors->first('firstname') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">Last name</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                        class="ti ti-user"></i></span>
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                    placeholder="John Doe" aria-label="John Doe" value="{{ $user->LastName }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            @if ($errors->has('lastname'))
                                <span class="text-danger">
                                    {{ $errors->first('lastname') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">User name</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                        class="ti ti-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="John Doe" aria-label="John Doe" value="{{ $user->Username }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            @if ($errors->has('username'))
                                <span class="text-danger">
                                    {{ $errors->first('username') }}
                                </span>
                            @endif
                        </div>
                    </div> --}}
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-email">Email</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="text" id="email" name="email" class="form-control"
                                    value="{{ $user->Email }}" placeholder="john.doe" aria-label="john.doe"
                                    aria-describedby="basic-icon-default-email2" />
                            </div>
                            @if ($errors->has('email'))
                                <span class="text-danger">
                                    {{ $errors->first('email') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">Phone Number</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-phone2" class="input-group-text"><i
                                        class="ti ti-phone"></i></span>
                                <input type="text" class="form-control" id="phone_number" name="phone_number"
                                    placeholder="John Doe" aria-label="John Doe" value="{{ $user->PhoneNumber }}"
                                    aria-describedby="basic-icon-default-phone2" />
                            </div>
                            @if ($errors->has('phone_number'))
                                <span class="text-danger">
                                    {{ $errors->first('phone_number') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="basic-icon-default-fullname">Company Name</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                        class="ti ti-user"></i></span>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                    placeholder="company name" value="{{ $user->CompanyName }}"
                                    aria-describedby="basic-icon-default-fullname2" />
                            </div>
                            @if ($errors->has('lastname'))
                                <span class="text-danger">
                                    {{ $errors->first('lastname') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="address">Address</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <textarea type="text" class="form-control" id="address" name="address">{{ $user->Address }}</textarea>
                            </div>
                            @if ($errors->has('address'))
                                <span class="text-danger">
                                    {{ $errors->first('address') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="city">City</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <input type="text" class="form-control" id="city" name="city"
                                    placeholder="City" value="{{ $user->City }}" />
                            </div>
                            @if ($errors->has('city'))
                                <span class="text-danger">
                                    {{ $errors->first('city') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="city">State</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
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
                                        <option value="{{ $state }}"
                                            {{ $user->State == $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($errors->has('state'))
                                <span class="text-danger">
                                    {{ $errors->first('state') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="city">Zip</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <input type="text" class="form-control" id="zip" name="zip"
                                    placeholder="Zip" value="{{ $user->Zip }}" />
                            </div>
                            @if ($errors->has('zip'))
                                <span class="text-danger">
                                    {{ $errors->first('zip') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- <div class="row mb-6">
                        <label class="col-sm-2 col-form-label" for="timezone">Timezone</label>
                        <div class="col-sm-10">
                            <div class="input-group input-group-merge">
                                <select name="timezone" id="timezone" class="form-control">
                                    @php
                                        $timezones = [
                                           'America/Chicago' => 'Central Time (CT)',
                                           'America/New_York' => 'Eastern Time (ET)',
                                           'America/Denver' => 'Mountain Time (MT)',
                                           'America/Los_Angeles' => 'Pacific Time (PT)',
                                        ];
                                    @endphp

                                    @foreach ($timezones as $key => $timezone)
                                        <option value="{{ $key }}"
                                            {{ $user->timezone == $key ? 'selected' : '' }}>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> --}}
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
                <form id="change-password" action="{{ route('user.change-password') }}" method="POST">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id" value="{{ $user->UserID }}">
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

    <div class="col-xxl">
        <div class="card mb-6">
            <h5 class="card-header">Current Plan</h5>
            <div class="card-body">
                <div class="row row-gap-4 row-gap-xl-0">
                    <div class="col-xl-6 order-1 order-xl-0">
                        <div class="mb-4">
                            <h6 class="mb-1">Your Current Plan is
                                {{ $package != '-1' ? $package_data['package_name'] : 'Trial' }}</h6>
                            <p>A simple start for everyone</p>
                        </div>
                        @if ($package != '-1')
                            <div class="mb-4">
                                <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                                <p>We will send you a notification upon Subscription expiration</p>
                            </div>
                        @endif
                    </div>
                    @if ($package != '-1')
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
                    @endif
                </div>
            </div>
        </div>
    </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        Inputmask({
            mask: "999-999-9999",
            placeholder: "",             // No placeholders
            showMaskOnHover: false,      // Don't show mask on hover
            showMaskOnFocus: false,      // Don't show mask on focus
        }).mask("#phone_number");
    </script>
@endsection
