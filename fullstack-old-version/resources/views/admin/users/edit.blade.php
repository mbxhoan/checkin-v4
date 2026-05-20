@extends('admin.layouts.templates.page-form', [
    'showBtns'  => false,
    'bread'     => true,
    'model'     => $user
])

@section('form-action', route('admin.users.update', $user))
@section('title', __('users.edit.page_heading'))
@section('li_1', __('users.edit.breadcrumb_label'))

@section('buttons')
    <div class="">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('users.edit.action_create') }}
        </a>
    </div>
@endsection

@section('custom-buttons')
    <div class="footer-fixed d-flex align-items-center justify-content-center">
        <button type="button"
                onclick="window.location='{{ route('admin.users.index') }}'"
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

@section('primary-content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-12">
            <x-card>
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
                                        @checked($user->roles->contains('id', $role->id))>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    @sys_admin
                        <div class="mb-3 col-md-4">
                            @include('components.select', [
                                'label'         => $user->company_id ?
                                    '<a href="'.route('admin.companys.edit', $user->company).'" target="_blank">'.__('users.edit.label_company').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                                    __('users.edit.label_company'),
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
                    <div class="mb-3 col-md-4">
                        @include('components.select', [
                            'label'         => $user->event_id ?
                                '<a href="'.route('admin.events.edit', $user->event).'" target="_blank">'.__('users.edit.label_event').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                                __('users.edit.label_event'),
                            'fieldName'     => 'event_id',
                            'id'            => 'event_id',
                            'options'       => $eventArray,
                            'selected'      => $user->event_id ?? ($event->id ?? null),
                            'placeholder'   => null,
                        ])
                    </div>
                    @include('components.form-groups.input-group', [
                        'fieldName'     => "is_checkout",
                        'id'            => "is_checkout",
                        'model'         => $user,
                        'label'         => 'Checkout',
                        'showLabelTop'  => true,
                        'type'          => "toggle",
                        'checked'       => $user->is_checkout,
                        'value'         => 1,
                        'formClass'     => 'mb-2 col-md-2',
                        'inputClass'    => 'form-check-input text-sm',
                    ])
                </div>
                <hr>
                <div class="row">
                    <h5 class="fw-bold text-center">
                        {{ __('users.edit.section_info_heading') }}
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
                        'formClass'         => 'mb-2 col-md-4',
                        'required'          => $user->isNew() ? true : ($user),
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
                        {{ __('users.edit.section_password_heading') }}
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
                                'label'         => "Gói đang dùng",
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
                        'label'             => __('users.edit.label_expire_date'),
                        'formClass'         => 'mb-3 col-md-4'
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "gate",
                        'model'             => $user,
                        'type'              => "text",
                        'value'             =>  $user->gate ? humanize_date($user->gate, 'Y-m-d') : null,
                        'label'             => __('users.edit.label_gate'),
                        'placeholder'       => __('users.edit.placeholder_gate'),
                        'formClass'         => 'mb-3 col-md-4'
                    ])
                    <div class="mb-3 col-md-4">
                        @include('components.select', [
                            'label'         => __('users.attributes.status'),
                            'fieldName'     => 'status',
                            'id'            => 'status-select',
                            'options'       => $user->getStatues(),
                            'selected'      => $user->status,
                            'placeholder'   => null,
                            'required'      => true,
                        ])
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- @include('admin/users/_form', [
        'user'          => $user,
        'company'       => $company,
        'event'         => $event ?? null,
        'companyArray'  => $companyArray,
        'eventArray'    => $eventArray,
        'roles'         => $roles,
    ]) --}}
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/users/detail.js'
    ])
@endpush
