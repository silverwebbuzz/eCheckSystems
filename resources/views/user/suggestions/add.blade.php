@extends('layouts/layoutMaster')

@section('title', 'Send A Suggestion')

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

    <script>
        window.onload = function() {

            // Initialize the first editor
            const quillContainer1 = document.getElementById('editor-container1');

            let quill1;

            if (quillContainer1) {
                quill1 = new Quill('#editor-container1', {
                    theme: 'snow',
                    placeholder: 'Type something...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                'header': 1
                            }, {
                                'header': 2
                            }, 'blockquote', 'code-block'],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quill1.root.addEventListener('paste', function(e) {
                    const clipboardData = e.clipboardData || window.clipboardData;
                    if (!clipboardData) return;

                    // Check if any pasted item is an image
                    for (let i = 0; i < clipboardData.items.length; i++) {
                        const item = clipboardData.items[i];
                        if (item.type.indexOf('image') !== -1) {
                            // Prevent image paste
                            e.preventDefault();
                            alert('Image is not allowed.');
                            return;
                        }
                    }
                });
            }

            // Form submission handling
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                if (quill1) {
                    document.getElementById('description').value = quill1.root.innerHTML;
                }
            });

            // // MutationObserver to replace deprecated DOM events
            // const observer = new MutationObserver(() => {
            //     console.log("DOM changed");
            // });
            // observer.observe(document.body, {
            //     childList: true,
            //     subtree: true
            // });

            // console.log("🎯 Quill editors initialized successfully.");
        };

        $(document).ready(function() {
            $('#section').select2({
                tags: true, // Enables custom options
                placeholder: "Specific Section or New Idea",
                allowClear: true
            });
        });
    </script>
@endsection

@section('content')
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="alert alert-danger">
                {{ session('info') }}
            </div>
        @endif
        <div id="alert-message">

        </div>
        <form action="{{ route('user.suggestion.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Send A Suggestion</h5>
                <div>
                    <button type="submit" id="add-suggestion" class="btn btn-primary" style="height: 40px !important;">
                         Send
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="col-8 col-md-4">
                    <label class="col-form-label" for="section">Specific Section or New Idea</label>
                    <select type="text" class="select2 form-control" name="section" id="section">
                        <option value=""></option>
                        <option value="Receive Payment" @if (old('section') == 'Receive Payment') selected @endif>Receive Payment
                        </option>
                        <option value="Send Payment" @if (old('section') == 'Send Payment') selected @endif>Send Payment</option>
                        <option value="Manage Payees (Pay To)" @if (old('section') == 'Manage Payees (Pay To)') selected @endif>Manage
                            Payees (Pay To)</option>
                        <option value="Manage Payors (Pay From)" @if (old('section') == 'Manage Payors (Pay From)') selected @endif>Manage
                            Payors (Pay From)</option>
                        <option value="History" @if (old('section') == 'History') selected @endif>History</option>
                        <option value="Billing & Plan" @if (old('section') == 'Billing & Plan') selected @endif>Billing & Plan
                        </option>
                        <option value="Other Or New Suggestion" @if (old('section') == 'Other Or New Suggestion') selected @endif>Other Or New Suggestion</option>
                    </select>
                    @if ($errors->has('section'))
                        <span class="text-danger">
                            {{ $errors->first('section') }}
                        </span>
                    @endif
                </div>
                <div class="col-12 mt-5">
                    <label class="col-form-label" for="description">Please give us as many details as possible</label>
                    <div id="editor-container1">{!! old('description') !!}</div>
                    <textarea type="text" class="form-control" name="description" id="description" style="display: none"></textarea>
                    @if ($errors->has('description'))
                        <span class="text-danger">
                            {{ $errors->first('description') }}
                        </span>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection
