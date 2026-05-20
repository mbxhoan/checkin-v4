{{-- Câu hỏi tùy chỉnh --}}
<div class="col-12">
    <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">Tùy chỉnh</h6>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="row row-cols-1 row-cols-lg-3 g-2 text-center align-items-stretch">

                @forelse($customFieldTemplates->where('is_default',0) as $cft)
                    <div class="col d-flex">
                        <div
                            class="position-relative border border-1 border-secondary-subtle rounded-3 py-3 h-100 w-100">
                            <div class="text-uppercase text-muted small">{{ $cft->name }}</div>
                            <div class="fw-semibold">{{ $cft->description ?: '—' }}</div>
                            <div class="small text-muted mt-1">{{ $cft->type }}</div>

                            {{-- nút chỉnh sửa mở modal --}}
                            {{-- truyền các biến vào nút mở model  --}}
                            <a href="#" class="position-absolute top-0 end-0 p-2 text-decoration-none text-muted"
                                title="Chỉnh sửa" data-bs-toggle="modal" data-bs-target="#editTemplateModal-custom"
                                data-id="{{ $cft->id }}" data-name="{{ $cft->name }}"
                                data-description="{{ $cft->description }}" data-type="{{ $cft->type }}"
                                data-required="{{ $cft->required }}" data-unique="{{ $cft->unique }}"
                                data-is_lp="{{ $cft->is_lp }}" data-event-id="{{ $event->id }}"
                                data-order="{{ $cft->order }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>
                    </div>
                @empty
                @endforelse

                {{-- Ô "+" để thêm mới --}}
                <div class="col d-flex">
                    <button type="button"
                        class="btn btn-light rounded-3 border border-1 border-secondary-subtle w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="fa-solid fa-plus fa-2x mb-2"></i>
                        <div>Thêm</div>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="createTemplateForm" method="POST" action="{{ route('admin.custom_field_templates.store') }}">
            @csrf
            @method('POST')
            <input type="hidden" name="new[event_id]" id="event_id" value="{{ $event->id }}">
            <input type="hidden" name="new[order]" id="order" value="1">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm trường thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tên</label>
                            <input type="text" class="form-control" name="new[name]" id="name"
                                placeholder="vd: congty">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Nhãn hiển thị</label>
                            <input type="text" class="form-control" name="new[description]" id="description"
                                placeholder="vd: Công ty">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Loại</label>
                            <select class="form-select" name="new[type]" id="type">
                                @foreach ($customFieldTemplates->first()?->getTypes() ?? [] as $k => $v)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="required" value="0">
                                    {{-- <input class="form-check-input" type="checkbox" id="required" name="required"
                                        value="1">
                                    <label class="form-check-label" for="required">Bắt buộc</label> --}}
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="unique" value="0">
                                    {{-- <input class="form-check-input" type="checkbox" id="unique" name="unique"
                                        value="1">
                                    <label class="form-check-label" for="unique">Duy nhất</label> --}}
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_lp" value="0">
                                    {{-- <input class="form-check-input" type="checkbox" id="is_lp" name="is_lp"
                                        value="1">
                                    <label class="form-check-label" for="is_lp">Landing page</label> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Options: chỉ hiện nếu type thuộc nhóm dùng options --}}
                        <div class="col-12" id="edit_options_block" style="display:none;">
                            <label class="form-label mb-2">Tùy chọn (key/value)</label>
                            <div id="edit_options_list" class="vstack gap-2"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btn_add_option">
                                <i class="fa-solid fa-plus"></i> Thêm option
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-regular fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Model chỉnh sửa --}}
<div class="modal fade" id="editTemplateModal-custom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="editTemplateForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="event_id" id="event_id">
            <input type="hidden" name="custom_field_template_id" id="custom_field_template_id">
            <input type="hidden" name="order[]" id="order">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Câu hỏi tùy chỉnh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tên</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="vd: congty">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Nhãn hiển thị</label>
                            <input type="text" class="form-control" name="description" id="description"
                                placeholder="vd: Công ty">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Loại</label>
                            <select class="form-select" name="type" id="type">
                                @foreach ($customFieldTemplates->first()?->getTypes() ?? [] as $k => $v)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="col-12">
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="required" value="0">
                                    <input class="form-check-input" type="checkbox" id="required" name="required"
                                        value="1">
                                    <label class="form-check-label" for="required">Bắt buộc</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="unique" value="0">
                                    <input class="form-check-input" type="checkbox" id="unique" name="unique"
                                        value="1">
                                    <label class="form-check-label" for="unique">Duy nhất</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_lp" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_lp" name="is_lp"
                                        value="1">
                                    <label class="form-check-label" for="is_lp">Landing page</label>
                                </div>
                            </div>
                        </div> --}}

                        {{-- Options: chỉ hiện nếu type thuộc nhóm dùng options --}}
                        <div class="col-12" id="edit_options_block" style="display:none;">
                            <label class="form-label mb-2">Tùy chọn (key/value)</label>
                            <div id="edit_options_list" class="vstack gap-2"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btn_add_option">
                                <i class="fa-solid fa-plus"></i> Thêm option
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-regular fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    // Khi modal chỉnh sửa hiện ra
    document.addEventListener('DOMContentLoaded', function() {
        var editTemplateModal = document.getElementById('editTemplateModal-custom');
        editTemplateModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;

            // Lấy dữ liệu từ nút đã nhấn
            // và điền vào form trong modal
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const type = button.getAttribute('data-type');
            const required = button.getAttribute('data-required');
            const unique = button.getAttribute('data-unique');
            const is_lp = button.getAttribute('data-is_lp');
            const event_id = button.getAttribute('data-event-id');
            const order = button.getAttribute('data-order');

            editTemplateModal.querySelector('#name').value = name || '';
            editTemplateModal.querySelector('#description').value = description || '';
            editTemplateModal.querySelector('#type').value = type || '';
            editTemplateModal.querySelector('#required').checked = required == 1;
            editTemplateModal.querySelector('#unique').checked = unique == 1;
            editTemplateModal.querySelector('#is_lp').checked = is_lp == 1;
            editTemplateModal.querySelector('#event_id').value = event_id || '';
            editTemplateModal.querySelector('#custom_field_template_id').value = id || '';
            editTemplateModal.querySelector('#order').value = order || '';
            editTemplateModal.querySelector('#editTemplateForm').action =
                '/admin/custom_field_templates/' + id;
        });
    });
</script>
