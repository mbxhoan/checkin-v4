@include('admin.events.steps._qrcode', [
    'model'     => $event,
    'settings'  => $settings ?? collect(),
    'setting'   => $setting ?? \App\Models\EventSetting::getModel(),
])
