<div class="row">
    <div class="col-md-2">
        <a href="{{ route('admin.language_defines.generate-lang', $event) }}" class="text-sm">
            <x-icon name="rotate"/>
        </a>
    </div>
    <div class="col-md-10 mb-2 text-end">
        <div class="d-flex align-items-center justify-content-end">
            @include('components.form-groups.input-group', [
                'fieldName'     => "show_language_selection",
                'id'            => "show_language_selection",
                'model'         => $model,
                'type'          => "switch",
                'value'         => $model->show_language_selection ?? 0,
                'formClass'     => '',
                'inputClass'    => 'form-check-input text-sm toggle-language-selection',
                'changeUrl'     => route('admin.landing_pages.update-show-language-selection', $model),
            ])
            @include('components.selects.languages.lang', [
                'languages'     => $model->getLanguages(),
                'event'         => $event,
                'edit'          => true,
            ])
        </div>
    </div>
</div>

<div class="text-xs">
    <div class="d-flex justify-content-between">
        <div class="fw-bold">
            Xem trước:
        </div>
        <div class="">
            <a id=""
                href=""
                class="text-sm"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions"
                aria-controls="offcanvasWithBothOptions"
            >
                <x-icon name="edit" />
                Chiều rộng form
            </a>
            <div class="offcanvas offcanvas-start"
                data-bs-scroll="true"
                tabindex="-1"
                id="offcanvasWithBothOptions"
                aria-labelledby="offcanvasWithBothOptionsLabel"
            >
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Tuỳ chỉnh form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            @include('components.select', [
                                'label'         => "Vị trí form",
                                'id'            => 'align',
                                'fieldName'     => 'align',
                                'options'       => $event->getAligns(),
                                'selected'      => $model->align,
                            ])
                        </div>
                        <div class="mb-3 col-md-6">
                            @include('components.select', [
                                'label'         => "Chiều rộng form",
                                'id'            => 'form_width',
                                'fieldName'     => 'form_width',
                                'options'       => $model->getFormWidths(),
                                'selected'      => $model->form_width,
                            ])
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary btn-sm">
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ratio ratio-16x9 border rounded mb-3" style="background-color: #ffffff;">
        <div class="container-fluid">
            @switch($model->template_id)
                @case(1)
                    <div class="row h-100 justify-content-center" style="
                        background-color: #dddddd78; /* adjust to match image tone */
                        background-image: url('{{ !empty($model->bg_desktop) ? $model->bg_desktop->getUrl() : null }}');
                        background-repeat: no-repeat;
                        background-position: center center;
                        background-attachment: fixed;
                        background-size: cover;
                    ">
                        <div class="
                            col-lg-{{ $model->form_width ?? 6 }}
                            col-md-{{ !empty($model->form_width) ? ((int)$model->form_width - 1) : 5 }}
                            col-sm-12 col-12
                            mt-2
                            h-100
                        ">
                            <div class="bg-white border rounded w-100" style="max-width: 350px; height: 95%; overflow-y: scroll;">
                                @if (!empty($model->banner_id))
                                    <img src="{{ $model->banner->getUrl() }}" class="rounded-top mb-2" alt="Banner" width="100%">
                                @endif
                                <div class="p-3">
                                    @include('web.landing_pages.components.subject', [
                                        'divClass'      => 'font-size-10',
                                        'id'            => 'register_subject',
                                        'text'          => $model->getTranslate('register_subject', $languageCode)->translate ?? 'Tiêu đề',
                                        'edit'          => true,
                                        'language'      => $model->getLanguageByCode($languageCode),
                                        'eventId'       => $event->id,
                                        'model'         => $model,
                                    ])
                                    @if (true)
                                        @include('web.landing_pages.components.subject', [
                                            'divClass'      => 'font-size-10',
                                            'text'          => $model->getTranslate('sub_register_subject', $languageCode)->translate ?? "Tiêu đề phụ",
                                            'id'            => 'sub_register_subject',
                                            'edit'          => true,
                                            'language'      => $model->getLanguageByCode($languageCode),
                                            'eventId'       => $event->id,
                                            'model'         => $model,
                                        ])
                                    @endif
                                    <div class="font-size-10">
                                        {{-- form here --}}
                                        @include('admin.landing_pages._web-form', [
                                            'model'                 => $model,
                                            'cfTemplate'            => $cfTemplate,
                                            'customFieldTemplates'  => $event->getCustomFieldTemplates(true, true),
                                        ])
                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-12 text-center">
                                            @include('web.landing_pages.components.submit', [
                                                'btnId'         => 'btn_submit-text',
                                                'btnText'       => $model->getTranslate('btn_submit', $languageCode)->translate ?? 'Đăng ký',
                                                'btnClass'      => 'btn btn-primary btn-xs',
                                                'id'            => 'btn_submit',
                                                'edit'          => $languageCode ? true : false,
                                                'model'         => $model,
                                                'language'      => $model->getLanguageByCode($languageCode),
                                                'eventId'       => $event->id,
                                            ])
                                        </div>
                                    </div>
                                    @include('web.landing_pages.components.html', [
                                        'divClass'      => 'text-sm',
                                        'id'            => 'html_behide_submit_btn',
                                        'text'          => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? 'Nội dung',
                                        'content'       => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? '',
                                        'edit'          => $languageCode ? true : false,
                                        'language'      => $model->getLanguageByCode($languageCode),
                                        'eventId'       => $event->id,
                                        'model'         => $model,
                                    ])
                                    @include('web.landing_pages.components.credit', [
                                        'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
                                        'creditName'    => $model->contact_name,
                                        'creditPhone'   => $model->contact_phone,
                                        'creditEmail'   => $model->contact_email,
                                        'creditAddress' => $model->contact_address,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
                @case(2)
                    <div class="row h-100">
                        <div class="
                            col-lg-{{ 12 - ($model->form_width ?? 6) }}
                            col-md-{{ 12 - (!empty($model->form_width) ? ((int)$model->form_width - 1) : 5) }}
                            col-sm-0
                            col-0
                            d-lg-block
                            d-md-block
                            d-none
                            d-none
                            d-flex align-items-center justify-content-center">
                            <!-- Background content goes here -->
                            <img src="{{ !empty($model->bg_desktop) ? $model->bg_desktop->getUrl() : null }}" alt="Background" class="px-0 mx-0"
                                style="
                                    object-fit: cover;
                                    height: 100%;
                                    width: 100%;
                                    max-height: 100vh;
                                    display: block;
                                "
                            >
                        </div>
                        <div class="
                            {{ $form_class ?? null }}
                            col-lg-{{ $model->form_width ?? 6 }}
                            col-md-{{ !empty($model->form_width) ? ((int)$model->form_width - 1) : 5 }}
                            col-sm-12 col-12
                            h-100">
                            <div class="bg-light border rounded w-100" style="height: 100%; overflow-y: scroll;">
                                @if (!empty($model->banner_id))
                                    <img src="{{ $model->banner->getUrl() }}" class="rounded-top mb-2" alt="Banner" width="100%">
                                @endif
                                <div class="p-3">
                                    @include('web.landing_pages.components.subject', [
                                        'divClass'      => 'font-size-10',
                                        'id'            => 'register_subject',
                                        'text'          => $model->getTranslate('register_subject', $languageCode)->translate ?? 'Tiêu đề',
                                        'edit'          => true,
                                        'language'      => $model->getLanguageByCode($languageCode),
                                        'eventId'       => $event->id,
                                        'model'         => $model,
                                    ])
                                    @if (true)
                                        @include('web.landing_pages.components.subject', [
                                            'divClass'      => 'font-size-10',
                                            'text'          => $model->getTranslate('sub_register_subject', $languageCode)->translate ?? "Tiêu đề phụ",
                                            'id'            => 'sub_register_subject',
                                            'edit'          => true,
                                            'language'      => $model->getLanguageByCode($languageCode),
                                            'eventId'       => $event->id,
                                            'model'         => $model,
                                        ])
                                    @endif
                                    <div class="font-size-10">
                                        {{-- form here --}}
                                        @include('admin.landing_pages._web-form', [
                                            'model'                 => $model,
                                            'cfTemplate'            => $cfTemplate,
                                            'customFieldTemplates'  => $event->getCustomFieldTemplates(true, true),
                                        ])
                                        @include('web.landing_pages.components.credit', [
                                            'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
                                            'creditName'    => $model->contact_name,
                                            'creditPhone'   => $model->contact_phone,
                                            'creditEmail'   => $model->contact_email,
                                            'creditAddress' => $model->contact_address,
                                        ])
                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-12 text-center">
                                            @include('web.landing_pages.components.submit', [
                                                'btnId'         => 'btn_submit-text',
                                                'btnText'       => $model->getTranslate('btn_submit', $languageCode)->translate ?? 'Đăng ký',
                                                'btnClass'      => 'btn btn-primary btn-xs',
                                                'id'            => 'btn_submit',
                                                'edit'          => $languageCode ? true : false,
                                                'model'         => $model,
                                                'language'      => $model->getLanguageByCode($languageCode),
                                                'eventId'       => $event->id,
                                            ])
                                        </div>
                                    </div>
                                    @include('web.landing_pages.components.html', [
                                        'divClass'      => 'text-sm',
                                        'id'            => 'html_behide_submit_btn',
                                        'text'          => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? 'Nội dung',
                                        'content'       => $model->getTranslate('html_behide_submit_btn', $languageCode)->translate ?? '',
                                        'edit'          => $languageCode ? true : false,
                                        'language'      => $model->getLanguageByCode($languageCode),
                                        'eventId'       => $event->id,
                                        'model'         => $model,
                                    ])
                                    @include('web.landing_pages.components.credit', [
                                        'logo'          => $event->logoUrl ? $event->logoUrl->getUrl() : null,
                                        'creditName'    => $model->contact_name,
                                        'creditPhone'   => $model->contact_phone,
                                        'creditEmail'   => $model->contact_email,
                                        'creditAddress' => $model->contact_address,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
                @default
            @endswitch
        </div>
    </div>
</div>

@push('admin_css')
    {!! $model->generateCssFromCustoms() !!}
@endpush

@push('admin_js')
    <script src="https://cdn.tiny.cloud/1/x6ycqq54irgc2638wc0pwmsbj1abzol3eryoncmpjstoikdz/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#html',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
@endpush
