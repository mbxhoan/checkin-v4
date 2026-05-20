<div class="">
    <div class="d-flex mb-2">
        <div class="bg-light border rounded shadown-sm px-4 py-3">
            <h6>
                Landing page(s)
            </h6>
            <h5 class="text-danger fw-bold">
                {{ $total ?? 0 }}
            </h5>
        </div>
        <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2">
            <h6>
                Lượng truy cập
            </h6>
            <h5 class="text-danger fw-bold">
                {{ !empty($totalAccesses) ? $totalAccesses->count() : 0 }}
            </h5>
        </div>
        <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2">
            <h6>
                Đã đăng ký
            </h6>
            <h5 class="text-danger fw-bold">
                {{ !empty($clientsLp) ? $clientsLp->count() : 0 }}
            </h5>
        </div>
        <div class="px-4">
            <form action="{{ route('admin.events.upload-medias', $model) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    @include('components.form-groups.input-group', [
                        'id'        => "logo",
                        'label'     => 'Logo '.'<i class="fa-solid fa-image"></i>',
                        'model'     => $model,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2 col-md-6'
                    ])
                    @include('components.form-groups.input-group', [
                        'id'        => "favicon",
                        'label'     => 'Favicon '.'<i class="fa-solid fa-image"></i>',
                        'model'     => $model,
                        'type'      => "file",
                        'accept'    => ".png, .jpg, .jpeg",
                        'formClass' => 'mb-2 col-md-6'
                    ])
                </div>
                <div class="row mt-2 justify-content-center">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-xs w-100">
                            <x-icon name="upload"/>
                            Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="buttons">
        <div class="row align-items-center justify-content-between mb-2">
            <div class="col-md-4 fw-bold">
                Thiệp/Thiệp: <span class="text-danger">{{ $total ?? 0 }}</span>
            </div>
            <div class="col-md-4 text-end">
                <form action="{{ route('admin.landing_pages.select-event-to-create') }}" method="GET">
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
