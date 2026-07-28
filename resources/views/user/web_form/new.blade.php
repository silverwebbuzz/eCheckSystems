@extends('layouts/layoutMaster')

@section('title', 'Add Web Form')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
    <!-- @vite(['resources/assets/js/forms-editors.js']) -->

    <script>
        let quill;
        document.addEventListener("DOMContentLoaded", function() {
            var quillContainer = document.getElementById('editor-container');

            if (quillContainer) {
                quill = new Quill('#editor-container', {
                    theme: 'snow',
                    placeholder: 'Type Something...',
                    modules: {
                        toolbar: [
                            [{
                                font: []
                            }, {
                                size: []
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                color: []
                            }, {
                                background: []
                            }],
                            [{
                                script: 'super'
                            }, {
                                script: 'sub'
                            }],
                            [{
                                header: '1'
                            }, {
                                header: '2'
                            }, 'blockquote', 'code-block'],
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }, {
                                indent: '-1'
                            }, {
                                indent: '+1'
                            }],
                            [{
                                direction: 'rtl'
                            }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                // Save Quill content in hidden textarea before submitting form
                document.querySelector('form').onsubmit = function() {
                    document.getElementById('page_desc').value = quill.root.innerHTML;
                };

                var oldContent = document.getElementById('page_desc').value;
                if (oldContent) {
                    quill.root.innerHTML = oldContent;
                }
            } else {
                console.error(
                    "❌ Error: Quill container not found! Check if #editor-container exists in your HTML.");
            }
        });
    </script>

    <script>
        document.getElementById('logo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block'; // Show the image
                };

                reader.readAsDataURL(file); // Convert file to Base64
            } else {
                preview.src = '';
                preview.style.display = 'none'; // Hide the image if no file selected
            }
        });
    </script>

@endsection

@section('content')
    <div class="row">
        <form id="webForm" action="{{ route('store_web_form') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Basic Layout -->
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Add Web Form</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('get_web_forms') }}" class="btn btn-primary mr-4">
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
                            <label class="col-sm-2 col-form-label" for="name">LOGO</label>
                            <div class="col-sm-10">
                                <input type="file" name="logo" id="logo" class="form-control"
                                    value="{{ old('logo') }}" />
                                @if ($errors->has('logo'))
                                    <span class="text-danger">
                                        {{ $errors->first('logo') }}
                                    </span>
                                @endif

                                <div id="preview-container"
                                    style="padding: 14px; margin-top: 20px; border: 1px solid #efe6e6; width: 130px; text-align: center;">
                                    <img id="preview"
                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGh5WFH8TOIfRKxUrIgJZoDCs1yvQ4hIcppw&s"
                                        alt="Company Logo" style="width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address">Address</label>
                            <div class="col-sm-10">
                                <textarea id="address" name="address" class="form-control">{{ old('address') }}</textarea>
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
                            <label class="col-sm-2 col-form-label" for="phone_number">Phone Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="phone_number" id="phone_number" class="form-control"
                                    value="{{ old('phone_number') }}" />
                                @if ($errors->has('phone_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('phone_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="page_desc">Page Description</label>
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="editor-container">
                                        </div>
                                        <textarea name="page_desc" id="page_desc" style="display:none;">{{ old('page_desc') ?? '' }}</textarea>
                                    </div>
                                    @if ($errors->has('page_desc'))
                                        <span class="text-danger">
                                            {{ $errors->first('page_desc') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="page_desc">Service Fees</label>
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-12 col-lg-12 d-flex align-items-center gap-2">
                                                <select name="service_fees_type" id="service_fees_type"
                                                    class="form-select" onchange="toggleFeeSymbol()">
                                                    <option value="">-- Select Service Fees --</option>
                                                    <option value="percentage"
                                                        @if (old('service_fees_type') == 'percentage') selected @endif>Percentage
                                                    </option>
                                                    <option value="amount"
                                                        @if (old('service_fees_type') == 'amount') selected @endif>Fixed Amount</option>
                                                </select>

                                                <span id="dollar_icon"
                                                    class="ms-2 @if (old('service_fees_type') != 'amount') d-none @endif">$</span>

                                                <input type="text" name="service_fees" id="service_fees"
                                                    class="form-control text-center @if (!old('service_fees_type')) d-none @endif"
                                                    value="{{ old('service_fees') }}" autocomplete="off"
                                                    onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                    >

                                                <!-- Symbols -->
                                                <span id="per_icon"
                                                    class="ms-2 @if (old('service_fees_type') != 'percentage') d-none @endif">%</span>
                                            </div>
                                            @if ($errors->has('service_fees_type'))
                                                <span class="text-danger">
                                                    {{ $errors->first('service_fees_type') }}
                                                </span>
                                            @endif
                                            @if ($errors->has('service_fees'))
                                                <span class="text-danger">
                                                    {{ $errors->first('service_fees') }}
                                                </span>
                                            @endif
                                            <div class="text-danger" id="error_service_fees_type"></div>
                                            <div class="text-danger" id="error_service_fees"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        Inputmask({
            mask: "999-999-9999",
            placeholder: "", // No placeholders
            showMaskOnHover: false, // Don't show mask on hover
            showMaskOnFocus: false, // Don't show mask on focus
        }).mask("#phone_number");

        function toggleFeeSymbol() {
            const type = document.getElementById('service_fees_type').value;
            const percentIcon = document.getElementById('per_icon');
            const dollarIcon = document.getElementById('dollar_icon');
            const serviceFees = document.getElementById('service_fees');
            // Hide both by default
            percentIcon.classList.add('d-none');
            dollarIcon.classList.add('d-none');
            serviceFees.classList.add('d-none');

            if (type != '') {
                serviceFees.classList.remove('d-none');
            }else{
                serviceFees.value = '';
            }
            
            if (type === 'percentage') {
                percentIcon.classList.remove('d-none');
            } else if (type === 'amount') {
                dollarIcon.classList.remove('d-none');
            }
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/just-validate@3.3.3/dist/just-validate.production.min.js"></script>
    <script>
        const validation = new JustValidate('#webForm', {
            errorLabelCssClass: 'text-danger'
        });

        validation
            .addField('[name="name"]', [{
                rule: 'required',
                errorMessage: 'Please enter name'
            }])
            .addField('[name="logo"]', [{
                rule: 'minFilesCount',
                value: 1,
                errorMessage: 'Please upload a logo'
            }])
            .addField('[name="address"]', [{
                rule: 'required',
                errorMessage: 'Please enter address'
            }])
            .addField('[name="city"]', [{
                rule: 'required',
                errorMessage: 'Please enter city'
            }])
            .addField('[name="state"]', [{
                rule: 'required',
                errorMessage: 'Please enter state'
            }])
            .addField('[name="zip"]', [{
                rule: 'required',
                errorMessage: 'Please enter zip'
            }])
            .addField('[name="phone_number"]', [{
                rule: 'customRegexp',
                value: /^\d{3}-\d{3}-\d{4}$/,
                errorMessage: 'The phone number field format is invalid.'
            }])
            .addField('[name="service_fees_type"]', [{
                rule: 'customRegexp',
                value: /^(percentage|amount)?$/,
                errorMessage: 'Invalid service fees type'
            }], {
                // Hide the default error for just this field
                errorLabelStyle: {
                    display: 'none', // Prevent default error message from being displayed
                }
            })
            .addField('[name="service_fees"]', [{
                    validator: (value, fields) => {
                        const type = document.querySelector('[name="service_fees_type"]').value;
                        if (type === 'percentage' || type === 'amount') {
                            return value.trim() !== ''; // must be filled
                        }
                        return true; // allow empty if type is not set
                    },
                    errorMessage: 'Please enter service fees'
                },
                {
                    validator: (value, fields) => {
                        const type = document.querySelector('[name="service_fees_type"]').value;
                        if (type === 'percentage' || type === 'amount') {
                            // First validation: Check if the value is a number
                            return !isNaN(value);
                        }
                        return true; // Skip if not 'percentage' or 'amount'
                    },
                    errorMessage: 'Service fee must be a number',
                },
                {
                    validator: (value, fields) => {
                        const type = document.querySelector('[name="service_fees_type"]').value;
                        if ((type === 'percentage' || type === 'amount') && !isNaN(value)) {
                            // Second validation: Check if the value is greater than 0
                            return parseFloat(value) > 0;
                        }
                        return true; // Skip if the first check fails or type is not 'percentage' or 'amount'
                    },
                    errorMessage: 'Service fee must be greater than 0',
                }
            ], {
                // Hide the default error for just this field
                errorLabelStyle: {
                    display: 'none', // Prevent default error message from being displayed
                }
            })
            .onFail((fields) => {
                // Get the specific field object
                const error_service_fees_type = fields['[name="service_fees_type"]'];
                const error_service_fees = fields['[name="service_fees"]'];

                if (error_service_fees_type) {
                    const error_service_fees_type_container = document.querySelector('#error_service_fees_type');
                    if (error_service_fees_type_container) {
                        error_service_fees_type_container.textContent = error_service_fees_type.errorMessage;
                    }
                }
                if (error_service_fees) {
                    const error_service_fees_container = document.querySelector('#error_service_fees');
                    if (error_service_fees_container) {
                        error_service_fees_container.textContent = error_service_fees.errorMessage;
                    }
                }

                Object.keys(fields).forEach((fieldName) => {
                    const obj = fields[fieldName];
                    if (obj && obj.isValid === true) {

                        const match = fieldName.match(/\[name=['"]?([^'"\]]+)['"]?\]/);
                        const error_element = match ? match[1] : null;

                        if (error_element) {
                            const fieldContainer = document.querySelector(`#error_${error_element}`);
                            if (fieldContainer) {
                                fieldContainer.textContent = ''; // Remove the error message
                            }
                        }
                    }

                });
            })
            .onSuccess((event) => {
                document.getElementById('page_desc').value = quill.root.innerHTML;
                event.target.submit();
            });
    </script>

@endsection
