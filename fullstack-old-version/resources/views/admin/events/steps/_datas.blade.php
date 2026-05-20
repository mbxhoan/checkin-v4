<div class="p-3 bg-light-subtle border rounded-3 guest-import-panel">
    <div class="small text-muted mb-2">
        {{ __('events.datas.description') }}
    </div>

    <form action="{{ route('admin.clients.get-template-qrcodes', $event) }}" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-auto">
            <label for="seed_clients_count" class="form-label fw-semibold fs-6 mb-1">
                {{ __('events.datas.step_1') }}
            </label>
            <input
                type="number"
                id="seed_clients_count"
                name="count"
                min="1"
                max="5000"
                value="100"
                class="form-control form-control-sm"
                style="width: 140px;"
            >
        </div>

        <div class="col-12 col-md-6">
            <label for="seed_clients_type" class="form-label fw-semibold fs-6 mb-1">
                {{ __('events.datas.group_name') }}
            </label>
            <input
                type="text"
                id="seed_clients_type"
                name="type"
                value=""
                class="form-control form-control-sm"
                placeholder="{{ __('events.datas.group_placeholder') }}"
            >
        </div>

        <div class="col-12 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                <x-icon name="download" />
                {{ __('events.datas.download') }}
            </button>
            <button
                type="button"
                id="btn-generate-clients"
                class="btn btn-warning btn-sm shadow-sm"
                data-confirm="{{ __('events.datas.confirm_generate', ['event' => $event->name]) }}"
                data-url="{{ route('admin.clients.generate', $event) }}"
            >
                <x-icon name="plus" />
                {{ __('events.datas.create') }}
            </button>
        </div>
    </form>

    <hr class="my-3">

    <form action="{{ route('admin.clients.upload', $event) }}" method="POST" enctype="multipart/form-data" class="text-xs">
        @csrf
        <div class="row g-2 align-items-end">
            @include('components.form-groups.input-group', [
                'id' => 'file',
                'label' => __('events.datas.step_2'),
                'labelClass' => 'form-label fw-semibold fs-6 mb-1',
                'model' => $event,
                'type' => 'file',
                'accept' => '.xlsx',
                'formClass' => 'mb-0 col-md-6'
            ])

            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                    <x-icon name="upload"/>
                    {{ __('events.datas.upload') }}
                </button>
            </div>

            @include('components.form-groups.input-group', [
                'id' => 'event_id',
                'fieldName' => 'event_id',
                'value' => $event->id,
                'type' => 'hidden',
                'formClass' => 'd-none',
            ])
        </div>
    </form>
</div>
