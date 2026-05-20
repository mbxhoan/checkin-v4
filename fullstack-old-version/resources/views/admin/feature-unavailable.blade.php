@extends('admin.layouts.templates.page')

@section('title', 'Tính năng chưa hỗ trợ')

@section('primary-content')
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                            style="width: 52px; height: 52px;"
                        >
                            <i class="fa-solid fa-lock fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted text-xs">Tính năng</div>
                            <div class="fw-bold" style="font-size: 1.25rem;">
                                {{ $label }}@if(!empty($sub)) / {{ $sub }}@endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="text-muted">
                        Phiên bản hiện tại không áp dụng tính năng này.
                        Vui lòng nâng cấp gói hoặc liên hệ Delfi để được tư vấn kích hoạt.
                    </div>

                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left"></i>
                            Quay lại
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="fa-solid fa-house"></i>
                            Về Dashboard
                        </a>
                    </div>

                    @if (!empty($featureKey))
                        <div class="mt-3 text-muted text-xs">
                            Mã tính năng: <span class="fw-semibold">{{ $featureKey }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

