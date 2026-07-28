@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Web Form')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
    <!-- <script>
        const videoModal = document.getElementById('videoModal');
        const myVideo = document.getElementById('myVideo');

        videoModal.addEventListener('shown.bs.modal', function() {
            myVideo.play();
        });

        videoModal.addEventListener('hidden.bs.modal', function() {
            myVideo.pause();
            myVideo.currentTime = 0; // reset video
        });
    </script> -->
@endsection

@section('content')
    @if (false)
        <div class="card mb-5">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card mb-3">
                <h5 class="card-header">Genral Settings</h5>
                <div class="row">
                    <form action="{{ route('update_records_per_page') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Basic Layout -->
                        <div class="col-xxl">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0">Per Page Record</h5>
                                    <div class="d-flex align-items-center">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Receive Payment</label>
                                        <div class="col-sm-10">
                                            <select id="rp_page" name="rp_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.rp_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.rp_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.rp_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.rp_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.rp_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.rp_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('rp_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('rp_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Send Payment</label>
                                        <div class="col-sm-10">
                                            <select id="sp_page" name="sp_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.sp_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.sp_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.sp_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.sp_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.sp_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.rp_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('sp_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('sp_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Payee</label>
                                        <div class="col-sm-10">
                                            <select id="payee_page" name="payee_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.payee_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.payee_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.payee_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.payee_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.payee_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.payee_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('payee_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('payee_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Payor</label>
                                        <div class="col-sm-10">
                                            <select id="payor_page" name="payor_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.payor_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.payor_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.payor_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.payor_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.payor_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.payor_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('payor_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('payor_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-sm-2 col-form-label" for="name">Check History</label>
                                        <div class="col-sm-10">
                                            <select id="history_page" name="history_page" class="form-control form-select">
                                                <option value="10"
                                                    {{ config('app.history_per_page') == 10 ? 'selected' : '' }}>
                                                    10</option>
                                                <option value="20"
                                                    {{ config('app.history_per_page') == 20 ? 'selected' : '' }}>
                                                    20</option>
                                                <option value="30"
                                                    {{ config('app.history_per_page') == 30 ? 'selected' : '' }}>
                                                    30</option>
                                                <option value="40"
                                                    {{ config('app.history_per_page') == 40 ? 'selected' : '' }}>
                                                    40</option>
                                                <option value="50"
                                                    {{ config('app.history_per_page') == 50 ? 'selected' : '' }}>
                                                    50</option>
                                                <option value="100"
                                                    {{ config('app.history_per_page') == 100 ? 'selected' : '' }}>
                                                    100</option>
                                            </select>
                                            @if ($errors->has('history_page'))
                                                <span class="text-danger">
                                                    {{ $errors->first('history_page') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-5">
        @if (session('sign_success'))
            <div class="alert alert-success">
                {{ session('sign_success') }}
            </div>
        @endif
        @if (session('grid_success'))
            <div class="alert alert-success">
                {{ session('grid_success') }}
            </div>
        @endif
        @if (session('grid_error'))
            <div class="alert alert-danger">
                {{ session('grid_error') }}
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Signatures
                @if(isset($how_it_works['Signatures']))
                        <a href="{{ $how_it_works['Signatures'] }}"
                            class="ms-2 text-primary fs-6" target="_blank">
                            <i class="ti ti-help-circle"></i> Click to see how it works?
                        </a>
                    @endif
            </h5>
            <a href="{{ route('add_sign') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;">
                <i class="fa-solid fa-plus"></i> &nbsp; Add Signature
            </a>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="signTable" class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Sign</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @if (!empty($is_web_form))
        <div class="card">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-header">Web Forms
                    @if(isset($how_it_works['Web Forms']))
                        <a href="{{ $how_it_works['Web Forms'] }}"
                            class="ms-2 text-primary fs-6" target="_blank">
                            <i class="ti ti-help-circle"></i> Click to see how it works?
                        </a>
                    @endif
                </h5>
                <a href="{{ route('new_web_form') }}" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">
                    <i class="fa-solid fa-plus"></i> &nbsp; Add Web Form
                </a>
            </div>
            <div class="card-datatable table-responsive pt-0">
                <table id="webFormTable" class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>Page URL</th>
                            <th>Logo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    @endif

    <div class="card mt-5">
        <!-- Video Modal -->
        <!-- <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <video id="myVideo" width="100%" controls>
                            <source src="{{ asset('videos/check-stub-custom-itemization-fields.mp4') }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div> -->
        <form action="{{ route('save_grid') }}" method="POST">
            @csrf
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-header">
                    Check Stub Custom Itemization Fields
                    @if(isset($how_it_works['Check Stub Custom Itemization Fields']))
                        <a href="{{ $how_it_works['Check Stub Custom Itemization Fields'] }}"
                            class="ms-2 text-primary fs-6" target="_blank">
                            <i class="ti ti-help-circle"></i> Click to see how it works?
                        </a>
                    @endif
                </h5>

                <button type="submit" class="btn btn-primary mr-4"
                    style="height: 40px !important;margin-right: 25px !important;">Save</button>
            </div>
            <div class="card-datatable table-responsive pt-0">
                <table id="gridTable" class="table">
                    <thead>
                        <tr>
                            {{-- <th><input type="checkbox" id="select-all"></th> --}}
                            <th>#</th>
                            <th style="width: 100px;">Display</th>
                            <th>Required</th>
                            <th>Title</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($grids->isEmpty())
                            @for ($i = 0; $i <= 7; $i++)
                                @php
                                    $title = '';
                                    $number = '';

                                    if ($i == 0) {
                                        $title = 'SrNo';
                                        $type = 'number';
                                    } elseif ($i == 1) {
                                        $title = 'Name';
                                        $type = 'text';
                                    } elseif ($i == 2) {
                                        $title = 'Date';
                                        $type = 'date';
                                    } elseif ($i == 3) {
                                        $title = 'Amount';
                                        $type = 'number';
                                    }

                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td style="width: 100px;">
                                        <input type="hidden" name="status[{{ $i }}]" value="0">
                                        <input type="checkbox" name="status[{{ $i }}]" value="1"
                                            @if (old('status.' . $i)) checked @endif>
                                    </td>
                                    <td>
                                        <select name="required[]" class="form-select">
                                            <option value="0" @if (old('required.' . $i) == '0') selected @endif>No</option>
                                            <option value="1" @if (old('required.' . $i) == '1') selected @endif>Yes</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="name[]" type="text"
                                            value="{{ old('name.' . $i) ?? $title }}" autocomplete="off">
                                    </td>
                                    <td>
                                        <select name="type[]" class="form-select">
                                            <option value="text" @if (old('type.' . $i) == 'text' || $type == 'text') selected @endif>Text
                                            </option>
                                            <option value="number" @if (old('type.' . $i) == 'number' || $type == 'number') selected @endif>
                                                Number</option>
                                            <option value="date" @if (old('type.' . $i) == 'date' || $type == 'date') selected @endif>Date
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            @endfor
                        @else
                            @forelse($grids as $key => $grid)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td style="width: 100px;">
                                        <input type="hidden" name="grid_id" value="{{ $grid->id }}">
                                        <input type="hidden" name="grid[{{ $grid->id }}][status]" value="0">
                                        <input name="grid[{{ $grid->id }}][status]" type="checkbox" value="1"
                                            @if (@old('status.' . $grid->id) == 1 || $grid->Status == 1) checked @endif>
                                    </td>
                                    <td>
                                        <select name="grid[{{ $grid->id }}][required]" class="form-select">
                                            <option value="0" @if (@old('required.' . $grid->id) == '0' || (isset($grid->Required) && $grid->Required == 0)) selected @endif>No</option>
                                            <option value="1" @if (@old('required.' . $grid->id) == '1' || (isset($grid->Required) && $grid->Required == 1)) selected @endif>Yes</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="grid[{{ $grid->id }}][name]"
                                            type="text" value="{{ old('name.' . $grid->id) ?? $grid->Title }}"
                                            autocomplete="off">
                                    </td>
                                    <td>
                                        <select name="grid[{{ $grid->id }}][type]" class="form-select">
                                            <option @if (@old('type.' . $grid->id) == 'text' || $grid->Type == 'text') selected1 @endif value="text">Text
                                            </option>
                                            <option @if (@old('type.' . $grid->id) == 'number' || $grid->Type == 'number') selected @endif value="number">
                                                Number</option>
                                            <option @if (@old('type.' . $grid->id) == 'date' || $grid->Type == 'date') selected @endif value="date">Date
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </form>
    </div>
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {

            // Total number of checkboxes except the "select all"
            let total = $('input[type="checkbox"]').not('#select-all').length;
            // Number of checked checkboxes except the "select all"
            let checked = $('input[type="checkbox"]').not('#select-all').filter(':checked').length;

            // If all are checked, check "select all", else uncheck it
            $('#select-all').prop('checked', total === checked);

            $('#select-all').click(function() {
                $('input[type="checkbox"]').prop('checked', this.checked).trigger('change');
            });



            $('#webFormTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_web_forms') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'company_name',
                        name: 'company_name'
                    },
                    {
                        data: 'page_url',
                        name: 'page_url'
                    },
                    {
                        data: 'logo',
                        name: 'logo',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#signTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_sign') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Sign',
                        name: 'Sign',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('body').on('click', '.copy-link', function(e) {
                e.preventDefault(); // Prevent default link action

                var link = $(this).attr("data-link");
                // Get the link from data attribute
                var tempInput = $("<input>");
                $("body").append(tempInput);
                tempInput.val(link).select();
                document.execCommand("copy"); // Copy text
                tempInput.remove();
            });
        });
    </script>

@endsection
