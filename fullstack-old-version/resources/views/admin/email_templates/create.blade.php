@extends('admin.layouts.templates.page-form', [
    'formId'        => "form-edit-template",
    'showBtns'      => false,
])

@section('form-action', route('admin.email_templates.store'))
@section('form-back', route('admin.email_templates.index'))
@section('title', __('email.create.title'))

@section('primary-content')
    <div class="row">
        <div class="col-12">
            <div class="row mt-2">
                @include('components.form-groups.input-group', [
                    'placeholder'       => __('email.create.name_placeholder'),
                    'label'             => __('email.create.name_label'),
                    'id'                => "name",
                    'model'             => null,
                    'type'              => "text",
                    'formClass'         => 'mb-3 col-md-3',
                ])
                @include('components.form-groups.input-group', [
                    'id'                => "subject",
                    'model'             => null,
                    'type'              => "text",
                    'formClass'         => 'mb-3 col-md-3',
                    'placeholder'       => __('email.create.subject_placeholder'),
                    'label'             => __('email.create.subject_label'),
                ])
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-2 justify-content-start">
                @include('admin.email_templates.components.toolbar', [
                    'templateEvents' => $templateEvents ?? [],
                    'templateFieldsByEvent' => $templateFieldsByEvent ?? [],
                    'templateDefaultEventId' => $templateDefaultEventId ?? null,
                ])
            </div>
            <div id="editor-wrapper" style="position: relative;">
                <!-- Spinner -->
                <div id="editor-loader"
                    style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="row mb-2 justify-content-center">
                    <div class="col-md-7">
                        <x-card>
                            <textarea
                                name="html_body"
                                id="html_body"
                                cols="50"
                                rows="30"
                                required
                                @class(['form-control trumbowyg-form', 'is-invalid' => $errors->has('html_body')])
                            >{{ old('html_body', $value ?? ($model->html_body ?? null)) }}</textarea>
                        </x-card>
                    </div>
                </div>
            </div>
            {{-- <div class="row mb-2 justify-content-center">
                <div class="col-md-7">
                    <textarea id="html" name="html_body">{{ old('html_body', $value ?? ($model->html_body ?? null)) }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="main-container col-md-12">
                    <div
                        class="editor-container editor-container_classic-editor editor-container_include-style editor-container_include-fullscreen"
                        id="editor-container"
                    >
                        <div class="editor-container__editor">
                            <div id="editor">

                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('custom-buttons')
    {{-- <div class="footer-fixed d-flex align-items-center justify-content-center">
        <a href="{{ route('admin.campaigns.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </a>
        <button id="btn-submit-email-template" type="submit" class="btn btn-sm btn-outline-primary">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button>
    </div> --}}
    <div class="footer-fixed d-flex align-items-center justify-content-center">
        <button type="button"
                onclick="window.location='{{ route('admin.email_templates.index') }}'"
                class="btn btn-outline-secondary me-2 d-inline-flex align-items-center">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </button>

        <button type="submit"
                class="btn btn-outline-primary d-inline-flex align-items-center">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button>
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/email_templates/detail.js',
    ])
    {{-- <script src="https://cdn.tiny.cloud/1/x6ycqq54irgc2638wc0pwmsbj1abzol3eryoncmpjstoikdz/tinymce/7/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script>
        tinymce.init({
            selector: 'textarea#html',
            // referrer_policy: 'origin',
            // content_css_cors: true,
            plugins: 'code anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | code',
        });
    </script> --}}
@endpush

@push('admin_css')
    <style>
        .template-preview * {
            all: revert;
        }

        /* Optional: re-apply base styles */
        .template-preview body,
        .template-preview p,
        .template-preview h1 {
            font-family: inherit;
            color: inherit;
        }
    </style>
@endpush
