@extends('admin.layouts.templates.page', [
    'showBtns' => false,
    'bread'    => true,
])

@section('form-action', route('admin.lucky_draws.update', $model))
@section('title', __('lucky_draws.detail-wheel.page_heading'))
@section('li_1', __('lucky_draws.detail-wheel.breadcrumb_label'))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.lucky_draws.display', $model) }}" class="btn btn-sm btn-info me-2" target="_blank">
            <x-icon name="tv" prefix="fa-solid"/>
            {{ __('lucky_draws.detail-wheel.action_display') }}
        </a>
        <a href="{{ route('admin.lucky_draws.create') }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('lucky_draws.detail-wheel.action_create') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="row g-2">
        {{-- LEFT: Danh sách tham dự --}}
        <div class="col-md-5">
            <x-card>
                <form action="{{ route('admin.lucky_draw_clients.sync', $model) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>
                                <i class="fa-solid fa-users text-primary"></i>
                                {{ __('lucky_draws.detail-wheel.participants_heading') }}
                                <span class="badge bg-danger">
                                    {{ $luckyDrawClients ? $luckyDrawClients->count() : 0 }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <x-icon name="rotate"/>
                                {{ __('lucky_draws.detail-wheel.action_sync') }}
                            </button>
                            @if ($luckyDrawClients && $luckyDrawClients->count() > 0)
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmResetClientsModal">
                                <x-icon name="eraser"/>
                                {{ __('lucky_draws.detail-wheel.action_reset_clients') }}
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            @include('components.select', [
                                'label'         => __('lucky_draws.detail-wheel.label_client_type'),
                                'id'            => 'client_type',
                                'fieldName'     => 'client_type',
                                'options'       => ["" => __('lucky_draws.detail.option_all')] + $types,
                                'selected'      => null,
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('components.select', [
                                'label'         => __('lucky_draws.detail-wheel.label_group'),
                                'id'            => 'group',
                                'fieldName'     => 'group',
                                'options'       => $groups ?? [],
                                'selected'      => null,
                            ])
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    @if (!empty($dataTable))
                        {!! $dataTable->table() !!}
                    @endif
                </div>

                @if ($luckyDrawClients && $luckyDrawClients->count() > 0)
                <div class="modal fade" id="confirmResetClientsModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('lucky_draws.detail-wheel.modal_reset_title') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ __('lucky_draws.detail-wheel.modal_reset_body', ['count' => $luckyDrawClients->count()]) }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('lucky_draws.detail-wheel.modal_cancel') }}</button>
                                <form action="{{ route('admin.lucky_draw_clients.reset', $model) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">{{ __('lucky_draws.detail-wheel.modal_confirm') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </x-card>
        </div>

        {{-- RIGHT: Wheel Preview --}}
        <div class="col-md-7">
            <x-card>
                <div class="text-center">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-circle-notch text-success"></i>
                        {{ __('lucky_draws.detail-wheel.preview_heading') }}
                    </h5>
                    <div id="wheel-container" class="d-flex justify-content-center align-items-center" style="min-height: 500px;">
                        <canvas id="wheel-canvas" width="500" height="500"></canvas>
                    </div>
                    <div class="mt-3 text-muted small">
                        <p><i class="fa-solid fa-info-circle"></i> {{ __('lucky_draws.detail-wheel.preview_hint') }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    <script>
        // Wheel drawing logic
        const canvas = document.getElementById('wheel-canvas');
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 200;

        // Get entries from backend
        const entries = @json($luckyDrawClients->pluck('name')->take(20)->values()->toArray());

        function drawWheel() {
            if (entries.length === 0) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.font = '20px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('{{ __('lucky_draws.detail-wheel.no_entries') }}', centerX, centerY);
                return;
            }

            const colors = [
                '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', 
                '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2',
                '#F8B739', '#52B788', '#E76F51', '#2A9D8F'
            ];

            const sliceAngle = (2 * Math.PI) / entries.length;

            entries.forEach((entry, index) => {
                const startAngle = index * sliceAngle - Math.PI / 2;
                const endAngle = startAngle + sliceAngle;

                // Draw slice
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                ctx.closePath();
                ctx.fillStyle = colors[index % colors.length];
                ctx.fill();
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Draw text
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(startAngle + sliceAngle / 2);
                ctx.textAlign = 'left';
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 14px Arial';
                ctx.fillText(entry.substring(0, 15), radius / 2, 5);
                ctx.restore();
            });

            // Draw center circle
            ctx.beginPath();
            ctx.arc(centerX, centerY, 30, 0, 2 * Math.PI);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 3;
            ctx.stroke();

            // Draw pointer
            ctx.beginPath();
            ctx.moveTo(centerX + radius + 10, centerY);
            ctx.lineTo(centerX + radius - 20, centerY - 15);
            ctx.lineTo(centerX + radius - 20, centerY + 15);
            ctx.closePath();
            ctx.fillStyle = '#FF4757';
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        drawWheel();
    </script>
@endpush
