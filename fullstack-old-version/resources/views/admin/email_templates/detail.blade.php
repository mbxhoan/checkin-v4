@extends('admin.layouts.templates.page-form', [
    'formId'        => "form-edit-template",
    'showBtns'      => false,
])

@php
    $navs = [
        'edit'      => [
            'title' => 'Chỉnh sửa',
            'icon'  => '<i class="fa-solid fa-edit"></i>',
        ],
        'preview'   => [
            'title' => 'Xem trước',
            'icon'  => '<i class="fa-solid fa-eye"></i>',
        ],
    ];
@endphp

@section('form-action', route('admin.email_templates.update-postmark-template', $object['TemplateId']))
@section('form-back', route('admin.email_templates.index'))
@section('title', $object['Name'])

@section('buttons')
    <a href="{{ route('admin.email_templates.create') }}" class="btn btn-sm btn-primary">
        <x-icon name="plus-square" prefix="fa-regular"/>
        Tạo mới
    </a>
    @sys_admin
        <a href="{{ route('admin.email_templates.sync-postmark-template', $object['TemplateId']) }}"
            class="btn btn-light border ms-2"
            title="Đồng bộ với Postmark">
            <x-icon name="rotate"/>
        </a>
    @endsys_admin
@endsection

@section('primary-content')
    <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
        @foreach ($navs as $key => $attr)
            <li class="nav-item col px-0" role="presentation">
                <button
                    class="nav-link rounded text-center text-decoration-none text-dark h-100 w-100 {{ $key == 'edit' ? 'active' : '' }}"
                    id="{{ $key }}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#{{ $key }}"
                    type="button"
                    role="tab"
                >
                    {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                </button>
            </li>
        @endforeach
    </ul>
    <!-- Tab Content -->
    <div class="tab-content mt-2" id="settingsTabsContent">
        <div class="tab-pane fade show active" id="edit" role="tabpanel">
            @include('components.form-groups.input-group', [
                'id'                => "name",
                'model'             => $object['Name'],
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
            @include('components.form-groups.input-group', [
                'id'                => "template_id",
                'model'             => $object['TemplateId'],
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
            <div class="row mt-2">
                @include('components.form-groups.input-group', [
                    'id'                => "subject",
                    'model'             => $object['Subject'] ?? null,
                    'type'              => "text",
                    'formClass'         => 'mb-3 col-md-4',
                    'placeholder'       => "Tiêu đề",
                    'label'             => "Tiêu đề",
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
                            >{!! $object['HtmlBody'] !!}</textarea>
                        </x-card>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade show" id="preview" role="tabpanel">
            <button type="button" class="btn btn-sm btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Gửi test
            </button>
            <div class="card border rounded" style="width: 100%; height: 77vh;">
                <div class="card-body p-0" style="height: 100%;">
                    <iframe srcdoc="{!! htmlspecialchars($object['FullHtmlBody']) !!}"
                        style="width: 100%; height: 100%; border: none;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customs')
    @include('admin.email_templates._modal-send-test', [
        'placeholders'  => $object['placeholders'],
        'templateId'    => (int)$object['TemplateId'],
    ])
@endsection

@section('custom-buttons')
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
    {{-- <script src="https://cdn.tiny.cloud/1/x6ycqq54irgc2638wc0pwmsbj1abzol3eryoncmpjstoikdz/tinymce/7/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script> --}}
    {{-- <script>
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

@endpush
