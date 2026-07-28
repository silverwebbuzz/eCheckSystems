@extends('layouts/layoutMaster')

@section('title', 'Add Package')

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
        <form action="{{ route('admin.package.store') }}" method="POST">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Add Package</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('admin.package') }}" class="btn btn-primary mr-4">
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
                            <label class="col-sm-2 col-form-label" for="description">Description</label>
                            <div class="col-sm-10">
                                <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                                @if ($errors->has('description'))
                                    <span class="text-danger">
                                        {{ $errors->first('description') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="price">Price</label>
                            <div class="col-sm-10">
                                <input type="text" name="price" id="price" class="form-control"
                                    value="{{ old('price') }}" />
                                @if ($errors->has('price'))
                                    <span class="text-danger">
                                        {{ $errors->first('price') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="duration">Duration(In Days)</label>
                            <div class="col-sm-10">
                                <input type="text" name="duration" id="duration" class="form-control"
                                    value="{{ old('duration') }}" />
                                @if ($errors->has('duration'))
                                    <span class="text-danger">
                                        {{ $errors->first('duration') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="check_limit">Check Limit(Per Month)</label>
                            <div class="col-sm-10">
                                <input type="text" name="check_limit" id="check_limit" class="form-control"
                                    value="{{ old('check_limit') }}" />
                                @if ($errors->has('check_limit'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_limit') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="frequency">Recurring Payment Frequency</label>
                            <div class="col-sm-10">
                                <select id="frequency" name="frequency" class="form-control form-select">
                                    <option value="" {{ old('frequency') == '' ? 'selected' : '' }}>Select Frequency
                                    </option>
                                    <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Daily
                                    </option>
                                    <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Weekly
                                    </option>
                                    <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly
                                    </option>
                                    <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly
                                    </option>
                                </select>
                                @if ($errors->has('frequency'))
                                    <span class="text-danger">
                                        {{ $errors->first('frequency') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="web_form">Web Form</label>
                            <div class="col-sm-10">
                                <select id="web_form" name="web_form" class="form-control form-select">
                                    <option value="1" {{ old('web_form') == 1 ? 'selected' : '' }}>Enable
                                    </option>
                                    <option value="0" {{ old('web_form') == 0 ? 'selected' : '' }}>Disable
                                    </option>
                                </select>
                                @if ($errors->has('web_form'))
                                    <span class="text-danger">
                                        {{ $errors->first('web_form') }}
                                    </span>
                                @endif
                            </div>
                        </div>
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
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
