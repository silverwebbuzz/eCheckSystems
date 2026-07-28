@extends('layouts/layoutMaster')

@section('title', 'Email Templates')

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
            console.log("✅ All resources loaded");

            // Initialize the first editor
            const quillContainer1 = document.getElementById('editor-container1');
            const quillContainer2 = document.getElementById('editor-container2');

            let quill1, quill2;

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
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                // Preload content into the first editor
                const existingContent1 = `{!! addslashes($emailTemplates->body1) !!}`;
                quill1.root.innerHTML = existingContent1;
            }

            if (quillContainer2) {
                quill2 = new Quill('#editor-container2', {
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
                            ['link', 'image', 'video'],
                            ['clean']
                        ]
                    }
                });

                // Preload content into the second editor
                const existingContent2 = `{!! addslashes($emailTemplates->body2) !!}`;
                quill2.root.innerHTML = existingContent2;
            }

            // Form submission handling
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                if (quill1) {
                    document.getElementById('body1').value = quill1.root.innerHTML;
                }
                if (quill2) {
                    document.getElementById('body2').value = quill2.root.innerHTML;
                }
            });

            // MutationObserver to replace deprecated DOM events
            const observer = new MutationObserver(() => {
                console.log("DOM changed");
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            console.log("🎯 Quill editors initialized successfully.");
        };
    </script>
@endsection

@section('content')
    <div class="row">
        <form action="{{ route('admin.email-template-update', ['id' => $emailTemplates->id]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="col-xxl">
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Edit Email Templates</h5>
                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                            &nbsp;&nbsp;
                            <a href="{{ route('admin.email-template') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $emailTemplates->name }}" />
                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="subject">Subject</label>
                            <div class="col-sm-10">
                                <input type="text" name="subject" id="subject" class="form-control"
                                    value="{{ $emailTemplates->subject }}" />
                                @if ($errors->has('subject'))
                                    <span class="text-danger">{{ $errors->first('subject') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="head">Header</label>
                            <div class="col-sm-10">
                                <input type="text" name="head" id="head" class="form-control"
                                    value="{{ $emailTemplates->head }}" />
                                @if ($errors->has('head'))
                                    <span class="text-danger">{{ $errors->first('head') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="content">Content</label>
                            <div class="col-sm-10">
                                <input type="text" name="content" id="content" class="form-control"
                                    value="{{ $emailTemplates->content }}" />
                                @if ($errors->has('content'))
                                    <span class="text-danger">{{ $errors->first('content') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="body1">Body1</label>
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="editor-container1"></div>
                                        <textarea name="body1" id="body1" style="display:none;">{!! $emailTemplates->body1 !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="body2">Body2</label>
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div id="editor-container2"></div>
                                        <textarea name="body2" id="body2" style="display:none;">{!! $emailTemplates->body2 !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </form>
    </div>
@endsection
