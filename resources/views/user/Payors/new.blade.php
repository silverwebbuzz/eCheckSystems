@extends('layouts/layoutMaster')

@section('title', 'Add Payors')

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
    <div class="row">
        <form action="{{ route('user.payors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Payor</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('user.Payors') }}" class="btn btn-primary mr-4">
                                {{-- &nbsp; --}}
                                Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name') }}" />
                                @if ($errors->has('name'))
                                    <span class="text-danger">
                                        {{ $errors->first('name') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="email">Email</label>
                            <div class="col-sm-10">
                                <input type="text" name="email" id="email" class="form-control"
                                    value="{{ old('email') }}" />
                                @if ($errors->has('email'))
                                    <span class="text-danger">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address1">Address 1</label>
                            <div class="col-sm-10">
                                <textarea id="address1" name="address1" class="form-control">{{ old('address1') }}</textarea>
                                @if ($errors->has('address1'))
                                    <span class="text-danger">
                                        {{ $errors->first('address1') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address1">Address 2</label>
                            <div class="col-sm-10">
                                <textarea id="address1" name="address2" class="form-control">{{ old('address2') }}</textarea>
                                @if ($errors->has('address1'))
                                    <span class="text-danger">
                                        {{ $errors->first('address2') }}
                                    </span>
                                @endif
                            </div>
                        </div> --}}
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="city">City</label>
                            <div class="col-sm-10">
                                <input type="text" name="city" id="city" class="form-control"
                                    value="{{ old('city') }}" />
                                @if ($errors->has('city'))
                                    <span class="text-danger">
                                        {{ $errors->first('city') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="state">State</label>
                            <div class="col-sm-10">
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
                                            {{ old('state') == $state ? 'selected' : '' }}>
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
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="zip">Zip</label>
                            <div class="col-sm-10">
                                <input type="text" name="zip" id="zip" class="form-control"
                                    value="{{ old('zip') }}" />
                                @if ($errors->has('zip'))
                                    <span class="text-danger">
                                        {{ $errors->first('zip') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="bank_name">Bank Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" />
                                @if ($errors->has('bank_name'))
                                    <span class="text-danger">
                                        {{ $errors->first('bank_name') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="account_number">Account Number</label>
                            <div class="col-sm-10">
                                <input type="text" inputmode="numeric" pattern="[0-9]*" name="account_number"
                                    id="account_number" class="form-control" value="{{ old('account_number') }}" />
                                @if ($errors->has('account_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('account_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="routing_number">Routing Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="routing_number" id="routing_number" class="form-control"
                                    value="{{ old('routing_number') }}" maxlength="9"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0,9);" />
                                @if ($errors->has('routing_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('routing_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" id="category" name="category" value="RP" />
                        <input type="hidden" name="type" id="type" value="Payor" />
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="status">Status</label>
                            <div class="col-sm-10">
                                <select id="status" name="status" class="form-control form-select">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                                @if ($errors->has('status'))
                                    <span class="text-danger">
                                        {{ $errors->first('status') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- <div class="row mb-6">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10 p-6">
                                <label class="switch switch-square" for="same_as">
                                    <input type="checkbox" class="switch-input" name="same_as" id="same_as" />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on"></span>
                                        <span class="switch-off"></span>
                                    </span>
                                    <span class="switch-label">Same As Client</span>
                                </label>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
