@extends('layouts/layoutMaster')

@section('title', 'Edit How it works')

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
        <form action="{{ route('admin.how_it_works.update', $howItWork) }}" method="POST">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Edit</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('admin.how_it_works') }}" class="btn btn-primary mr-4">
                                Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="section">Section</label>
                            <div class="col-sm-10">
                                <input type="text" name="section" id="section" class="form-control"
                                    value="{{ $howItWork->section }}" disabled />
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="link">Link</label>
                            <div class="col-sm-10 ">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-link"></i></span>
                                    <input type="text" name="link" id="link" class="form-control"
                                        value="{{ $howItWork->link }}" />
                                </div>

                                @if ($errors->has('link'))
                                    <span class="text-danger">
                                        {{ $errors->first('link') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="status">Status</label>
                            <div class="col-sm-10">
                                <select id="status" name="status" class="form-control form-select">
                                   <option value="Active" @selected($howItWork->status == 'Active')>Active
                                    </option>
                                    <option value="Inactive" @selected($howItWork->status == 'Inactive')>
                                        Inactive
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
