<div class="collapse p-2 bg-light rounded shadow-sm" id="collapseCheckin">
    <div class="row mb-2 justify-content-center">
        <div class="col-md-4 fw-bold text-sm text-center">
            <a href="">
                <a class="btn btn-sm btn-primary" href="{{ route('admin.checkins.config', $event) }}">
                    Cấu hình Checkin
                </a>
            </a>
        </div>
        <div class="col-md-4 fw-bold text-sm text-center">
            <a href="">
                <a class="btn btn-sm btn-primary" target="_blank" href="{{ $event->getScanLink() }}">
                    Trang Checkin
                </a>
            </a>
        </div>
    </div>
</div>

