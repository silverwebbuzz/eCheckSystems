@extends('layouts/layoutMaster')

@section('title', 'Send Email to Clients')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/quill/editor.scss',
        'resources/assets/vendor/libs/quill/typography.scss',
        'resources/assets/vendor/libs/quill/katex.scss'
    ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/quill/katex.js',
        'resources/assets/vendor/libs/quill/quill.js'
    ])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite([
        'resources/assets/js/form-layouts.js',
        'resources/assets/js/forms-editors.js'
    ])

    <script>
        window.onload = function () {
            console.log("✅ All resources loaded");

            // Initialize Quill editor
            const quillContainer = document.getElementById('editor');
            let quill;

            if (quillContainer) {
                quill = new Quill('#editor', {
                    theme: 'snow',
                    placeholder: 'Type something...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'header': 1 }, { 'header': 2 }, 'blockquote', 'code-block'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                // Preload Quill content
                @if(old('body'))
                    quill.root.innerHTML = `{!! addslashes(old('body')) !!}`;
                @endif
        }

            // Form submission with inline validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function (e) {
                // Remove previous error messages
                document.querySelectorAll('.js-error').forEach(el => el.remove());

                let hasError = false;

                // ----- Validate Subject -----
                const subject = document.querySelector('input[name="subject"]');
                if (!subject.value.trim()) {
                    showError(subject, "Subject is required.");
                    hasError = true;
                } else {
                    removeError(subject);
                }

                // ----- Validate Client -----
                const clients = document.querySelector('select[name="client[]"]');
                if (!clients || [...clients.selectedOptions].length === 0) {
                    showError(clients, "Please select at least one client.");
                    hasError = true;
                } else {
                    removeError(clients);
                }

                // ----- Validate Body -----
                if (quill) {
                    const bodyContent = quill.root.innerHTML.trim();
                    document.getElementById('body').value = bodyContent;

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = bodyContent;
                    if (tempDiv.textContent.trim() === '') {
                        const editorContainer = document.getElementById('editor');
                        showError(editorContainer, "Body is required.");
                        hasError = true;
                    } else {
                        removeError(document.getElementById('editor'));
                    }
                }

                if (hasError) e.preventDefault();
            });

            // ----- Function to show error -----
            function showError(element, message) {
                // Remove previous error for this element
                removeError(element);

                const error = document.createElement('span');
                error.classList.add('text-danger', 'js-error');
                error.innerText = message;
                element.parentNode.appendChild(error);
                element.classList.add('is-invalid');
            }

            // ----- Function to remove error -----
            function removeError(element) {
                const error = element.parentNode.querySelector('.js-error');
                if (error) error.remove();
                element.classList.remove('is-invalid');
            }

            // ----- Remove error on user input -----
            const subjectInput = document.querySelector('input[name="subject"]');
            subjectInput.addEventListener('input', () => removeError(subjectInput));

            if (typeof $ !== 'undefined' && $('.select2').length) {
                $('.select2').on('select2:select select2:unselect', function () {
                    removeError(this);
                });
            }

            if (quill) {
                quill.on('text-change', () => removeError(document.getElementById('editor')));
            }

            console.log("🎯 Quill editor initialized successfully.");
        };
    </script>

@endsection

@section('content')
    <div class="row">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{ route('admin.bulk-email.send') }}" method="POST">
            @csrf

            <div class="card mb-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Send Email To Clients</h5>
                    <div class="d-flex align-items-center">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </div>

                <div class="card-body">

                    {{-- Client Dropdown --}}
                    <div class="mb-4">
                        <label class="form-label">Client</label>
                        <select name="client[]" class="form-select select2" multiple>
                            <option value="all" {{ in_array('all', old('client', [])) ? 'selected' : '' }}>All Clients
                            </option>
                            @foreach($clients as $client)
                                <option value="{{ $client->UserID }}" {{ in_array($client->UserID, old('client', [])) ? 'selected' : '' }}>
                                    {{ $client->FirstName }} {{ $client->LastName }} ({{ $client->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple clients.</small>
                        @error('client')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="mb-4">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}">
                        @error('subject')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Body --}}
                    <div class="mb-4">
                        <label class="form-label">Body</label>
                        {{-- Hidden textarea to hold quill content on submit --}}
                        <textarea name="body" id="body" class="form-control d-none"></textarea>
                        {{-- Quill editor container --}}
                        <div id="editor" class="editor-container1" style="height: 300px;"></div>
                    </div>
                    
                    @error('body')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror

                </div>
            </div>
        </form>
    </div>
@endsection