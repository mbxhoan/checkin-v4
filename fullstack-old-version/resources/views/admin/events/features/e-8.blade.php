<div class="">
    <div class="d-flex mb-2">
        <div class="bg-light border rounded shadown-sm px-4 py-3">
            <h6>
                Campaign(s):
            </h6>
            <h5 class="text-danger fw-bold">
                {{ $total ?? 0 }}
            </h5>
        </div>
        <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2">
            <h6 class="">
                Tổng mail
            </h6>
            <h5 class="text-danger fw-bold">
                <span class="text-lg">{{ $sentEmailCount ?? 0 }} </span>
                @if (!empty($limitedEmails))
                    <span class="text-xs text-secondary">/{{ $limitedEmails }}</span>
                    @include('components._progress', [
                        'completed'     => $sentEmailCount ?? 0,
                        'total'         => $limitedEmails ?? $sentEmailCount,
                        'width'         => 300,
                    ])
                @else
                    <span class="text-xs text-secondary">Gói không giới hạn</span>
                @endif
            </h5>
        </div>
        @if (count($dataStatuses))
            @foreach ($dataStatuses as $status => $count)
                <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2" style="width: 150px;">
                    @php
                        switch ($status) {
                            case 'Bounce':
                                $status = "Chưa gửi";
                                break;
                            case 'Open':
                                $status = "Đã mở";
                                break;
                            case 'Click':
                                $status = "Đã click";
                                break;
                            default:
                                $status = "Đã gửi";
                                break;
                        }
                    @endphp
                    <h6>
                        {{ $status }}
                    </h6>
                    <h5 class="text-danger fw-bold">
                        {{ $count }}
                    </h5>
                </div>
            @endforeach
        @endif
    </div>
    <div class="buttons">
        <div class="d-lg-flex justify-content-between">
            <a href="{{ route('admin.email_templates.index') }}" class="btn btn-primary btn-sm align-self-center mb-1 ms-1">
                <x-icon name="calendar-days"/>
                Templates
            </a>
            <div class="">
                <form action="{{ route('admin.campaigns.select-event-to-create') }}" method="GET">
                    @include('components.form-groups.input-group', [
                        'id'                => "event_id",
                        'model'             => $model->id,
                        'type'              => "hidden",
                    ])
                    <button type="submit" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2">
                        <x-icon name="plus-square" prefix="fa-regular"/>
                        Thêm mới
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class="">
            @include('admin.campaigns._modal-filter', [
                'modalId'       => 'selectEventModal',
                'title'         => "Bộ lọc",
                'submitBtn'     => "Lọc",
                'model'         => \App\Models\Client::getModel(),
                'route'         => route('admin.campaigns.index'),
            ])
            {{-- <a href="{{ route('admin.campaigns.export-list', ['event' => $event]) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="file-excel" prefix="fa-solid"/>
                @lang('imports.export')
            </a> --}}
        </div>
    </div>
    <div class="table-responsive pb-2">
        {!! $dataTable->table() !!}
    </div>
</div>

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
