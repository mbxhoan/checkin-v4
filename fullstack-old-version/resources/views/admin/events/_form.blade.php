@php
    $isSysAdmin      = auth()->user()->isSysAdmin();
    $hasCompanyFixed = !empty($company);
    $isNew           = $model->isNew();
    $isReadOnlyCode  = !$isNew && !$isSysAdmin;

    $fromDateDefault = optional($model->from_date)->format('Y-m-d') ?? now()->format('Y-m-d');
    $toDateDefault   = optional($model->to_date)->format('Y-m-d')   ?? now()->format('Y-m-d');

    $provinceOptions = $provinceArray ?? ($proviceArray ?? []);
    $step2Fields     = ['province_id','type_id','from_date','to_date'];

    // Nếu có lỗi thuộc step 2 thì tự động mở step 2, còn không thì mặc định step 1
    $openStep = old('current_step')
        ?? (collect($step2Fields)->contains(fn($f) => $errors->has($f)) ? 2 : 1);
@endphp

{{-- Hidden để nhớ step hiện tại (sẽ set bằng JS) --}}
<input type="hidden" name="current_step" id="current_step" value="{{ $openStep }}">

{{-- Header tuyến trình --}}
<x-stepper :steps="[
    ['id'=>1,'label'=>__('events.create.basic_information.title')],
    ['id'=>2,'label'=>__('events.create.time_location.title')],
]" :current="$openStep"  />
<x-card>
    {{-- ========== STEP 1: Tên & Mô tả ========== --}}
    <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
        <div class="row text-xs">
            {{-- Company --}}
            @if ($hasCompanyFixed)
                @include('components.form-groups.input-group', [
                    'id'         => "company_id",
                    'fieldName'  => "company_id",
                    'value'      => $company->id,
                    'type'       => "hidden",
                    'formClass'  => 'd-none',
                ])
            @else
                @sys_admin
                    <div class="mb-3 col-md-12">
                        @include('components.select', [
                            'label' => $model->company_id
                                ? '<a href="'.route('admin.companys.edit', $model->company).'" target="_blank">'
                                    . __('events.create.basic_information.company') .
                                    ' <i class="fa-solid fa-edit fa-xs"></i></a>'
                                : __('events.create.basic_information.company'),

                            'fieldName'   => 'company_id',
                            'id'          => 'company_id',
                            'options'     => $companyArray,
                            'selected'    => old('company_id', request()->company_id ?? $model->company_id),
                            'placeholder' => __('events.index.select_company'),
                            'required'    => true,
                        ])
                    </div>
                @endsys_admin
            @endif

            {{-- Mã sự kiện (cho phép hiện khi sysadmin hoặc khi đang chỉnh sửa) --}}
            @if ($isSysAdmin || !$isNew)
                @include('components.form-groups.input-group', [
                    'id'          => "code",
                    'model'       => $model,
                    'type'        => "text",
                    'label'       => __('events.create.basic_information.event_code'),
                    'formClass'   => 'mb-3 col-md-12',
                    'required'    => true,
                    'readonly'    => $isReadOnlyCode,
                    'placeholder' => "su-kien-01",
                    'value'       => old('code', $model->code),
                ])
            @endif

            {{-- Tên sự kiện --}}
            @include('components.form-groups.input-group', [
                'id'                => "name",
                'model'             => $model,
                'type'              => "text",
                'label'             => __('events.create.basic_information.event_name'),
                'placeholder'       => __('events.create.basic_information.event_name_placeholder').now()->format('Y'),
                'required'          => true,
                'formClass'         => 'mb-3 col-md-12',
            ])
        </div>

        <div class="row text-xs">
            {{-- Mô tả --}}
            @include('components.form-groups.input-group', [
                'id'          => "description",
                'model'       => $model,
                'type'        => "textarea",
                'label'       => __('events.create.basic_information.event_description'),
                'formClass'   => 'mb-3 col-md-12',
                'placeholder' => __('events.create.basic_information.event_description_placeholder'),
                'rows'        => 3,
                'value'       => old('description', $model->description),
            ])
        </div>
        <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-primary" data-next>
                {{ __('common.next')}} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                </button>
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "status",
            'fieldName'         => "status",
            'value'             => 'ACTIVE',
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
    </div>

    {{-- ========== STEP 2: Ngày, Tỉnh/Thành, Loại hình ========== --}}
    <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
        <div class="row text-xs">
            <div class="row">
                {{-- Tỉnh/Thành phố --}}
                <div class="mb-3 col-md-6">
                    @include('components.select', [
                        'label'       => __('events.create.time_location.province'),
                        'fieldName'   => 'province_id',
                        'id'          => 'province_id',
                        'options'     => $provinceOptions,
                        'selected'    => old('province_id', $model->province_id),
                        'placeholder' => __('events.create.time_location.province_placeholder'),
                        'required'    => true,
                    ])
                </div>
                {{-- Loại hình sự kiện --}}
                <div class="mb-3 col-md-6">
                    @include('components.select', [
                        'label'       =>  __('events.create.time_location.event_type'),
                        'fieldName'   => 'type_id',
                        'id'          => 'type_id',
                        'options'     => $eventTypeArray,
                        'selected'    => old('type_id', $model->type_id),
                        'placeholder' =>  __('events.create.time_location.event_type_placeholder'),
                        'required'    => true,
                    ])
                </div>
            </div>
            <div class="row">
                {{-- Ngày bắt đầu --}}
                <div class="mb-3 col-md-6">
                    @include('components.form-groups.input-group', [
                        'id'        => "from_date",
                        'model'     => $model,
                        'type'      => "date",
                        'value'     => old('from_date', $fromDateDefault),
                        'label'     =>  __('events.create.time_location.start_date'),
                        'required'  => false,
                    ])
                </div>
                <div class="mb-3 col-md-6">
                    @include('components.form-groups.input-group', [
                        'id'        => "to_date",
                        'model'     => $model,
                        'type'      => "date",
                        'value'     => old('to_date', $toDateDefault),
                        'label'     =>  __('events.create.time_location.end_date'),
                        'required'  => false,
                    ])
                </div>
            </div>
            <div class="d-flex justify-content-start align-items-center gap-2 mb-3">
                {{-- Nút quay lại --}}
                <button type="button" class="btn btn-light" data-prev>
                    <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('common.back')}}
                </button>

                {{-- Chỉ render khi trang này có form-action --}}
                @hasSection('form-action')
                    {{-- Ý định submit: step 1 => save_and_next, step 2 (cuối) => save_finish --}}
                    <input type="hidden" name="intent" id="intent"
                        value="{{ $openStep == 1 ? 'save_and_next' : 'save_finish' }}">

                    <button type="submit"
                            class="btn btn-primary {{ $openStep == 2 ? '' : 'd-none' }}"
                            id="btn-submit">
                    <x-icon name="save" />
                    <span>{{ __('common.save')}}</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-card>