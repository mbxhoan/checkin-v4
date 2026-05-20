@extends('admin.layouts.app')

@section('title', __('lucky_draws.builder.page_title_prefix') . ' ' . $luckyDraw->name)

@push('admin_css')
<style>
.lucky-draw-builder {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
}
.lucky-draw-builder .builder-header {
    flex-shrink: 0;
}
.lucky-draw-builder .builder-body {
    flex: 1;
    overflow: hidden;
}
.lucky-draw-builder .builder-body .row {
    height: 100%;
}
.lucky-draw-builder .data-panel,
.lucky-draw-builder .prize-panel {
    height: 100%;
    overflow-y: auto;
    background: #fff;
}
.lucky-draw-builder .canvas-panel {
    height: 100%;
    background: #1a1a1a;
}
.lucky-draw-builder .canvas-wrapper {
    background: #2a2a2a;
}
.lucky-draw-builder .canvas-container {
    width: 100%;
    height: 100%;
}
.lucky-draw-builder .field-list {
    max-height: 300px;
    overflow-y: auto;
}
.lucky-draw-builder .field-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    margin-bottom: 4px;
    background: #f8f9fa;
    border-radius: 4px;
    cursor: grab;
    font-size: 13px;
}
.lucky-draw-builder .field-item:hover {
    background: #e9ecef;
}
.lucky-draw-builder .field-item.dragging {
    opacity: 0.5;
}
.lucky-draw-builder .field-group-header {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    padding: 4px 0;
}
.lucky-draw-builder .reward-item {
    cursor: pointer;
}
.lucky-draw-builder .reward-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.lucky-draw-builder .reward-item .reward-thumb {
    max-width: 100%;
    max-height: 60px;
    object-fit: contain;
}
.lucky-draw-builder .canvas-tabs-container {
    background: #f8f9fa;
}
.lucky-draw-builder .canvas-tabs-container .nav-tabs {
    border-bottom: none;
}
.lucky-draw-builder .canvas-tabs-container .nav-tabs .nav-link {
    font-size: 13px;
    padding: 8px 16px;
    border-radius: 0;
    border-bottom: 2px solid transparent;
    cursor: pointer;
}
.lucky-draw-builder .canvas-tabs-container .nav-tabs .nav-link.active {
    border-bottom-color: var(--bs-primary);
    font-weight: 500;
}
.lucky-draw-builder .block-toolbox {
    background: #f0f0f0;
}
.lucky-draw-builder .properties-panel {
    background: #fff;
    max-height: 300px;
    overflow-y: auto;
}
.lucky-draw-builder .participant-list {
    max-height: 150px;
    overflow-y: auto;
}
.lucky-draw-builder .participant-item {
    padding: 4px 8px;
    background: #f8f9fa;
    margin-bottom: 2px;
    border-radius: 2px;
    cursor: pointer;
}
.lucky-draw-builder .participant-item:hover {
    background: #e9ecef;
}
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
}
</style>
@endpush

@section('content')
<div class="lucky-draw-builder"
     data-lucky-draw-id="{{ $luckyDraw->id }}"
     data-api-base="{{ route('admin.lucky_draws.builder.index', $luckyDraw) }}">

    <!-- Header Toolbar -->
    <div class="builder-header bg-light border-bottom p-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.lucky_draws.edit', $luckyDraw) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('lucky_draws.builder.action_back') }}
            </a>
            <h5 class="mb-0">{{ $luckyDraw->name }}</h5>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-undo" disabled>
                <i class="fas fa-undo"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-redo" disabled>
                <i class="fas fa-redo"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-preview">
                <i class="fas fa-eye"></i> {{ __('lucky_draws.builder.action_preview') }}
            </button>
            <button type="button" class="btn btn-sm btn-success" id="btn-save">
                <i class="fas fa-save"></i> {{ __('lucky_draws.builder.action_save') }}
            </button>
        </div>
    </div>

    <div class="builder-body">
        <div class="row g-0 h-100">
            <!-- LEFT: Data Panel -->
            <div class="col-2 data-panel border-end">
                <div class="panel-header bg-light border-bottom p-2">
                    <strong>{{ __('lucky_draws.builder.panel_event_data') }}</strong>
                </div>
                <div class="panel-body p-2">
                    <!-- Participant Filter -->
                    <div class="mb-3">
                        <label class="form-label small">{{ __('lucky_draws.builder.label_participant_filter') }}</label>
                        <select class="form-select form-select-sm" id="participant-filter">
                            <option value="all">{{ __('lucky_draws.builder.filter_all') }} ({{ $clients->count() }})</option>
                            <option value="available">{{ __('lucky_draws.builder.filter_available') }}</option>
                            <option value="won">{{ __('lucky_draws.builder.filter_won') }}</option>
                        </select>
                    </div>

                    <!-- Field Selection -->
                    <div class="mb-3">
                        <label class="form-label small">{{ __('lucky_draws.builder.label_core_fields') }}</label>
                        <div id="field-list" class="field-list">
                            @foreach($fields['core_fields'] ?? [] as $field)
                            <div class="field-item draggable-field"
                                 data-field-key="{{ $field['key'] }}"
                                 data-field-type="{{ $field['type'] }}"
                                 draggable="true">
                                <i class="fas fa-grip-vertical text-muted"></i>
                                <span>{{ $field['label'] }}</span>
                                <span class="badge bg-secondary">{{ $field['type'] }}</span>
                            </div>
                            @endforeach

                            @if(count($fields['custom_fields'] ?? []) > 0)
                            <div class="field-group-header mt-2">{{ __('lucky_draws.builder.label_custom_fields') }}</div>
                            @foreach($fields['custom_fields'] as $field)
                            <div class="field-item draggable-field"
                                 data-field-key="{{ $field['key'] }}"
                                 data-field-type="{{ $field['type'] }}"
                                 draggable="true">
                                <i class="fas fa-grip-vertical text-muted"></i>
                                <span>{{ $field['label'] }}</span>
                                <span class="badge bg-info">{{ $field['type'] }}</span>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Participant List Preview -->
                    <div class="mb-3">
                        <label class="form-label small">{{ __('lucky_draws.builder.label_participant_sample') }}</label>
                        <div id="participant-preview" class="participant-list">
                            @foreach($clients->take(5) as $client)
                            <div class="participant-item small" data-client-id="{{ $client->id }}">
                                {{ $client->name }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- CENTER: Prize Management -->
            <div class="col-3 prize-panel border-end">
                <div class="panel-header bg-light border-bottom p-2">
                    <strong>{{ __('lucky_draws.builder.panel_rewards') }}</strong>
                </div>
                <div class="panel-body p-2">
                    <!-- Reward List -->
                    <div class="reward-list mb-3">
                        @foreach($rewards as $reward)
                        <div class="reward-item card mb-2 {{ $reward->is_given ? 'bg-light' : '' }}"
                             data-reward-id="{{ $reward->id }}">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $reward->order_name }}</strong>
                                        <div class="small text-muted">{{ $reward->name }}</div>
                                    </div>
                                    <span class="badge {{ $reward->is_given ? 'bg-success' : 'bg-warning' }}">
                                        {{ $reward->is_given ? __('lucky_draws.builder.badge_given') : __('lucky_draws.builder.badge_waiting') }}
                                    </span>
                                </div>
                                @if($reward->img_link)
                                <img src="{{ $reward->img_link }}" class="reward-thumb mt-1" alt="">
                                @endif
                                <div class="mt-2 d-flex gap-1 flex-wrap">
                                    <button class="btn btn-xs btn-outline-primary btn-edit-layout"
                                            data-reward-id="{{ $reward->id }}">
                                        <i class="fas fa-edit"></i> {{ __('lucky_draws.builder.btn_layout') }}
                                    </button>
                                    <button class="btn btn-xs btn-outline-info btn-auto-generate"
                                            data-reward-id="{{ $reward->id }}"
                                            data-reward-value="{{ $reward->value }}"
                                            title="{{ __('lucky_draws.builder.auto_generate_title') }}">
                                        <i class="fas fa-magic"></i> {{ __('lucky_draws.builder.btn_auto') }}
                                    </button>
                                    <button class="btn btn-xs btn-outline-success btn-view-winners"
                                            data-reward-id="{{ $reward->id }}">
                                        <i class="fas fa-trophy"></i> {{ __('lucky_draws.builder.btn_winners') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Winners Section -->
                    <div id="winners-section" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Người thắng</strong>
                            <button class="btn btn-xs btn-outline-secondary" id="btn-close-winners">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="winners-list"></div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Canvas Builder -->
            <div class="col-7 canvas-panel d-flex flex-column">
                <!-- Canvas Tabs (per reward) -->
                <div class="canvas-tabs-container border-bottom">
                    <ul class="nav nav-tabs" id="layout-tabs">
                        @foreach($rewards as $index => $reward)
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                               data-reward-id="{{ $reward->id }}"
                               data-layout-id="{{ $reward->layout?->id ?? '' }}"
                               data-reward-img="{{ $reward->img_link ?? '' }}"
                               data-reward-value="{{ $reward->value ?? 1 }}">
                                {{ $reward->order_name }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Block Toolbox -->
                <div class="block-toolbox bg-light border-bottom p-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-secondary block-tool" id="btn-add-random-field" data-block-type="random_field" title="Số ô tối đa theo value của giải">
                            <i class="fas fa-random"></i> Ô quay ngẫu nhiên
                        </button>
                        <div class="vr"></div>
                        <button class="btn btn-sm btn-outline-danger" id="btn-delete-block" disabled>
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-duplicate-block" disabled>
                            <i class="fas fa-copy"></i> Nhân bản
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-background">
                            <i class="fas fa-fill-drip"></i> Nền
                        </button>
                    </div>
                </div>

                <!-- Canvas Container -->
                <div class="canvas-wrapper flex-grow-1 position-relative overflow-hidden">
                    <div id="canvas-container" class="canvas-container"></div>

                    <!-- Canvas Controls -->
                    <div class="canvas-controls position-absolute bottom-0 end-0 p-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-light" id="btn-zoom-out"><i class="fas fa-minus"></i></button>
                            <span class="btn btn-light" id="zoom-level">100%</span>
                            <button class="btn btn-light" id="btn-zoom-in"><i class="fas fa-plus"></i></button>
                            <button class="btn btn-light" id="btn-zoom-fit"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Properties Panel (collapsible sidebar) -->
                <div id="properties-panel" class="properties-panel border-top" style="display: none;">
                    <div class="p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ __('lucky_draws.builder.properties_title') }}</strong>
                            <button class="btn btn-xs btn-light" id="btn-close-properties">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="properties-form">
                            <!-- Position -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_x') }}</label>
                                    <input type="number" class="form-control form-control-sm" name="x">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_y') }}</label>
                                    <input type="number" class="form-control form-control-sm" name="y">
                                </div>
                            </div>

                            <!-- Size -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_width') }}</label>
                                    <input type="number" class="form-control form-control-sm" name="width">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_height') }}</label>
                                    <input type="number" class="form-control form-control-sm" name="height">
                                </div>
                            </div>

                            <!-- Source Field (for field-based blocks) -->
                            <div class="mb-2 source-field-group" style="display: none;">
                                <label class="form-label small">{{ __('lucky_draws.builder.label_source_field') }}</label>
                                <select class="form-select form-select-sm" name="source">
                                    <option value="">{{ __('lucky_draws.builder.label_source_field') }}</option>
                                </select>
                            </div>

                            <!-- Text Style (for text blocks) -->
                            <div class="text-style-group" style="display: none;">
                                <div class="mb-2">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_font_size') }}</label>
                                    <input type="number" class="form-control form-control-sm" name="style.fontSize" min="8" max="200">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_font_color') }}</label>
                                    <input type="color" class="form-control form-control-sm form-control-color" name="style.color">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_align') }}</label>
                                    <select class="form-select form-select-sm" name="style.align">
                                        <option value="left">{{ __('lucky_draws.builder.align_left') }}</option>
                                        <option value="center">{{ __('lucky_draws.builder.align_center') }}</option>
                                        <option value="right">{{ __('lucky_draws.builder.align_right') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Image URL (for image blocks) -->
                            <div class="image-url-group" style="display: none;">
                                <div class="mb-2">
                                    <label class="form-label small">{{ __('lucky_draws.builder.label_image_url') }}</label>
                                    <input type="text" class="form-control form-control-sm" name="imageUrl" placeholder="https://...">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-upload-image">
                                    <i class="fas fa-upload"></i> {{ __('lucky_draws.builder.btn_upload') }}
                                </button>
                            </div>

                            <!-- Visibility -->
                            <div class="mb-2">
                                <label class="form-label small">{{ __('lucky_draws.builder.label_visible_when') }}</label>
                                <select class="form-select form-select-sm" name="visibleWhen">
                                    <option value="always">{{ __('lucky_draws.builder.visible_always') }}</option>
                                    <option value="result">{{ __('lucky_draws.builder.visible_result') }}</option>
                                </select>
                            </div>

                            <!-- Slot Index -->
                            <div class="mb-2 slot-index-group" style="display: none;">
                                <label class="form-label small">{{ __('lucky_draws.builder.label_slot_index') }}</label>
                                <input type="number" class="form-control form-control-sm" name="slotIndex" min="0">
                                <small class="text-muted">{{ __('lucky_draws.builder.slot_index_hint') }}</small>
                            </div>

                            <!-- Z-Index -->
                            <div class="mb-2">
                                <label class="form-label small">{{ __('lucky_draws.builder.label_layer') }}</label>
                                <div class="btn-group btn-group-sm w-100">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-send-back">
                                        <i class="fas fa-arrow-down"></i> {{ __('lucky_draws.builder.action_send_back') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-bring-front">
                                        <i class="fas fa-arrow-up"></i> {{ __('lucky_draws.builder.action_bring_front') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Background Settings Modal -->
<div class="modal fade" id="backgroundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('lucky_draws.builder.bg_settings_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.bg_type_label') }}</label>
                    <select class="form-select" id="bg-type">
                        <option value="color">{{ __('lucky_draws.builder.bg_type_color') }}</option>
                        <option value="image">{{ __('lucky_draws.builder.bg_type_image') }}</option>
                        <option value="video">{{ __('lucky_draws.builder.bg_type_video') }}</option>
                    </select>
                </div>
                <div id="bg-color-group" class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.bg_color_label') }}</label>
                    <input type="color" class="form-control form-control-color" id="bg-color" value="#000000">
                </div>
                <div id="bg-image-group" class="mb-3" style="display: none;">
                    <label class="form-label">{{ __('lucky_draws.builder.bg_image_url_label') }}</label>
                    <input type="text" class="form-control" id="bg-image-url">
                    <button type="button" class="btn btn-outline-secondary mt-2" id="btn-upload-bg">
                        <i class="fas fa-upload"></i> {{ __('lucky_draws.builder.bg_upload_image') }}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('lucky_draws.builder.modal_cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-apply-bg">{{ __('lucky_draws.builder.modal_apply') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">{{ __('lucky_draws.builder.preview_title') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="preview-container" style="aspect-ratio: 16/9;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Generate Slots Modal -->
<div class="modal fade" id="autoGenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('lucky_draws.builder.auto_generate_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="auto-gen-reward-id">
                <input type="hidden" id="auto-gen-slot-count">
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ __('lucky_draws.builder.auto_alert_info', ['count' => 0]) }}
                    <br>
                    <small class="text-muted">Số lượng ô được xác định bởi giá trị "Value" của giải thưởng</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_layout_type_label') }}</label>
                    <select class="form-select" id="auto-layout-type">
                        <option value="grid">{{ __('lucky_draws.builder.auto_layout_grid') }}</option>
                        <option value="horizontal">{{ __('lucky_draws.builder.auto_layout_horizontal') }}</option>
                        <option value="vertical">{{ __('lucky_draws.builder.auto_layout_vertical') }}</option>
                    </select>
                </div>
                
                <div class="mb-3" id="grid-cols-group">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_grid_cols_label') }}</label>
                    <input type="number" class="form-control" id="auto-grid-cols" value="3" min="1" max="10">
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.auto_slot_width_label') }}</label>
                        <input type="number" class="form-control" id="auto-slot-width" value="300" min="50" max="800">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.auto_slot_height_label') }}</label>
                        <input type="number" class="form-control" id="auto-slot-height" value="80" min="30" max="300">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_spacing_label') }}</label>
                    <input type="number" class="form-control" id="auto-spacing" value="20" min="0" max="100">
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.auto_start_x_label') }}</label>
                        <input type="number" class="form-control" id="auto-start-x" value="100" min="0" max="1920">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.auto_start_y_label') }}</label>
                        <input type="number" class="form-control" id="auto-start-y" value="400" min="0" max="1080">
                    </div>
                </div>
                
                <hr>
                
                <!-- Trường quay -->
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_random_source_label') }}</label>
                    <select class="form-select" id="auto-random-source">
                        <option value="name">Tên (name)</option>
                        <option value="phone">Số điện thoại (phone)</option>
                        <option value="email">Email</option>
                        <option value="qrcode">Mã QR/ID</option>
                    </select>
                </div>
                
                <!-- Trường kết quả -->
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_result_fields_label') }} <small>(tùy chọn)</small></label>
                    
                    <div class="form-check">
                        <input class="form-check-input auto-result-checkbox" type="checkbox" value="phone" id="auto-rf-phone">
                        <label class="form-check-label" for="auto-rf-phone">Số điện thoại</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input auto-result-checkbox" type="checkbox" value="email" id="auto-rf-email">
                        <label class="form-check-label" for="auto-rf-email">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input auto-result-checkbox" type="checkbox" value="qrcode" id="auto-rf-qrcode">
                        <label class="form-check-label" for="auto-rf-qrcode">Mã QR/ID</label>
                    </div>
                    
                    <div id="auto-custom-fields-list" class="mt-2"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_offset_label') }}</label>
                        <input type="number" class="form-control" id="auto-result-offset-y" value="70" min="20">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_spacing_label') }}</label>
                        <input type="number" class="form-control" id="auto-result-spacing" value="35" min="10">
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Chú ý:</strong> {{ __('lucky_draws.builder.auto_warning') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('lucky_draws.builder.modal_cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-auto-generate">
                    <i class="fas fa-magic"></i> {{ __('lucky_draws.builder.auto_confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Random Field Modal -->
<div class="modal fade" id="createRandomFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('lucky_draws.builder.create_random_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Trường quay -->
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.auto_random_source_label') }}</label>
                    <select class="form-select" id="random-field-source">
                        <option value="name">Tên (name)</option>
                        <option value="phone">Số điện thoại (phone)</option>
                        <option value="email">Email</option>
                        <option value="qrcode">Mã QR/ID</option>
                    </select>
                    <small class="text-muted">{{ __('lucky_draws.builder.create_random_hint') }}</small>
                </div>
                
                <hr>
                
                <!-- Trường kết quả -->
                <div class="mb-3">
                    <label class="form-label">{{ __('lucky_draws.builder.create_result_title') }} <small class="text-muted">(hiển thị khi dừng)</small></label>
                    
                    <div class="mb-2"><strong>{{ __('lucky_draws.builder.auto_result_basic_label') }}</strong></div>
                    <div class="form-check">
                        <input class="form-check-input result-field-checkbox" type="checkbox" value="name" id="rf-name">
                        <label class="form-check-label" for="rf-name">Tên (name)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input result-field-checkbox" type="checkbox" value="phone" id="rf-phone">
                        <label class="form-check-label" for="rf-phone">Số điện thoại (phone)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input result-field-checkbox" type="checkbox" value="email" id="rf-email">
                        <label class="form-check-label" for="rf-email">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input result-field-checkbox" type="checkbox" value="qrcode" id="rf-qrcode">
                        <label class="form-check-label" for="rf-qrcode">Mã QR/ID</label>
                    </div>
                    
                    <div class="mb-2 mt-3"><strong>Custom Fields</strong></div>
                    <div id="result-custom-fields-list"></div>
                </div>
                
                <hr>
                
                <!-- Cấu hình bố trí -->
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_width_label') }}</label>
                        <input type="number" class="form-control" id="rf-width" value="400" min="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_height_label') }}</label>
                        <input type="number" class="form-control" id="rf-height" value="60" min="30">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_offset_label') }}</label>
                        <input type="number" class="form-control" id="rf-offset-y" value="70" min="20">
                    </div>
                    <div class="col-6">
                        <label class="form-label">{{ __('lucky_draws.builder.create_spacing_label') }}</label>
                        <input type="number" class="form-control" id="rf-spacing" value="35" min="10">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('lucky_draws.builder.modal_cancel') }}</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-create-random">
                    <i class="fas fa-plus"></i> {{ __('lucky_draws.builder.create_confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('admin_js')
<script src="https://unpkg.com/konva@9/konva.min.js"></script>
<script>

// Simple event emitter
class EventEmitter {
    constructor() {
        this.events = {};
    }
    on(event, callback) {
        if (!this.events[event]) this.events[event] = [];
        this.events[event].push(callback);
    }
    emit(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(cb => cb(data));
        }
    }
}

// Block Types Configuration
const BLOCK_TYPES = {
    text: {
        name: 'Text',
        icon: 'fa-font',
        defaultWidth: 400,
        defaultHeight: 60,
        defaultStyle: {
            fontSize: 48,
            fontFamily: 'Arial',
            fontWeight: 'normal',
            color: '#FFFFFF',
            align: 'center',
        },
    },
    image: {
        name: 'Image',
        icon: 'fa-image',
        defaultWidth: 300,
        defaultHeight: 200,
        defaultStyle: {},
    },
    avatar: {
        name: 'Avatar',
        icon: 'fa-user-circle',
        defaultWidth: 200,
        defaultHeight: 200,
        defaultStyle: {
            cornerRadius: 100,
        },
    },
    random_field: {
        name: 'Random Field',
        icon: 'fa-random',
        defaultWidth: 400,
        defaultHeight: 60,
        defaultStyle: {
            fontSize: 48,
            fontFamily: 'Arial',
            fontWeight: 'bold',
            color: '#FFFF00',
            align: 'center',
        },
    },
    result_field: {
        name: 'Result Field',
        icon: 'fa-trophy',
        defaultWidth: 400,
        defaultHeight: 60,
        defaultStyle: {
            fontSize: 72,
            fontFamily: 'Arial',
            fontWeight: 'bold',
            color: '#00FF00',
            align: 'center',
        },
    },
};

// Create Block Factory
function createBlock(config) {
    const type = config.type;

    if (type === 'text' || type === 'random_field' || type === 'result_field') {
        return createTextBlock(config);
    } else if (type === 'image') {
        return createImageBlock(config);
    } else if (type === 'avatar') {
        return createAvatarBlock(config);
    }
    return createTextBlock(config);
}

function createTextBlock(config) {
    const group = new Konva.Group({
        x: config.x,
        y: config.y,
        width: config.width,
        height: config.height,
        rotation: config.rotation || 0,
        name: 'block',
        id: config.id,
    });

    const bgRect = new Konva.Rect({
        width: config.width,
        height: config.height,
        fill: 'transparent',
        stroke: config.type === 'random_field' ? '#FFFF00' : (config.type === 'result_field' ? '#00FF00' : 'transparent'),
        strokeWidth: 1,
        dash: [5, 5],
    });
    group.add(bgRect);

    const placeholder = config.content || getPlaceholderText(config);
    const text = new Konva.Text({
        width: config.width,
        height: config.height,
        text: placeholder,
        fontSize: config.style?.fontSize || 48,
        fontFamily: config.style?.fontFamily || 'Arial',
        fontStyle: config.style?.fontWeight || 'normal',
        fill: config.style?.color || '#FFFFFF',
        align: config.style?.align || 'center',
        verticalAlign: 'middle',
    });
    group.add(text);

    group.setAttr('blockConfig', config);
    return group;
}

function createImageBlock(config) {
    const group = new Konva.Group({
        x: config.x,
        y: config.y,
        width: config.width,
        height: config.height,
        rotation: config.rotation || 0,
        name: 'block',
        id: config.id,
    });

    const placeholder = new Konva.Rect({
        width: config.width,
        height: config.height,
        fill: '#333',
        stroke: '#666',
        strokeWidth: 2,
    });
    group.add(placeholder);

    if (config.imageUrl) {
        const imageObj = new Image();
        imageObj.onload = function() {
            const img = new Konva.Image({
                image: imageObj,
                width: config.width,
                height: config.height,
            });
            group.add(img);
            placeholder.destroy();
            group.getLayer()?.draw();
        };
        imageObj.src = config.imageUrl;
    } else {
        const icon = new Konva.Text({
            width: config.width,
            height: config.height,
            text: 'Image',
            fontSize: 24,
            fill: '#999',
            align: 'center',
            verticalAlign: 'middle',
        });
        group.add(icon);
    }

    group.setAttr('blockConfig', config);
    return group;
}

function createAvatarBlock(config) {
    const group = new Konva.Group({
        x: config.x,
        y: config.y,
        width: config.width,
        height: config.height,
        rotation: config.rotation || 0,
        name: 'block',
        id: config.id,
    });

    const radius = Math.min(config.width, config.height) / 2;
    const circle = new Konva.Circle({
        x: config.width / 2,
        y: config.height / 2,
        radius: radius,
        fill: '#555',
        stroke: '#888',
        strokeWidth: 3,
    });
    group.add(circle);

    const icon = new Konva.Text({
        width: config.width,
        height: config.height,
        text: 'Avatar',
        fontSize: 18,
        fill: '#aaa',
        align: 'center',
        verticalAlign: 'middle',
    });
    group.add(icon);

    group.setAttr('blockConfig', config);
    return group;
}

function getPlaceholderText(config) {
    if (config.source) {
        return `[${config.source}]`;
    }
    switch (config.type) {
        case 'random_field':
            return '[Spinning...]';
        case 'result_field':
            return '[Winner Name]';
        default:
            return 'Double-click to edit';
    }
}

// Resize all children of a block Group to match new width/height (so Transformer resize persists)
function resizeGroupChildren(group, width, height) {
    if (!group || !group.getChildren) return;
    group.getChildren().forEach(child => {
        const className = child.getClassName?.() || '';
        if (className === 'Rect' || child.width !== undefined) {
            child.width(width);
            child.height(height);
        }
        if (className === 'Text') {
            child.width(width);
            child.height(height);
        }
        if (className === 'Image') {
            child.width(width);
            child.height(height);
        }
        if (className === 'Circle') {
            const r = Math.min(width, height) / 2;
            child.radius(r);
            child.x(width / 2);
            child.y(height / 2);
        }
    });
}

// API Helper
const api = {
    async get(url) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },
    async put(url, data) {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },
    async delete(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },
};

// State Manager for undo/redo
class StateManager extends EventEmitter {
    constructor() {
        super();
        this.history = [];
        this.currentIndex = -1;
        this.maxHistory = 50;
    }

    saveState(state) {
        // Remove future states if we're not at the end
        if (this.currentIndex < this.history.length - 1) {
            this.history = this.history.slice(0, this.currentIndex + 1);
        }

        this.history.push(JSON.parse(JSON.stringify(state)));

        if (this.history.length > this.maxHistory) {
            this.history.shift();
        } else {
            this.currentIndex++;
        }

        this.emit('change');
    }

    undo() {
        if (this.canUndo()) {
            this.currentIndex--;
            this.emit('change');
            return this.getCurrentState();
        }
        return null;
    }

    redo() {
        if (this.canRedo()) {
            this.currentIndex++;
            this.emit('change');
            return this.getCurrentState();
        }
        return null;
    }

    canUndo() {
        return this.currentIndex > 0;
    }

    canRedo() {
        return this.currentIndex < this.history.length - 1;
    }

    getCurrentState() {
        return this.currentIndex >= 0 ? JSON.parse(JSON.stringify(this.history[this.currentIndex])) : null;
    }
}

// Canvas Manager
class CanvasManager {
    constructor(container, width, height, apiBase = null) {
        this.container = container;
        this.canvasWidth = width;
        this.canvasHeight = height;
        this.zoom = 1;
        this.apiBase = apiBase;

        this.stage = new Konva.Stage({
            container: container,
            width: container.offsetWidth,
            height: container.offsetHeight,
        });

        this.backgroundLayer = new Konva.Layer();
        this.stage.add(this.backgroundLayer);

        this.contentLayer = new Konva.Layer();
        this.stage.add(this.contentLayer);

        this.transformer = new Konva.Transformer({
            rotateEnabled: true,
            enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'middle-left', 'middle-right', 'top-center', 'bottom-center'],
            anchorSize: 10,
            anchorStroke: '#0066ff',
            anchorFill: '#fff',
            borderStroke: '#0066ff',
            boundBoxFunc: (oldBox, newBox) => {
                if (newBox.width < 10 || newBox.height < 10) {
                    return oldBox;
                }
                return newBox;
            }
        });
        this.contentLayer.add(this.transformer);

        this.backgroundRect = new Konva.Rect({
            x: 0,
            y: 0,
            width: this.canvasWidth,
            height: this.canvasHeight,
            fill: '#000000',
            listening: false,
        });
        this.backgroundLayer.add(this.backgroundRect);

        this.backgroundImage = null;

        this.canvasFrame = new Konva.Rect({
            x: 0,
            y: 0,
            width: this.canvasWidth,
            height: this.canvasHeight,
            stroke: '#ccc',
            strokeWidth: 2,
            listening: false,
            fill: 'transparent', // Ensure frame doesn't cover background
        });
        this.backgroundLayer.add(this.canvasFrame);

        this.setupStageEvents();
    }

    setupStageEvents() {
        this.stage.on('click tap', (e) => {
            if (e.target === this.stage || e.target === this.backgroundRect) {
                this.transformer.nodes([]);
                this.contentLayer.draw();
            }
        });
    }

    getContentLayer() {
        return this.contentLayer;
    }

    getTransformer() {
        return this.transformer;
    }

    getStage() {
        return this.stage;
    }

    setBackground(type, value) {
        if (type === 'color') {
            this.backgroundRect.fill(value);
            if (this.backgroundImage) {
                this.backgroundImage.destroy();
                this.backgroundImage = null;
            }
            this.backgroundLayer.draw();
            this.stage.draw();
        } else if (type === 'image') {
            if (!value || value.trim() === '') {
                console.warn('Empty image URL provided for background');
                return;
            }
            
            // Nếu value là URL proxy (đã lưu nhầm từ lần trước), lấy ra URL ảnh gốc để tránh proxy hai lần
            if (value && value.includes('proxy-image') && value.includes('url=')) {
                try {
                    const u = new URL(value);
                    const inner = u.searchParams.get('url');
                    if (inner) value = inner;
                } catch (e) { /* giữ value */ }
            }
            
            // Check if URL is from different domain (CORS issue)
            const currentOrigin = window.location.origin;
            let needsProxy = false;
            let imageSrc = value;
            
            console.log('Setting background image:', {
                originalUrl: value,
                currentOrigin: currentOrigin,
                apiBase: this.apiBase
            });
            
            try {
                const imageUrl = new URL(value, currentOrigin);
                needsProxy = imageUrl.origin !== currentOrigin && !value.startsWith('/');
                console.log('URL analysis:', {
                    imageOrigin: imageUrl.origin,
                    currentOrigin: currentOrigin,
                    needsProxy: needsProxy
                });
            } catch (e) {
                // If URL parsing fails, assume it's relative
                console.warn('URL parsing failed, assuming relative:', e);
                needsProxy = false;
            }
            
            // Always use proxy for external URLs to avoid CORS issues
            if (needsProxy && this.apiBase) {
                // Ensure apiBase doesn't end with slash
                const baseUrl = this.apiBase.endsWith('/') ? this.apiBase.slice(0, -1) : this.apiBase;
                imageSrc = `${baseUrl}/proxy-image?url=${encodeURIComponent(value)}`;
                console.log('Using proxy for image:', imageSrc);
            } else if (!needsProxy && value.startsWith('http')) {
                // Even for same origin, might need proxy if it's HTTPS from HTTP localhost
                const isHttpsFromHttp = value.startsWith('https://') && currentOrigin.startsWith('http://');
                if (isHttpsFromHttp && this.apiBase) {
                    const baseUrl = this.apiBase.endsWith('/') ? this.apiBase.slice(0, -1) : this.apiBase;
                    imageSrc = `${baseUrl}/proxy-image?url=${encodeURIComponent(value)}`;
                    console.log('Using proxy for HTTPS from HTTP:', imageSrc);
                }
            }
            
            console.log('Final image source:', imageSrc);
            
            const imageObj = new Image();
            // Remove crossOrigin when using proxy (same origin)
            if (!needsProxy && !imageSrc.includes('/proxy-image')) {
                imageObj.crossOrigin = 'anonymous';
            }
            
            imageObj.onload = () => {
                console.log('Image loaded, creating Konva.Image node');
                if (this.backgroundImage) {
                    this.backgroundImage.destroy();
                }
                this.backgroundImage = new Konva.Image({
                    x: 0,
                    y: 0,
                    width: this.canvasWidth,
                    height: this.canvasHeight,
                    image: imageObj,
                    listening: false,
                });
                
                // Add image to layer
                this.backgroundLayer.add(this.backgroundImage);
                
                // Ensure proper z-order: backgroundRect -> backgroundImage -> canvasFrame
                this.backgroundImage.moveToBottom();
                this.backgroundRect.moveToBottom();
                this.canvasFrame.moveToTop();
                
                // Make backgroundRect transparent when image is present
                this.backgroundRect.fill('transparent');
                
                // Draw all layers
                this.backgroundLayer.draw();
                this.stage.draw();
                
                console.log('Background image loaded successfully:', {
                    url: value,
                    imageSize: { width: imageObj.width, height: imageObj.height },
                    canvasSize: { width: this.canvasWidth, height: this.canvasHeight }
                });
            };
            
            imageObj.onerror = (error) => {
                console.error('Failed to load background image:', {
                    originalUrl: value,
                    imageSrc: imageSrc,
                    error: error,
                    imageObj: imageObj
                });
                // Fallback to black background if image fails to load
                this.backgroundRect.fill('#000000');
                this.backgroundLayer.draw();
            };
            
            imageObj.src = imageSrc;
            this.backgroundLayer.draw(); // Draw immediately to show loading state
        }
    }

    getBackground() {
        let value = this.backgroundImage ? this.backgroundImage.image().src : this.backgroundRect.fill();
        // Luôn lưu URL ảnh gốc, không lưu URL proxy (để khi mở builder từ domain khác vẫn load đúng)
        if (this.backgroundImage && value && value.includes('proxy-image')) {
            try {
                const u = new URL(value);
                const urlParam = u.searchParams.get('url');
                if (urlParam) value = urlParam;
            } catch (e) { /* giữ value */ }
        }
        return {
            type: this.backgroundImage ? 'image' : 'color',
            value: value
        };
    }

    fitToContainer() {
        const containerWidth = this.container.offsetWidth;
        const containerHeight = this.container.offsetHeight;

        const scaleX = containerWidth / this.canvasWidth;
        const scaleY = containerHeight / this.canvasHeight;
        const scale = Math.min(scaleX, scaleY, 1) * 0.9;

        this.zoom = scale;

        const offsetX = (containerWidth - this.canvasWidth * scale) / 2;
        const offsetY = (containerHeight - this.canvasHeight * scale) / 2;

        this.stage.scale({ x: scale, y: scale });
        this.stage.position({ x: offsetX, y: offsetY });
        this.stage.width(containerWidth);
        this.stage.height(containerHeight);
        this.stage.batchDraw();
    }

    zoomIn() {
        this.zoom = Math.min(this.zoom * 1.2, 2);
        this.applyZoom();
    }

    zoomOut() {
        this.zoom = Math.max(this.zoom / 1.2, 0.2);
        this.applyZoom();
    }

    applyZoom() {
        const containerWidth = this.container.offsetWidth;
        const containerHeight = this.container.offsetHeight;

        const offsetX = (containerWidth - this.canvasWidth * this.zoom) / 2;
        const offsetY = (containerHeight - this.canvasHeight * this.zoom) / 2;

        this.stage.scale({ x: this.zoom, y: this.zoom });
        this.stage.position({ x: offsetX, y: offsetY });
        this.stage.batchDraw();
    }

    getZoom() {
        return this.zoom;
    }

    getPointerPosition(event) {
        const transform = this.stage.getAbsoluteTransform().copy();
        transform.invert();
        const pos = this.stage.getPointerPosition();
        return transform.point(pos);
    }
}

// Block Manager
class BlockManager extends EventEmitter {
    constructor(canvasManager, stateManager) {
        super();
        this.canvasManager = canvasManager;
        this.stateManager = stateManager;
        this.layer = canvasManager.getContentLayer();
        this.transformer = canvasManager.getTransformer();
        this.blocks = new Map();
        this.selectedBlocks = new Set(); // Multi-selection support
        this.selectionRect = null; // For box selection
        this.isBoxSelecting = false;
    }

    addBlock(type, config = {}) {
        const id = config.id || `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const typeConfig = BLOCK_TYPES[type] || BLOCK_TYPES.text;

        const defaultConfig = {
            id,
            type,
            x: config.x || 960,
            y: config.y || 540,
            width: config.width || typeConfig.defaultWidth,
            height: config.height || typeConfig.defaultHeight,
            rotation: config.rotation || 0,
            source: config.source || null,
            style: { ...typeConfig.defaultStyle, ...config.style },
            animation: config.animation || { type: 'none', duration: 500 },
            visibleWhen: config.visibleWhen || 'always',
            zIndex: config.zIndex || this.blocks.size + 1,
            locked: config.locked || false,
            content: config.content || '',
            imageUrl: config.imageUrl || '',
        };

        const konvaNode = createBlock(defaultConfig);
        konvaNode.draggable(!defaultConfig.locked);

        konvaNode.on('click tap', (e) => {
            const ctrlKey = e.evt?.ctrlKey || e.evt?.metaKey; // Support Ctrl/Cmd
            this.selectBlock(id, ctrlKey);
        });

        // Store initial positions for dragging
        let dragStartPositions = new Map();
        
        konvaNode.on('dragstart', () => {
            // Store positions for all selected blocks
            this.selectedBlocks.forEach(selectedId => {
                const block = this.blocks.get(selectedId);
                if (block) {
                    dragStartPositions.set(selectedId, {
                        x: block.konvaNode.x(),
                        y: block.konvaNode.y()
                    });
                }
            });
            
            // Also include grouped blocks if slotIndex exists
            if (defaultConfig.slotIndex !== undefined) {
                this.blocks.forEach((block, blockId) => {
                    if (block.config.slotIndex === defaultConfig.slotIndex && 
                        !dragStartPositions.has(blockId)) {
                        dragStartPositions.set(blockId, {
                            x: block.konvaNode.x(),
                            y: block.konvaNode.y()
                        });
                    }
                });
            }
        });

        konvaNode.on('dragmove', () => {
            if (dragStartPositions.size === 0) return;
            
            const startPos = dragStartPositions.get(id);
            if (!startPos) return;
            
            // Calculate the delta movement
            const deltaX = konvaNode.x() - startPos.x;
            const deltaY = konvaNode.y() - startPos.y;
            
            // Move all blocks in dragStartPositions (selected + grouped)
            dragStartPositions.forEach((pos, blockId) => {
                if (blockId !== id) {
                    const block = this.blocks.get(blockId);
                    if (block) {
                        block.konvaNode.x(pos.x + deltaX);
                        block.konvaNode.y(pos.y + deltaY);
                    }
                }
            });
            
            this.layer.draw();
        });

        konvaNode.on('dragend', () => {
            // Update positions for all moved blocks
            dragStartPositions.forEach((pos, blockId) => {
                const block = this.blocks.get(blockId);
                if (block) {
                    this.updateBlockConfig(blockId, {
                        x: block.konvaNode.x(),
                        y: block.konvaNode.y(),
                    });
                }
            });
            
            dragStartPositions.clear();
            this.saveState();
        });

        konvaNode.on('transformend', () => {
            const scaleX = konvaNode.scaleX();
            const scaleY = konvaNode.scaleY();
            const newWidth = Math.max(10, konvaNode.width() * scaleX);
            const newHeight = Math.max(10, konvaNode.height() * scaleY);
            const rotation = konvaNode.rotation();
            
            // Get the original position before transform
            const pivotX = defaultConfig.x;
            const pivotY = defaultConfig.y;

            this.updateBlockConfig(id, {
                x: konvaNode.x(),
                y: konvaNode.y(),
                width: newWidth,
                height: newHeight,
                rotation: rotation,
            });

            konvaNode.scaleX(1);
            konvaNode.scaleY(1);
            resizeGroupChildren(konvaNode, newWidth, newHeight);
            
            // Transform all grouped blocks
            if (defaultConfig.slotIndex !== undefined) {
                this.blocks.forEach((block, blockId) => {
                    if (blockId !== id && 
                        block.config.slotIndex === defaultConfig.slotIndex) {
                        
                        // Calculate relative position from pivot
                        const relX = block.config.x - pivotX;
                        const relY = block.config.y - pivotY;
                        
                        // Apply scale to the block's size
                        const blockNewWidth = Math.max(10, block.config.width * scaleX);
                        const blockNewHeight = Math.max(10, block.config.height * scaleY);
                        
                        // Apply scale to relative position
                        const newRelX = relX * scaleX;
                        const newRelY = relY * scaleY;
                        
                        // Calculate new absolute position
                        const blockNewX = konvaNode.x() + newRelX;
                        const blockNewY = konvaNode.y() + newRelY;
                        
                        // Update block
                        block.konvaNode.x(blockNewX);
                        block.konvaNode.y(blockNewY);
                        block.konvaNode.width(blockNewWidth);
                        block.konvaNode.height(blockNewHeight);
                        block.konvaNode.rotation(rotation);
                        block.konvaNode.scaleX(1);
                        block.konvaNode.scaleY(1);
                        
                        this.updateBlockConfig(blockId, {
                            x: blockNewX,
                            y: blockNewY,
                            width: blockNewWidth,
                            height: blockNewHeight,
                            rotation: rotation,
                        });
                        
                        resizeGroupChildren(block.konvaNode, blockNewWidth, blockNewHeight);
                    }
                });
            }
            
            this.layer.draw();
            this.saveState();
        });

        this.layer.add(konvaNode);
        this.blocks.set(id, { konvaNode, config: defaultConfig });
        this.layer.draw();

        this.selectBlock(id);
        this.saveState();

        return id;
    }

    addFieldBlock(fieldKey, fieldType, position) {
        const blockType = fieldType === 'image' ? 'avatar' : 'result_field';
        return this.addBlock(blockType, {
            x: position.x,
            y: position.y,
            source: fieldKey,
        });
    }

    selectBlock(id, addToSelection = false) {
        const block = this.blocks.get(id);
        if (!block) return;

        if (addToSelection) {
            // Ctrl+Click: Toggle selection
            if (this.selectedBlocks.has(id)) {
                this.selectedBlocks.delete(id);
            } else {
                this.selectedBlocks.add(id);
            }
        } else {
            // Normal click: Single selection
            this.selectedBlocks.clear();
            this.selectedBlocks.add(id);
        }

        // Update transformer to show all selected blocks
        const selectedNodes = [];
        this.selectedBlocks.forEach(selectedId => {
            const selectedBlock = this.blocks.get(selectedId);
            if (selectedBlock) {
                selectedNodes.push(selectedBlock.konvaNode);
            }
        });
        
        this.transformer.nodes(selectedNodes);
        this.layer.draw();

        // Highlight grouped blocks for the primary selection
        this.highlightGroupedBlocks(block.config.slotIndex);

        // Emit event with first selected block's config
        const firstBlock = this.blocks.get(Array.from(this.selectedBlocks)[0]);
        if (firstBlock) {
            this.emit('select', firstBlock.config);
        }
    }

    deselectAll() {
        this.selectedBlocks.clear();
        this.transformer.nodes([]);
        
        // Clear group highlights
        this.highlightGroupedBlocks(null);
        
        this.layer.draw();
        this.emit('deselect');
    }

    /**
     * Hiển thị visual indicator cho các blocks có cùng slotIndex
     */
    highlightGroupedBlocks(slotIndex) {
        // Clear previous highlights
        this.layer.find('.group-indicator').forEach(node => node.destroy());
        
        if (slotIndex === null || slotIndex === undefined) {
            this.layer.draw();
            return;
        }
        
        // Tìm tất cả blocks có cùng slotIndex
        const groupedBlocks = Array.from(this.blocks.values())
            .filter(b => b.config.slotIndex === slotIndex)
            .map(b => b.konvaNode);
        
        if (groupedBlocks.length <= 1) {
            this.layer.draw();
            return;
        }
        
        // Tính bounding box của group
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        groupedBlocks.forEach(node => {
            const box = node.getClientRect();
            minX = Math.min(minX, box.x);
            minY = Math.min(minY, box.y);
            maxX = Math.max(maxX, box.x + box.width);
            maxY = Math.max(maxY, box.y + box.height);
        });
        
        // Vẽ hình chữ nhật highlight
        const indicator = new Konva.Rect({
            x: minX - 10,
            y: minY - 10,
            width: maxX - minX + 20,
            height: maxY - minY + 20,
            stroke: '#00BFFF',
            strokeWidth: 2,
            dash: [10, 5],
            name: 'group-indicator',
            listening: false,
        });
        
        this.layer.add(indicator);
        indicator.moveToBottom();
        this.layer.draw();
    }

    deleteBlock(id) {
        const block = this.blocks.get(id);
        if (!block) return;

        block.konvaNode.destroy();
        this.blocks.delete(id);

        if (this.selectedBlocks.has(id)) {
            this.selectedBlocks.delete(id);
            if (this.selectedBlocks.size === 0) {
                this.deselectAll();
            }
        }

        this.layer.draw();
        this.saveState();
    }

    deleteSelected() {
        if (this.selectedBlocks.size === 0) return;
        
        const blocksToDelete = new Set();
        
        // Collect all blocks to delete (selected + their groups)
        this.selectedBlocks.forEach(selectedId => {
            const block = this.blocks.get(selectedId);
            if (!block) return;
            
            blocksToDelete.add(selectedId);
            
            // If block has slotIndex, include all blocks in the group
            if (block.config.slotIndex !== undefined) {
                this.blocks.forEach((b, blockId) => {
                    if (b.config.slotIndex === block.config.slotIndex) {
                        blocksToDelete.add(blockId);
                    }
                });
            }
        });
        
        // Delete all collected blocks
        blocksToDelete.forEach(blockId => {
            this.deleteBlock(blockId);
        });
        
        toastr.info(`Đã xóa ${blocksToDelete.size} ô`);
        this.deselectAll();
    }

    duplicateSelected() {
        if (this.selectedBlocks.size === 0) return;

        const blocksToDuplicate = [];
        const processedSlotIndexes = new Set();
        
        // Collect all blocks to duplicate (selected + their groups)
        this.selectedBlocks.forEach(selectedId => {
            const block = this.blocks.get(selectedId);
            if (!block) return;
            
            // If block has slotIndex, duplicate the entire group once
            if (block.config.slotIndex !== undefined) {
                if (!processedSlotIndexes.has(block.config.slotIndex)) {
                    processedSlotIndexes.add(block.config.slotIndex);
                    const newSlotIndex = Date.now() + processedSlotIndexes.size;
                    
                    this.blocks.forEach((b, blockId) => {
                        if (b.config.slotIndex === block.config.slotIndex) {
                            blocksToDuplicate.push({
                                config: b.config,
                                newSlotIndex: newSlotIndex
                            });
                        }
                    });
                }
            } else {
                // Single block without slotIndex
                blocksToDuplicate.push({
                    config: block.config,
                    newSlotIndex: undefined
                });
            }
        });
        
        // Duplicate all collected blocks
        blocksToDuplicate.forEach(item => {
            const newConfig = { ...item.config };
            delete newConfig.id;
            newConfig.x += 20;
            newConfig.y += 20;
            if (item.newSlotIndex !== undefined) {
                newConfig.slotIndex = item.newSlotIndex;
            }
            
            this.addBlock(newConfig.type, newConfig);
        });
        
        toastr.success(`Đã nhân bản ${blocksToDuplicate.length} ô`);
    }

    updateBlockConfig(id, updates) {
        const block = this.blocks.get(id);
        if (!block) return;

        Object.assign(block.config, updates);

        const node = block.konvaNode;
        if (updates.x !== undefined) node.x(updates.x);
        if (updates.y !== undefined) node.y(updates.y);
        if (updates.width !== undefined) node.width(updates.width);
        if (updates.height !== undefined) node.height(updates.height);
        if (updates.rotation !== undefined) node.rotation(updates.rotation);

        if (updates.width !== undefined || updates.height !== undefined) {
            const w = node.width();
            const h = node.height();
            resizeGroupChildren(node, w, h);
        }

        // Update text node if source field changes
        if (updates.source !== undefined) {
            const textNode = node.findOne('Text');
            if (textNode) {
                const placeholder = updates.source ? `[${updates.source}]` : getPlaceholderText(block.config);
                textNode.text(placeholder);
            }
        }

        if (updates.style) {
            Object.assign(block.config.style, updates.style);
            const textNode = node.findOne('Text');
            if (textNode) {
                if (updates.style.fontSize) textNode.fontSize(updates.style.fontSize);
                if (updates.style.color) textNode.fill(updates.style.color);
                if (updates.style.align) textNode.align(updates.style.align);
            }
        }

        this.layer.draw();
    }

    getBlockConfig(id) {
        const block = this.blocks.get(id);
        return block ? block.config : null;
    }

    getSelectedBlockConfig() {
        // Return config of first selected block
        const firstId = Array.from(this.selectedBlocks)[0];
        return firstId ? this.getBlockConfig(firstId) : null;
    }

    updateSelectedBlock(updates) {
        // Update all selected blocks
        this.selectedBlocks.forEach(id => {
            this.updateBlockConfig(id, updates);
        });
        if (this.selectedBlocks.size > 0) {
            this.saveState();
        }
    }

    bringToFront(id) {
        if (id) {
            const block = this.blocks.get(id);
            if (block) {
                block.konvaNode.moveToTop();
            }
        } else {
            // Move all selected blocks to front
            this.selectedBlocks.forEach(selectedId => {
                const block = this.blocks.get(selectedId);
                if (block) {
                    block.konvaNode.moveToTop();
                }
            });
        }
        this.transformer.moveToTop();
        this.layer.draw();
        this.saveState();
    }

    sendToBack(id) {
        if (id) {
            const block = this.blocks.get(id);
            if (block) {
                block.konvaNode.moveToBottom();
            }
        } else {
            // Move all selected blocks to back
            this.selectedBlocks.forEach(selectedId => {
                const block = this.blocks.get(selectedId);
                if (block) {
                    block.konvaNode.moveToBottom();
                }
            });
        }
        this.layer.draw();
        this.saveState();
    }

    loadBlocks(blocksConfig) {
        this.blocks.forEach(block => block.konvaNode.destroy());
        this.blocks.clear();
        this.deselectAll();

        (blocksConfig || []).forEach(config => {
            this.addBlock(config.type, config);
        });

        this.deselectAll();
    }

    /**
     * Đếm số block theo type (không đồng bộ vị trí)
     */
    getBlockCountByType(type) {
        return Array.from(this.blocks.values()).filter(b => b.config.type === type).length;
    }

    getBlocks() {
        // Đồng bộ vị trí/kích thước hiện tại từ Konva vào config trước khi trả về (đảm bảo lưu đúng vị trí)
        this.blocks.forEach((block) => {
            const node = block.konvaNode;
            if (node) {
                block.config.x = node.x();
                block.config.y = node.y();
                block.config.width = node.width();
                block.config.height = node.height();
                block.config.rotation = node.rotation();
            }
        });
        return Array.from(this.blocks.values()).map(b => ({ ...b.config }));
    }

    getState() {
        return {
            blocks: this.getBlocks(),
        };
    }

    loadFromState(state) {
        if (state && state.blocks) {
            this.loadBlocks(state.blocks);
        }
    }

    saveState() {
        this.stateManager.saveState(this.getState());
    }
}

// Main Builder Class
class LuckyDrawBuilder {
    constructor(container) {
        this.container = container;
        this.luckyDrawId = container.dataset.luckyDrawId;
        this.apiBase = container.dataset.apiBase;

        this.stateManager = new StateManager();
        this.canvasManager = null;
        this.blockManager = null;

        this.currentLayoutId = null;
        this.currentRewardId = null;
        this.currentRewardValue = null; // Số lượng slots tối đa cho reward hiện tại

        this.init();
    }

    async init() {
        this.canvasManager = new CanvasManager(
            document.getElementById('canvas-container'),
            1920,
            1080,
            this.apiBase
        );

        this.blockManager = new BlockManager(this.canvasManager, this.stateManager);

        this.setupEventListeners();

        // Load layout của tab đầu tiên (giải đầu tiên) hoặc default nếu không có giải
        const firstTab = document.querySelector('#layout-tabs .nav-link.active');
        if (firstTab) {
            const layoutId = firstTab.dataset.layoutId || null;
            const rewardId = firstTab.dataset.rewardId || null;
            const rewardImg = firstTab.dataset.rewardImg || null;
            const rewardValue = parseInt(firstTab.dataset.rewardValue) || null;
            
            // Lưu reward value
            this.currentRewardValue = rewardValue;
            
            await this.loadLayout(layoutId || 'default', rewardId || null, rewardImg || null);
        } else {
            await this.loadLayout('default');
        }

        this.canvasManager.fitToContainer();
    }

    setupEventListeners() {
        // Block tools
        document.querySelectorAll('.block-tool').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.blockType;
                
                if (type === 'random_field') {
                    // Mở modal thay vì tạo ngay
                    this.openCreateRandomFieldModal();
                } else {
                    // Các loại block khác tạo như cũ
                    this.blockManager.addBlock(type);
                }
            });
        });

        // Drag fields from left panel
        document.querySelectorAll('.draggable-field').forEach(field => {
            field.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', JSON.stringify({
                    fieldKey: field.dataset.fieldKey,
                    fieldType: field.dataset.fieldType
                }));
            });
        });

        // Canvas drop zone
        const canvasContainer = document.getElementById('canvas-container');
        canvasContainer.addEventListener('dragover', (e) => e.preventDefault());
        canvasContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            const pos = this.canvasManager.getPointerPosition(e);
            this.blockManager.addFieldBlock(data.fieldKey, data.fieldType, pos);
        });

        // Box selection on canvas
        this.setupBoxSelection();

        // Block selection
        this.blockManager.on('select', (block) => {
            this.showPropertiesPanel(block);
            document.getElementById('btn-delete-block').disabled = false;
            document.getElementById('btn-duplicate-block').disabled = false;
        });

        this.blockManager.on('deselect', () => {
            this.hidePropertiesPanel();
            document.getElementById('btn-delete-block').disabled = true;
            document.getElementById('btn-duplicate-block').disabled = true;
        });

        // Delete block
        document.getElementById('btn-delete-block').addEventListener('click', () => {
            this.blockManager.deleteSelected();
            this.updateRandomFieldButtonState();
        });

        // Duplicate block
        document.getElementById('btn-duplicate-block').addEventListener('click', () => {
            this.blockManager.duplicateSelected();
        });

        // Close properties
        document.getElementById('btn-close-properties').addEventListener('click', () => {
            this.blockManager.deselectAll();
        });

        // Layer buttons
        document.getElementById('btn-bring-front').addEventListener('click', () => {
            this.blockManager.bringToFront();
        });

        document.getElementById('btn-send-back').addEventListener('click', () => {
            this.blockManager.sendToBack();
        });

        // Layout tabs
        document.querySelectorAll('#layout-tabs .nav-link').forEach(tab => {
            tab.addEventListener('click', async (e) => {
                e.preventDefault();
                document.querySelectorAll('#layout-tabs .nav-link').forEach(t => t.classList.remove('active'));
                e.currentTarget.classList.add('active');

                const rewardId = e.currentTarget.dataset.rewardId || null;
                const layoutId = e.currentTarget.dataset.layoutId || null;
                const rewardImg = e.currentTarget.dataset.rewardImg || null;
                const rewardValue = parseInt(e.currentTarget.dataset.rewardValue) || null;
                
                // Lưu reward value
                this.currentRewardValue = rewardValue;
                
                console.log('Tab clicked:', {
                    rewardId,
                    layoutId,
                    rewardImg,
                    rewardValue,
                    tabElement: e.currentTarget
                });
                
                await this.loadLayout(layoutId, rewardId, rewardImg);
            });
        });

        // Save button
        document.getElementById('btn-save').addEventListener('click', async () => {
            await this.saveLayout();
        });

        // Preview button
        document.getElementById('btn-preview').addEventListener('click', () => {
            this.openPreview();
        });

        // Background button
        document.getElementById('btn-background').addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('backgroundModal'));
            const bg = this.canvasManager.getBackground();
            document.getElementById('bg-type').value = bg.type;
            if (bg.type === 'color') {
                document.getElementById('bg-color').value = bg.value;
            } else {
                document.getElementById('bg-image-url').value = bg.value;
            }
            this.toggleBgInputs(bg.type);
            modal.show();
        });

        document.getElementById('bg-type').addEventListener('change', (e) => {
            this.toggleBgInputs(e.target.value);
        });

        document.getElementById('btn-apply-bg').addEventListener('click', () => {
            const type = document.getElementById('bg-type').value;
            const value = type === 'color'
                ? document.getElementById('bg-color').value
                : document.getElementById('bg-image-url').value;
            this.canvasManager.setBackground(type, value);
            bootstrap.Modal.getInstance(document.getElementById('backgroundModal')).hide();
        });

        // Auto Generate Slots
        document.querySelectorAll('.btn-auto-generate').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const rewardId = e.currentTarget.dataset.rewardId;
                const rewardValue = parseInt(e.currentTarget.dataset.rewardValue) || 1;
                
                // Lưu reward value để validation sau này
                this.currentRewardValue = rewardValue;
                
                document.getElementById('auto-gen-reward-id').value = rewardId;
                document.getElementById('auto-gen-slot-count').value = rewardValue;
                document.getElementById('slot-count-display').textContent = rewardValue;
                
                // Populate custom fields
                this.populateCustomFieldsForModal('auto-custom-fields-list', 'auto-result-checkbox');
                this.populateRandomSourceDropdown('auto-random-source');
                
                // Reset checkboxes
                document.querySelectorAll('.auto-result-checkbox').forEach(cb => cb.checked = false);
                
                const modal = new bootstrap.Modal(document.getElementById('autoGenerateModal'));
                modal.show();
            });
        });

        document.getElementById('auto-layout-type').addEventListener('change', (e) => {
            const gridColsGroup = document.getElementById('grid-cols-group');
            gridColsGroup.style.display = e.target.value === 'grid' ? 'block' : 'none';
        });

        document.getElementById('btn-confirm-auto-generate').addEventListener('click', () => {
            const rewardId = document.getElementById('auto-gen-reward-id').value;
            const slotCount = parseInt(document.getElementById('auto-gen-slot-count').value);
            
            // Kiểm tra số lượng slots không vượt quá reward value
            if (this.currentRewardValue !== null && slotCount > this.currentRewardValue) {
                toastr.error(`Số lượng ô không được vượt quá ${this.currentRewardValue} (giá trị của giải này)`);
                return;
            }
            
            // Lấy trường quay và kết quả
            const randomSource = document.getElementById('auto-random-source').value;
            
            const resultSources = [];
            document.querySelectorAll('.auto-result-checkbox:checked').forEach(cb => {
                resultSources.push(cb.value);
            });
            
            const layoutConfig = {
                type: document.getElementById('auto-layout-type').value,
                cols: parseInt(document.getElementById('auto-grid-cols').value),
                width: parseInt(document.getElementById('auto-slot-width').value),
                height: parseInt(document.getElementById('auto-slot-height').value),
                spacing: parseInt(document.getElementById('auto-spacing').value),
                startX: parseInt(document.getElementById('auto-start-x').value),
                startY: parseInt(document.getElementById('auto-start-y').value),
                randomSource,
                resultSources,
                resultOffsetY: parseInt(document.getElementById('auto-result-offset-y').value),
                resultSpacing: parseInt(document.getElementById('auto-result-spacing').value),
            };
            
            this.generateAutoSlots(slotCount, layoutConfig);
            
            bootstrap.Modal.getInstance(document.getElementById('autoGenerateModal')).hide();
            
            this.updateRandomFieldButtonState();
            const totalFields = slotCount * (1 + resultSources.length);
            toastr.success(`Đã tạo ${slotCount} khối với tổng ${totalFields} ô`);
        });

        // Create Random Field Modal
        document.getElementById('btn-confirm-create-random').addEventListener('click', () => {
            const randomSource = document.getElementById('random-field-source').value;
            
            const resultSources = [];
            document.querySelectorAll('.result-field-checkbox:checked').forEach(cb => {
                resultSources.push(cb.value);
            });
            
            const config = {
                x: 960, // Center
                y: 540,
                width: parseInt(document.getElementById('rf-width').value),
                height: parseInt(document.getElementById('rf-height').value),
                randomSource,
                resultSources,
                offsetY: parseInt(document.getElementById('rf-offset-y').value),
                spacing: parseInt(document.getElementById('rf-spacing').value),
            };
            
            this.createRandomFieldWithResults(config);
            
            bootstrap.Modal.getInstance(document.getElementById('createRandomFieldModal')).hide();
            this.updateRandomFieldButtonState();
        });

        // Undo/Redo
        document.getElementById('btn-undo').addEventListener('click', () => {
            const state = this.stateManager.undo();
            if (state) this.blockManager.loadFromState(state);
        });

        document.getElementById('btn-redo').addEventListener('click', () => {
            const state = this.stateManager.redo();
            if (state) this.blockManager.loadFromState(state);
        });

        this.stateManager.on('change', () => {
            document.getElementById('btn-undo').disabled = !this.stateManager.canUndo();
            document.getElementById('btn-redo').disabled = !this.stateManager.canRedo();
        });

        // Zoom controls
        document.getElementById('btn-zoom-in').addEventListener('click', () => {
            this.canvasManager.zoomIn();
            this.updateZoomLevel();
        });

        document.getElementById('btn-zoom-out').addEventListener('click', () => {
            this.canvasManager.zoomOut();
            this.updateZoomLevel();
        });

        document.getElementById('btn-zoom-fit').addEventListener('click', () => {
            this.canvasManager.fitToContainer();
            this.updateZoomLevel();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    this.blockManager.deleteSelected();
                    this.updateRandomFieldButtonState();
                }
            }
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    const state = this.stateManager.undo();
                    if (state) this.blockManager.loadFromState(state);
                }
                if (e.key === 'y' || (e.shiftKey && e.key === 'z')) {
                    e.preventDefault();
                    const state = this.stateManager.redo();
                    if (state) this.blockManager.loadFromState(state);
                }
                if (e.key === 's') {
                    e.preventDefault();
                    this.saveLayout();
                }
            }
        });

        // Window resize
        window.addEventListener('resize', () => {
            this.canvasManager.fitToContainer();
        });

        // Properties form changes
        document.getElementById('properties-form').addEventListener('change', (e) => {
            const name = e.target.name;
            let value = e.target.type === 'number' ? parseFloat(e.target.value) : e.target.value;

            if (name.startsWith('style.')) {
                const styleProp = name.replace('style.', '');
                this.blockManager.updateSelectedBlock({ style: { [styleProp]: value } });
            } else if (name === 'source') {
                // When source field changes, updateSelectedBlock will handle text update
                this.blockManager.updateSelectedBlock({ [name]: value });
            } else {
                this.blockManager.updateSelectedBlock({ [name]: value });
            }
        });
    }

    toggleBgInputs(type) {
        document.getElementById('bg-color-group').style.display = type === 'color' ? 'block' : 'none';
        document.getElementById('bg-image-group').style.display = type === 'image' ? 'block' : 'none';
    }

    showPropertiesPanel(blockConfig) {
        const panel = document.getElementById('properties-panel');
        const form = document.getElementById('properties-form');

        form.querySelector('[name="x"]').value = Math.round(blockConfig.x);
        form.querySelector('[name="y"]').value = Math.round(blockConfig.y);
        form.querySelector('[name="width"]').value = Math.round(blockConfig.width);
        form.querySelector('[name="height"]').value = Math.round(blockConfig.height);
        form.querySelector('[name="visibleWhen"]').value = blockConfig.visibleWhen || 'always';

        const isTextBlock = ['text', 'random_field', 'result_field'].includes(blockConfig.type);
        const isImageBlock = ['image', 'avatar'].includes(blockConfig.type);
        const isFieldBlock = ['random_field', 'result_field', 'avatar'].includes(blockConfig.type);

        document.querySelector('.text-style-group').style.display = isTextBlock ? 'block' : 'none';
        document.querySelector('.image-url-group').style.display = isImageBlock ? 'block' : 'none';
        document.querySelector('.source-field-group').style.display = isFieldBlock ? 'block' : 'none';

        if (isTextBlock && blockConfig.style) {
            form.querySelector('[name="style.fontSize"]').value = blockConfig.style.fontSize || 48;
            form.querySelector('[name="style.color"]').value = blockConfig.style.color || '#FFFFFF';
            form.querySelector('[name="style.align"]').value = blockConfig.style.align || 'center';
        }

        if (isImageBlock) {
            form.querySelector('[name="imageUrl"]').value = blockConfig.imageUrl || '';
        }

        // Populate source field dropdown
        if (isFieldBlock) {
            this.populateSourceFieldDropdown(form, blockConfig.source);
        }

        // Hiển thị slotIndex nếu có
        const slotIndexGroup = form.querySelector('.slot-index-group');
        if (blockConfig.slotIndex !== null && blockConfig.slotIndex !== undefined) {
            slotIndexGroup.style.display = 'block';
            form.querySelector('[name="slotIndex"]').value = blockConfig.slotIndex;
        } else {
            slotIndexGroup.style.display = 'none';
        }

        panel.style.display = 'block';
    }

    populateSourceFieldDropdown(form, currentValue = '') {
        const select = form.querySelector('[name="source"]');
        if (!select) return;

        // Clear existing options except the first one
        select.innerHTML = '<option value="">Chọn trường...</option>';

        // Get all available fields from the field list
        const fieldItems = document.querySelectorAll('#field-list .draggable-field');
        fieldItems.forEach(fieldItem => {
            const fieldKey = fieldItem.dataset.fieldKey;
            const fieldLabel = fieldItem.querySelector('span').textContent.trim();
            const fieldType = fieldItem.dataset.fieldType;

            const option = document.createElement('option');
            option.value = fieldKey;
            option.textContent = `${fieldLabel} (${fieldType})`;
            if (fieldKey === currentValue) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        console.log('Populated source field dropdown:', {
            optionsCount: select.options.length - 1,
            currentValue: currentValue
        });
    }

    hidePropertiesPanel() {
        document.getElementById('properties-panel').style.display = 'none';
    }

    async loadLayout(layoutId, rewardId = null, rewardImg = null) {
        this.currentLayoutId = layoutId;
        this.currentRewardId = rewardId;

        try {
            let backgroundType = 'color';
            let backgroundValue = '#000000';
            let layoutHasImage = false;

            if (layoutId === 'default' || !layoutId || layoutId === '') {
                const response = await api.get(`${this.apiBase}/layouts/default`);
                this.blockManager.loadBlocks(response.layout?.blocks || []);
                if (response.layout && response.layout.id) {
                    backgroundType = response.layout.background_type || 'color';
                    backgroundValue = response.layout.background_value || '#000000';
                    layoutHasImage = backgroundType === 'image' && backgroundValue && backgroundValue !== '#000000';
                    this.currentLayoutId = response.layout.id;
                } else {
                    this.currentLayoutId = 'default';
                }
            } else {
                const response = await api.get(`${this.apiBase}/layouts/${layoutId}`);
                this.blockManager.loadBlocks(response.layout?.blocks || []);
                backgroundType = response.layout?.background_type || 'color';
                backgroundValue = response.layout?.background_value || '#000000';
                layoutHasImage = backgroundType === 'image' && backgroundValue && backgroundValue !== '#000000';
            }

            // Nếu reward có hình ảnh và layout chưa có background image, tự động set từ reward
            if (rewardImg && rewardImg.trim() !== '') {
                if (!layoutHasImage) {
                    console.log('Auto-setting background image from reward:', rewardImg);
                    backgroundType = 'image';
                    backgroundValue = rewardImg;
                } else {
                    console.log('Layout already has background image, keeping:', backgroundValue);
                }
            } else if (rewardId) {
                console.warn('Reward has no image URL:', rewardId);
            }

            this.canvasManager.setBackground(backgroundType, backgroundValue);
            this.stateManager.saveState(this.blockManager.getState());
            this.updateRandomFieldButtonState();
        } catch (error) {
            console.error('Failed to load layout:', error);
            this.blockManager.loadBlocks([]);
            // Nếu có lỗi nhưng có rewardImg, vẫn thử set background
            if (rewardImg && rewardImg.trim() !== '') {
                console.log('Setting background image from reward after error:', rewardImg);
                this.canvasManager.setBackground('image', rewardImg);
            } else {
                this.canvasManager.setBackground('color', '#000000');
            }
        }
    }

    async saveLayout() {
        const blocks = this.blockManager.getBlocks();
        const background = this.canvasManager.getBackground();

        const data = {
            name: this.currentRewardId ? `Bố cục giải ${this.currentRewardId}` : 'Bố cục mặc định',
            reward_id: this.currentRewardId,
            canvas_width: 1920,
            canvas_height: 1080,
            background_type: background.type,
            background_value: background.value,
            blocks: blocks
        };

        try {
            let response;
            if (this.currentLayoutId && this.currentLayoutId !== 'default') {
                response = await api.put(`${this.apiBase}/layouts/${this.currentLayoutId}`, data);
            } else {
                response = await api.post(`${this.apiBase}/layouts`, data);
                this.currentLayoutId = response.layout.id;

                const tab = document.querySelector(`#layout-tabs .nav-link[data-reward-id="${this.currentRewardId || ''}"]`);
                if (tab) {
                    tab.dataset.layoutId = response.layout.id;
                }
            }

            toastr.success('Đã lưu bố cục');
        } catch (error) {
            console.error('Failed to save layout:', error);
            const msg = error.response?.data?.errors?.blocks?.[0]
                || error.response?.data?.message
                || error.response?.data?.error
                || 'Lưu bố cục thất bại';
            toastr.error(typeof msg === 'string' ? msg : 'Lưu bố cục thất bại');
        }
    }

    openPreview() {
        const url = `{{ route('admin.lucky_draws.display', $luckyDraw) }}`;
        window.open(url, '_blank', 'width=1920,height=1080');
    }

    updateZoomLevel() {
        const zoom = Math.round(this.canvasManager.getZoom() * 100);
        document.getElementById('zoom-level').textContent = `${zoom}%`;
    }

    /**
     * Cập nhật trạng thái nút "Ô quay ngẫu nhiên": disable khi đã đủ số ô theo value của giải
     */
    updateRandomFieldButtonState() {
        const btn = document.getElementById('btn-add-random-field');
        if (!btn) return;
        if (this.currentRewardValue === null || this.currentRewardValue === undefined) {
            btn.disabled = false;
            return;
        }
        const count = this.blockManager.getBlockCountByType('random_field');
        btn.disabled = count >= this.currentRewardValue;
        btn.title = count >= this.currentRewardValue
            ? `Đã đủ ${this.currentRewardValue} ô (theo value của giải)`
            : 'Số ô tối đa theo value của giải';
    }

    /**
     * Mở modal tạo random field
     */
    openCreateRandomFieldModal() {
        // Populate custom fields
        this.populateCustomFieldsForModal('result-custom-fields-list', 'result-field-checkbox');
        this.populateRandomSourceDropdown('random-field-source');
        
        // Reset form
        document.getElementById('random-field-source').value = 'name';
        document.querySelectorAll('.result-field-checkbox').forEach(cb => cb.checked = false);
        
        const modal = new bootstrap.Modal(document.getElementById('createRandomFieldModal'));
        modal.show();
    }

    /**
     * Tự động tạo nhiều ô quay theo cấu hình
     */
    generateAutoSlots(slotCount, layoutConfig) {
        // Xóa tất cả các random_field và result_field hiện có
        const currentBlocks = this.blockManager.getBlocks();
        const fieldBlocks = currentBlocks.filter(b => 
            b.type === 'random_field' || b.type === 'result_field'
        );
        
        fieldBlocks.forEach(block => {
            this.blockManager.deleteBlock(block.id);
        });

        // Tính toán vị trí các ô
        const { 
            type, cols, width, height, spacing, startX, startY, 
            randomSource, resultSources, resultOffsetY, resultSpacing 
        } = layoutConfig;
        
        // Tính chiều cao của 1 khối (random + results)
        const resultHeight = 35;
        const blockHeight = height + (resultSources?.length > 0 
            ? resultOffsetY + (resultSources.length * (resultHeight + resultSpacing))
            : 0);
        
        for (let i = 0; i < slotCount; i++) {
            let x, y;
            
            if (type === 'grid') {
                const row = Math.floor(i / cols);
                const col = i % cols;
                x = col * (width + spacing) + startX;
                y = row * (blockHeight + spacing) + startY; // Dùng blockHeight
            } else if (type === 'horizontal') {
                x = i * (width + spacing) + startX;
                y = startY;
            } else if (type === 'vertical') {
                x = startX;
                y = i * (blockHeight + spacing) + startY; // Dùng blockHeight
            }
            
            // Tạo khối hoàn chỉnh (random + results) cho mỗi slot
            this.createRandomFieldWithResults({
                x,
                y,
                width,
                height,
                randomSource: randomSource || 'name',
                resultSources: resultSources || [],
                offsetY: resultOffsetY || 70,
                spacing: resultSpacing || 35,
                slotIndex: i,
            });
        }
        
        // Lưu state sau khi tạo
        this.blockManager.saveState();
    }

    /**
     * Thêm nhiều result_fields cho một random_field
     */
    // DEPRECATED: No longer used - fields are now added directly in createRandomFieldModal
    // addInfoFields(targetSlotIndex, fields, layoutConfig) {
    //     const targetBlock = this.blockManager.getBlocks()
    //         .find(b => ['random_field', 'result_field'].includes(b.type) && b.slotIndex === targetSlotIndex);
    //     
    //     if (!targetBlock) {
    //         toastr.error('Không tìm thấy ô với slotIndex này');
    //         return;
    //     }
    //     
    //     const baseX = targetBlock.x;
    //     const baseY = targetBlock.y;
    //     const offsetY = layoutConfig.offsetY || 90;
    //     const spacing = layoutConfig.spacing || 40;
    //     
    //     fields.forEach((fieldSource, index) => {
    //         this.blockManager.addBlock('result_field', {
    //             x: baseX,
    //             y: baseY + offsetY + (index * spacing),
    //             width: targetBlock.width,
    //             height: 35,
    //             source: fieldSource,
    //             slotIndex: targetSlotIndex,
    //             style: {
    //                 fontSize: 24,
    //                 color: '#FFFFFF',
    //                 align: 'center',
    //             },
    //             visibleWhen: 'result',
    //         });
    //     });
    //     
    //     this.blockManager.saveState();
    //     toastr.success(`Đã thêm ${fields.length} trường thông tin cho slot ${targetSlotIndex}`);
    // }

    /**
     * Tạo 1 random_field kèm các result_fields
     */
    createRandomFieldWithResults(config) {
        const { x, y, width, height, randomSource, resultSources, offsetY, spacing, slotIndex } = config;
        
        // Giới hạn số ô quay theo value của giải (khi tạo slot mới thủ công)
        if (this.currentRewardValue !== null && slotIndex === undefined) {
            const currentBlocks = this.blockManager.getBlocks();
            const randomFieldCount = currentBlocks.filter(b => b.type === 'random_field').length;
            
            if (randomFieldCount >= this.currentRewardValue) {
                toastr.error(`Không thể tạo thêm ô. Giải này chỉ cho phép tối đa ${this.currentRewardValue} ô quay (đã có ${randomFieldCount} ô).`);
                return;
            }
        }
        
        // Tạo random_field
        const randomId = this.blockManager.addBlock('random_field', {
            x,
            y,
            width,
            height,
            source: randomSource,
            slotIndex: slotIndex ?? Date.now(),
        });
        
        // Tạo các result_fields nếu có
        if (resultSources && resultSources.length > 0) {
            const resultHeight = 35; // Chiều cao nhỏ hơn
            
            resultSources.forEach((source, index) => {
                this.blockManager.addBlock('result_field', {
                    x,
                    y: y + height + offsetY + (index * spacing),
                    width,
                    height: resultHeight,
                    source,
                    slotIndex: slotIndex ?? Date.now(),
                    style: {
                        fontSize: 24,
                        color: '#FFFFFF',
                        align: 'center',
                    },
                    visibleWhen: 'result',
                });
            });
        }
        
        this.blockManager.saveState();
        
        const totalFields = 1 + (resultSources?.length || 0);
        toastr.success(`Đã tạo khối với ${totalFields} trường`);
    }

    /**
     * Populate custom fields list trong modal
     */
    // DEPRECATED: No longer used - replaced by populateCustomFieldsForModal
    // populateCustomFieldsList() {
    //     const container = document.getElementById('custom-fields-list');
    //     container.innerHTML = '';
    //     
    //     const customFieldItems = document.querySelectorAll('#field-list .draggable-field[data-field-key^="custom_fields."]');
    //     
    //     if (customFieldItems.length === 0) {
    //         container.innerHTML = '<small class="text-muted">Không có custom fields</small>';
    //         return;
    //     }
    //     
    //     customFieldItems.forEach(item => {
    //         const fieldKey = item.dataset.fieldKey;
    //         const fieldLabel = item.querySelector('span').textContent.trim();
    //         
    //         const checkDiv = document.createElement('div');
    //         checkDiv.className = 'form-check';
    //         checkDiv.innerHTML = `
    //             <input class="form-check-input" type="checkbox" value="${fieldKey}" id="field-${fieldKey.replace(/\./g, '-')}">
    //             <label class="form-check-label" for="field-${fieldKey.replace(/\./g, '-')}">${fieldLabel}</label>
    //         `;
    //         container.appendChild(checkDiv);
    //     });
    // }

    /**
     * Populate custom fields vào modal (có thể dùng lại)
     */
    populateCustomFieldsForModal(containerId, checkboxClass) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '';
        
        // Lấy custom fields từ field list
        const customFieldItems = document.querySelectorAll('#field-list .draggable-field[data-field-key^="custom_fields."]');
        
        if (customFieldItems.length === 0) {
            container.innerHTML = '<small class="text-muted">Không có custom fields</small>';
            return;
        }
        
        customFieldItems.forEach(item => {
            const fieldKey = item.dataset.fieldKey;
            const fieldLabel = item.querySelector('span').textContent.trim();
            const fieldId = fieldKey.replace(/\./g, '-');
            
            const checkDiv = document.createElement('div');
            checkDiv.className = 'form-check';
            checkDiv.innerHTML = `
                <input class="form-check-input ${checkboxClass}" type="checkbox" value="${fieldKey}" id="${checkboxClass}-${fieldId}">
                <label class="form-check-label" for="${checkboxClass}-${fieldId}">${fieldLabel}</label>
            `;
            container.appendChild(checkDiv);
        });
    }

    /**
     * Thêm custom fields vào dropdown "Trường quay"
     */
    populateRandomSourceDropdown(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Giữ các option cơ bản
        // Thêm custom fields
        const customFieldItems = document.querySelectorAll('#field-list .draggable-field[data-field-key^="custom_fields."]');
        
        customFieldItems.forEach(item => {
            const fieldKey = item.dataset.fieldKey;
            const fieldLabel = item.querySelector('span').textContent.trim();
            
            const option = document.createElement('option');
            option.value = fieldKey;
            option.textContent = fieldLabel;
            select.appendChild(option);
        });
    }

    /**
     * Setup box selection (bôi chọn nhiều ô)
     */
    setupBoxSelection() {
        const stage = this.canvasManager.getStage();
        const layer = this.canvasManager.getContentLayer();
        
        let selectionRectangle = null;
        let x1, y1, x2, y2;
        
        stage.on('mousedown touchstart', (e) => {
            // Only start selection on background (not on blocks)
            if (e.target !== stage) {
                return;
            }
            
            // Clear selection if not Ctrl
            if (!e.evt.ctrlKey && !e.evt.metaKey) {
                this.blockManager.deselectAll();
            }
            
            const pos = stage.getPointerPosition();
            x1 = pos.x;
            y1 = pos.y;
            x2 = pos.x;
            y2 = pos.y;
            
            // Create selection rectangle
            selectionRectangle = new Konva.Rect({
                x: x1,
                y: y1,
                width: 0,
                height: 0,
                fill: 'rgba(0, 123, 255, 0.1)',
                stroke: 'rgba(0, 123, 255, 0.8)',
                strokeWidth: 1,
                dash: [5, 5],
            });
            layer.add(selectionRectangle);
        });
        
        stage.on('mousemove touchmove', (e) => {
            if (!selectionRectangle) {
                return;
            }
            
            const pos = stage.getPointerPosition();
            x2 = pos.x;
            y2 = pos.y;
            
            selectionRectangle.setAttrs({
                x: Math.min(x1, x2),
                y: Math.min(y1, y2),
                width: Math.abs(x2 - x1),
                height: Math.abs(y2 - y1),
            });
            
            layer.batchDraw();
        });
        
        stage.on('mouseup touchend', (e) => {
            if (!selectionRectangle) {
                return;
            }
            
            // Find blocks within selection rectangle
            const box = selectionRectangle.getClientRect();
            const ctrlKey = e.evt?.ctrlKey || e.evt?.metaKey;
            
            this.blockManager.blocks.forEach((block, id) => {
                const blockBox = block.konvaNode.getClientRect();
                
                // Check if block intersects with selection box
                if (this.boxesIntersect(box, blockBox)) {
                    this.blockManager.selectBlock(id, ctrlKey);
                }
            });
            
            // Remove selection rectangle
            selectionRectangle.destroy();
            selectionRectangle = null;
            layer.batchDraw();
        });
    }

    /**
     * Check if two boxes intersect
     */
    boxesIntersect(box1, box2) {
        return !(
            box2.x > box1.x + box1.width ||
            box2.x + box2.width < box1.x ||
            box2.y > box1.y + box1.height ||
            box2.y + box2.height < box1.y
        );
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.lucky-draw-builder');
    if (container) {
        window.luckyDrawBuilder = new LuckyDrawBuilder(container);
    }
});
</script>
@endpush
