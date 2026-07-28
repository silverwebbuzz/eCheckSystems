@extends('layouts/layoutMaster')

@section('title', 'Add Signature')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
    <style>
        #sig {
            width: 500px;
            height: 200px;
            border: 1px solid #555;
            cursor: crosshair;
        }
    </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    @vite(['resources/assets/js/form-layouts.js'])
    <script type="text/javascript">
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pad
            var canvas = document.getElementById('sig');
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }
            
            var signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });

        // Handle window resize
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        // Load existing signature if available
        var existingSignature = {!! json_encode(!empty($userSignature->Sign) ? asset('sign/' . $userSignature->Sign) : '') !!};

        if (existingSignature) {
            var img = new Image();
            img.crossOrigin = "Anonymous";
            img.src = existingSignature;

            img.onload = function() {
                // Create a temporary canvas to convert the image to dataURL
                var tempCanvas = document.createElement('canvas');
                var tempCtx = tempCanvas.getContext('2d');
                tempCanvas.width = canvas.width;
                tempCanvas.height = canvas.height;
                
                // Draw the image on temporary canvas
                tempCtx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                // Get the dataURL from temporary canvas
                var dataURL = tempCanvas.toDataURL();
                
                // Load the signature into signature pad
                signaturePad.fromDataURL(dataURL);
                
                // Save to hidden field
                $("#signature64").val(dataURL);
            };
        }

        // Update hidden field when signature changes
        signaturePad.addEventListener("endStroke", function() {
            var dataURL = signaturePad.toDataURL();
            $("#signature64").val(dataURL);
        });

        // Clear button functionality
        $('#clear').click(function(e) {
            e.preventDefault();
            signaturePad.clear();
            $("#signature64").val('');
        });
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
                        <input type="hidden" name="id" id="id" value="{{ $userSignature->Id }}" />
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $userSignature->Name }}" />
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
                                <canvas id="sig" style="border: 1px solid #555; width: 500px; height: 200px;"></canvas>
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
