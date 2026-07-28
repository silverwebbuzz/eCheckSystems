@extends('layouts/layoutMaster')

@section('title', 'Add Signature')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
    <style>
        .kbw-signature {
            width: 500px;
            height: 200px;
            border: none !important;
        }

        #sig canvas {
            width: 500px;
            height: 200px;
            border: 1px solid #555;
        }
    </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
    <script type="text/javascript">
        var sig = $('#sig').signature({
            syncField: '#signature64',
            syncFormat: 'PNG'
        });

        var existingSignature = {!! json_encode(!empty($check->DigitalSignature) ? asset('sign/' . $check->DigitalSignature) : '') !!};

        if (existingSignature) {
            var img = new Image();
            img.crossOrigin = "Anonymous"; // Prevent CORS issues when converting to Base64
            img.src = existingSignature;

            img.onload = function() {
                var canvas = $('#sig canvas')[0];
                var ctx = canvas.getContext("2d");

                // Draw existing signature on canvas
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                // Convert canvas content to Base64
                var base64Signature = canvas.toDataURL("image/png");

                // Save Base64 signature to hidden field
                $("#signature64").val(base64Signature);
            };
        }


        $('#clear').click(function(e) {

            e.preventDefault();

            sig.signature('clear');

            $("#signature64").val('');

        });
    </script>
@endsection

@section('content')
    <div class="row">
        <form action="{{ route('store_sign') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Add Signature</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('get_web_forms') }}" class="btn btn-primary mr-4">Back</a>
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
                            <label class="col-sm-2 col-form-label">Signature</label>
                            <div class="col-sm-10">
                                <div id="sig"></div>
                                <br />
                                <button id="clear" class="btn btn-sm btn-danger">Clear</button>
                                <input type="hidden" name="signature" id="signature64">
                                <br>
                                @if ($errors->has('signature'))
                                    <span class="text-danger">
                                        {{ $errors->first('signature') }}
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
