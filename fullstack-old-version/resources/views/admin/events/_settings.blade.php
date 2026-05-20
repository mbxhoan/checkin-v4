@foreach ($groups ?? $setting->getEventSettingGroup() as $groupKey => $groupName)
    <div class="bg-light rounded shadow-sm p-2">
        <div class="fw-bold mt-2">
            {!! $groupName !!}
        </div>
        @php
            $childSettings = $settings->where('parent_id', '!=', null);
        @endphp
        @foreach ($settings as $setting)
            @if ($setting->group == $groupKey && empty($setting->parent_id))
                @if (auth()->user()->validateSetting($setting->name))
                    @include('admin.event_settings._setting', [
                        'event'     => $event,
                        'setting'   => $setting,
                    ])
                @endif
                @foreach ($childSettings as $childSetting)
                    @if ($childSetting->parent_id == $setting->id)
                        @if (auth()->user()->validateSetting($setting->name))
                            @include('admin.event_settings._setting', [
                                'event'     => $event,
                                'setting'   => $childSetting,
                                'isChild'   => true,
                            ])
                        @endif
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
@endforeach
