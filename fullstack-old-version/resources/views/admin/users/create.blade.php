@extends('admin.layouts.templates.page-form', [
    'showBtns'  => false
])

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.users.store'))
@section('title', __('users.create.page_heading'))

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12 mx-auto">
            <x-stepper :steps="[
                [
                    'id' => 1,
                    'label' => __('users.create.step_role'),
                ],
                [
                    'id' => 2,
                    'label' => __('users.create.step_info'),
                ],
            ]" :current="$openStep" />

            <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
            <input type="hidden" id="intent" name="intent" value="">

            <x-card>
                {{-- STEP 1 --}}
                <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                    <div class="form-group mb-2 row justify-content-center">
                        @foreach ($roles as $role)
                            <div class="col-4 text-center">
                                <label class="border rounded shadow text-center py-3 px-2">
                                    <div class="mb-2">
                                        @switch($role->name)
                                            @case('scanner')
                                                <i class="fa-solid fa-mobile-screen-button fa-2xl"></i>
                                                @break
                                            @default
                                                <i class="fa-solid fa-user fa-2xl"></i>
                                        @endswitch
                                    </div>
                                    @if (Lang::has('roles.' . $role->name))
                                        {!! __('roles.' . $role->name) !!}
                                    @else
                                        {{ ucfirst($role->name) }}
                                    @endif
                                    <div class="mt-2">
                                        {{-- <input type="checkbox" name="roles[{{ $role->id }}]" value="{{ $role->id }}"
                                            @checked($user->hasRole($role->name))> --}}

                                        <input type="radio" name="role_id" value="{{ $role->id }}"
                                            @checked($user->roles->contains('id', $role->id)) required>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                         @include('components.form-groups.input-group', [
                            'fieldName'     => "is_checkout",
                            'id'            => "is_checkout",
                            'model'         => $user,
                            'label'         => __('users.create.toggle_checkout'),
                            'showLabelTop'  => true,
                            'type'          => "toggle",
                            'checked'       => $user->is_checkout,
                            'value'         => 1,
                            'formClass'     => 'mb-2 col-6',
                            'inputClass'    => 'form-check-input text-sm',
                        ])
                    </div>
                    <div class="mb-3 col-md-2 d-none">
                        @include('components.select', [
                            'label'         => __('users.attributes.type'),
                            'fieldName'     => 'type',
                            'id'            => 'type',
                            'options'       => $user->getTypes(),
                            'selected'      => $user->type,
                            'placeholder'   => null,
                            'required'      => true,
                        ])
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-xs btn-primary" data-next>
                            {{ __('users.create.action_next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>
                {{-- STEP 2 --}}
                <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                    <div class="row">
                        @sys_admin
                            <div class="mb-3 col-md-12">
                                @include('components.select', [
                                    'label'         => $user->company_id ?
                                        '<a href="'.route('admin.companys.edit', $user->company).'" target="_blank">'.__('users.create.label_company').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                                        __('users.create.label_company'),
                                    'fieldName'     => 'company_id',
                                    'id'            => 'company_id',
                                    'options'       => $companyArray,
                                    'selected'      => request()->company_id ?? $user->company_id,
                                    'placeholder'   => null,
                                    'required'      => true,
                                    // 'changeUrl'     => route('admin.events.get-list-by-company-id'),
                                ])
                            </div>
                        @else
                            @include('components.form-groups.input-group', [
                                'id'                => "company_id",
                                'fieldName'         => "company_id",
                                'value'             => $company->id,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                        @endsys_admin
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            @include('components.select', [
                                'label'         => $user->event_id ?
                                    '<a href="'.route('admin.events.edit', $user->event).'" target="_blank">'.__('users.create.label_event').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                                    __('users.create.label_event'),
                                'fieldName'     => 'event_id',
                                'id'            => 'event_id',
                                'options'       => $eventArray,
                                'selected'      => $user->event_id ?? ($event->id ?? null),
                                'placeholder'   => null,
                                'formClass'     => 'form-control w-100',
                            ])
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <h5 class="fw-bold text-center">
                            {{ __('users.create.section_info_heading') }}
                        </h5>
                        @include('components.form-groups.input-group', [
                            'id'                => "name",
                            'model'             => $user,
                            'type'              => "text",
                            'label'             => __('users.attributes.name'),
                            'placeholder'       => __('users.placeholder.name'),
                            'required'          => true,
                            'formClass'         => 'mb-2 col-md-4',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "email",
                            'model'             => $user,
                            'type'              => "email",
                            'label'             => __('users.attributes.email'),
                            'placeholder'       => __('users.placeholder.email'),
                            'required'          => $user->isNew() ? true : ($user),
                            'formClass'         => 'mb-2 col-md-4',
                            'readonly'          => $user->isNew() ? false : true,
                            'disabled'          => $user->isNew() ? false : true,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "username",
                            'model'             => $user,
                            'type'              => "text",
                            'label'             => "Username",
                            'placeholder'       => "Username",
                            'required'          => true,
                            'formClass'         => 'mb-2 col-md-4',
                            'readonly'          => $user->isNew() ? false : true,
                            'disabled'          => $user->isNew() ? false : true,
                        ])
                    </div>
                    <hr>
                    <div class="row">
                        <h5 class="fw-bold text-center">
                            {{ __('users.create.section_password_heading') }}
                        </h5>
                        @include('components.form-groups.input-group', [
                            'id'                => "password",
                            'model'             => null,
                            'type'              => "password",
                            'label'             => __('users.attributes.password'),
                            'formClass'         => 'form-group mb-3 col-md-6',
                            'inputClass'        => 'form-control text-sm',
                            'placeholder'       => __('users.attributes.password'),
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "password_confirmation",
                            'model'             => null,
                            'type'              => "password",
                            'label'             => __('users.attributes.password_confirmation'),
                            'formClass'         => 'form-group mb-3 col-md-6',
                            'inputClass'        => 'form-control text-sm',
                            'placeholder'       => __('users.attributes.password_confirmation'),
                        ])
                    </div>
                    <hr>
                    <div class="row">
                        @sys_admin
                            <div class="col-md-4">
                                @include('components.select', [
                            'label'         => __('users.create.label_package'),
                                    'fieldName'     => 'package_id',
                                    'id'            => 'package_id',
                                    'options'       => $packagesArray,
                                    'selected'      => $user->package_id,
                                    'placeholder'   => null,
                                    'formClass'     => 'mb-3 form-control'
                                ])
                            </div>
                        @else
                            @include('components.form-groups.input-group', [
                                'id'                => "package_id",
                                'type'              => "hidden",
                                'value'             => auth()->user()->package_id,
                                'formClass'         => 'd-none'
                            ])
                        @endsys_admin
                        @include('components.form-groups.input-group', [
                            'id'                => "expire_date",
                            'model'             => $user,
                            'type'              => "date",
                            'value'             =>  $user->expire_date ? humanize_date($user->expire_date, 'Y-m-d') : null,
                            'label'             => __('users.create.label_expire_date'),
                            'formClass'         => 'mb-3 col-md-4'
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "gate",
                            'model'             => $user,
                            'type'              => "text",
                            'value'             =>  $user->gate ? humanize_date($user->gate, 'Y-m-d') : null,
                            'label'             => __('users.create.label_gate'),
                            'placeholder'       => __('users.create.placeholder_gate'),
                            'formClass'         => 'mb-3 col-md-4'
                        ])
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-xs btn-light" data-prev>
                            <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('users.create.action_back') }}
                        </button>
                        <button type="submit" class="btn btn-xs btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('users.create.action_save') }}</span>
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/users/detail.js'
    ])
@endpush
