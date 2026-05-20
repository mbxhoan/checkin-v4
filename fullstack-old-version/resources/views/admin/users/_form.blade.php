<form action="{{ !$user->isNew() ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
    @if ($user->isNew())
        @method('POST')
    @else
        @method('PATCH')
    @endif

    @csrf

    <div class="row">
        @sys_admin
            <div class="mb-3 col-md-3">
                @include('components.select', [
                    'label'         => $user->company_id ?
                        '<a href="'.route('admin.companys.edit', $user->company).'" target="_blank">'.__('users._form.label_company').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                        __('users._form.label_company'),
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

        @include('components.form-groups.input-group', [
            'id'                => "name",
            'model'             => $user,
            'type'              => "text",
            'label'             => __('users.attributes.name'),
            'placeholder'       => __('users.placeholder.name'),
            'required'          => true,
            'formClass'         => 'mb-2 col-md-3',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "email",
            'model'             => $user,
            'type'              => "email",
            'label'             => __('users.attributes.email'),
            'placeholder'       => __('users.placeholder.email'),
            'required'          => $user->isNew() ? true : ($user),
            'formClass'         => 'mb-2 col-md-2',
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
            'formClass'         => 'mb-2 col-md-2',
            'readonly'          => $user->isNew() ? false : true,
            'disabled'          => $user->isNew() ? false : true,
        ])
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
    <div class="row">
        <div class="mb-3 col-md-3">
            @include('components.select', [
                'label'         => $user->event_id ?
                    '<a href="'.route('admin.events.edit', $user->event).'" target="_blank">'.__('users._form.label_event').' <i class="fa-solid fa-edit fa-xs"></i></a>' :
                    __('users._form.label_event'),
                'fieldName'     => 'event_id',
                'id'            => 'event_id',
                'options'       => $eventArray,
                'selected'      => $user->event_id ?? ($event->id ?? null),
                'placeholder'   => null,
            ])
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "password",
            'model'             => null,
            'type'              => "password",
            'label'             => __('users.attributes.password'),
            'formClass'         => 'form-group mb-3 col-md-3',
            'inputClass'        => 'form-control text-sm',
            'placeholder'       => __('users.attributes.password'),
        ])
        @include('components.form-groups.input-group', [
            'id'                => "password_confirmation",
            'model'             => null,
            'type'              => "password",
            'label'             => __('users.attributes.password_confirmation'),
            'formClass'         => 'form-group mb-3 col-md-3',
            'inputClass'        => 'form-control text-sm',
            'placeholder'       => __('users.attributes.password_confirmation'),
        ])
        <div class="mb-3 col-md-2">
            @include('components.select', [
                'label'         => __('users.attributes.status'),
                'fieldName'     => 'status',
                'id'            => 'status',
                'options'       => $user->getStatues(),
                'selected'      => $user->status,
                'placeholder'   => null,
                'required'      => true,
            ])
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-2">
                <label class="form-label" for="roles">
                    @lang('users.attributes.roles')
                </label>

                @foreach ($roles as $role)
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="roles[{{ $role->id }}]" value="{{ $role->id }}"
                                @checked($user->hasRole($role->name))>

                            @if (Lang::has('roles.' . $role->name))
                                {!! __('roles.' . $role->name) !!}
                            @else
                                {{ ucfirst($role->name) }}
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            @sys_admin
                @include('components.select', [
                    'label'         => "Gói đang dùng",
                    'fieldName'     => 'package_id',
                    'id'            => 'package_id',
                    'options'       => $packagesArray,
                    'selected'      => $user->package_id,
                    'placeholder'   => null,
                    'formClass'     => 'mb-3 form-control'
                ])
            @else
                @include('components.form-groups.input-group', [
                    'id'                => "package_id",
                    'type'              => "hidden",
                    'value'             => auth()->user()->package_id,
                    'formClass'         => 'd-none'
                ])
            @endsys_admin
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "expire_date",
            'model'             => $user,
            'type'              => "date",
            'value'             =>  $user->expire_date ? humanize_date($user->expire_date, 'Y-m-d') : null,
            'label'             => __('users._form.label_expire_date'),
            'formClass'         => 'mb-3 col-md-2'
        ])
        @include('components.form-groups.input-group', [
            'id'                => "gate",
            'model'             => $user,
            'type'              => "text",
            'value'             =>  $user->gate ? humanize_date($user->gate, 'Y-m-d') : null,
            'label'             => __('users._form.label_gate'),
            'placeholder'       => __('users._form.placeholder_gate'),
            'formClass'         => 'mb-3 col-md-2'
        ])
        <div class="col-md-2">
            @include('components.select', [
                'label'         => __('users.attributes.gender'),
                'fieldName'     => 'gender',
                'id'            => 'gender',
                'options'       => $user->getGenders(),
                'selected'      => $user->gender,
                'placeholder'   => null,
                'required'      => true,
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
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-light">
        <x-icon name="chevron-left" />

        @lang('forms.actions.back')
    </a>

    <button type="submit" class="btn btn-primary">
        <x-icon name="save" />

        @lang('forms.actions.update')
    </button>
</form>
