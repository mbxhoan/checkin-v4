<div class="">
    <div class="buttons">
        <div class="row align-items-center justify-content-between mb-2">
            <div class="col-md-4 fw-bold">
                Mẫu in: <span class="text-danger">{{ $total ?? 0 }}</span>
            </div>
            <div class="col-md-4 text-end">
                <form action="{{ route('admin.labels.select-event-to-create') }}" method="GET">
                    @include('components.form-groups.input-group', [
                        'id'                => "event_id",
                        'model'             => $model->id,
                        'type'              => "hidden",
                    ])
                    <button type="submit" class="btn btn-xs btn-primary align-self-center mb-lg-0 mb-2">
                        <x-icon name="plus-square" prefix="fa-regular"/>
                        Thêm mới
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="table-responsive pb-2">
        {!! $dataTable->table() !!}
    </div>
</div>

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
