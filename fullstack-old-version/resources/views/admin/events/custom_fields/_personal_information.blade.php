{{-- Câu hỏi tùy chỉnh --}}
<div class="col-12">
    <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">THÔNG TIN CỐ ĐỊNH</h6>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="row row-cols-1 row-cols-lg-3 g-2 text-center align-items-stretch">

                @forelse($customFieldTemplates->where('is_default',1) as $cft)
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
                                data-order="{{ $cft->order }}"
                                data-options='@json(!empty($cft->options) ? json_decode($cft->options, true) : [])'
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
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
                    <h5 class="modal-title">Thông tin cố định</h5>
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
                        <div class="col-12">
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
                        </div>

                        {{-- Options: chỉ hiện nếu type thuộc nhóm dùng options --}}
                        <div class="col-12" id="edit_options_block" style="display:none;">
                            <label class="form-label mb-2">Tùy chọn (key/value)</label>
                            <div id="edit_options_list" class="vstack gap-2"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btn_add_option">
                                <i class="fa-solid fa-plus"></i> Thêm option
                            </button>
                            <div class="form-text">
                                Key sẽ là giá trị lưu xuống dữ liệu; Giá trị là nhãn hiển thị cho người dùng.
                            </div>
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
        const TYPES_USE_OPTIONS = ['SELECT', 'MULTICHOICE', 'RADIO'];

        function safeParseJson(val) {
            try {
                if (val === null || val === undefined || val === '') return [];
                return JSON.parse(val);
            } catch (e) {
                return [];
            }
        }

        function setOptionsBlockVisible(modalEl, visible) {
            const block = modalEl.querySelector('#edit_options_block');
            if (!block) return;
            block.style.display = visible ? '' : 'none';
        }

        function nextOptionIndex(listEl) {
            const last = listEl.querySelector('[data-option-row]:last-child');
            if (!last) return 0;
            const idx = parseInt(last.getAttribute('data-index') || '0', 10);
            return Number.isFinite(idx) ? idx + 1 : 0;
        }

        function makeOptionRow(modalEl, index, keyVal = '', labelVal = '') {
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center';
            row.setAttribute('data-option-row', '1');
            row.setAttribute('data-index', String(index));

            const colKey = document.createElement('div');
            colKey.className = 'col-md-5';
            const inputKey = document.createElement('input');
            inputKey.type = 'text';
            inputKey.className = 'form-control form-control-sm';
            inputKey.name = `options[${index}][key]`;
            inputKey.placeholder = 'Key (vd: nam)';
            inputKey.value = keyVal || '';
            colKey.appendChild(inputKey);

            const colVal = document.createElement('div');
            colVal.className = 'col-md-6';
            const inputVal = document.createElement('input');
            inputVal.type = 'text';
            inputVal.className = 'form-control form-control-sm';
            inputVal.name = `options[${index}][value]`;
            inputVal.placeholder = 'Giá trị (vd: Nam)';
            inputVal.value = labelVal || '';
            colVal.appendChild(inputVal);

            const colDel = document.createElement('div');
            colDel.className = 'col-md-1 text-end';
            const btnDel = document.createElement('button');
            btnDel.type = 'button';
            btnDel.className = 'btn btn-sm btn-link text-danger p-0';
            btnDel.innerHTML = '<i class=\"fa-solid fa-trash\"></i>';
            btnDel.addEventListener('click', function() {
                row.remove();
                // keep at least 1 row when options are required
                const typeVal = modalEl.querySelector('#type')?.value;
                const listEl = modalEl.querySelector('#edit_options_list');
                if (TYPES_USE_OPTIONS.includes(typeVal) && listEl && !listEl.querySelector('[data-option-row]')) {
                    listEl.appendChild(makeOptionRow(modalEl, 0, '', ''));
                }
            });
            colDel.appendChild(btnDel);

            row.appendChild(colKey);
            row.appendChild(colVal);
            row.appendChild(colDel);
            return row;
        }

        function fillOptions(modalEl, optionsArr) {
            const listEl = modalEl.querySelector('#edit_options_list');
            if (!listEl) return;
            listEl.innerHTML = '';

            // Stored shapes:
            // - [{key,value}, ...]
            // - {key: value, ...} (legacy)
            let rows = [];
            if (Array.isArray(optionsArr) && optionsArr.length) {
                rows = optionsArr.map((opt) => ({ key: opt?.key ?? '', value: opt?.value ?? '' }));
            } else if (optionsArr && typeof optionsArr === 'object') {
                rows = Object.entries(optionsArr).map(([k, v]) => ({ key: k, value: v ?? '' }));
            }

            if (rows.length) {
                rows.forEach((opt, i) => listEl.appendChild(makeOptionRow(modalEl, i, opt.key, opt.value)));
                return;
            }

            listEl.appendChild(makeOptionRow(modalEl, 0, '', ''));
        }

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
            const optionsJson = button.getAttribute('data-options');

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

            // tùy chỉnh cho name, qrcode
            const requiredInput = editTemplateModal.querySelector('#required');
            const uniqueInput = editTemplateModal.querySelector('#unique');
            const isLpInput = editTemplateModal.querySelector('#is_lp');

            // Options UI
            const typeVal = editTemplateModal.querySelector('#type').value;
            const visible = TYPES_USE_OPTIONS.includes(typeVal);
            setOptionsBlockVisible(editTemplateModal, visible);
            if (visible) {
                fillOptions(editTemplateModal, safeParseJson(optionsJson));
            } else {
                // Clear options inputs so they won't be submitted by accident.
                const listEl = editTemplateModal.querySelector('#edit_options_list');
                if (listEl) listEl.innerHTML = '';
            }
        });

        // When type changes inside modal, toggle options block.
        const typeSelect = editTemplateModal.querySelector('#type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                const typeVal = this.value;
                const visible = TYPES_USE_OPTIONS.includes(typeVal);
                setOptionsBlockVisible(editTemplateModal, visible);
                if (visible) {
                    const listEl = editTemplateModal.querySelector('#edit_options_list');
                    if (listEl && !listEl.querySelector('[data-option-row]')) {
                        fillOptions(editTemplateModal, []);
                    }
                } else {
                    const listEl = editTemplateModal.querySelector('#edit_options_list');
                    if (listEl) listEl.innerHTML = '';
                }
            });
        }

        // Add option button
        const btnAdd = editTemplateModal.querySelector('#btn_add_option');
        if (btnAdd) {
            btnAdd.addEventListener('click', function() {
                const listEl = editTemplateModal.querySelector('#edit_options_list');
                if (!listEl) return;
                const idx = nextOptionIndex(listEl);
                listEl.appendChild(makeOptionRow(editTemplateModal, idx, '', ''));
            });
        }
    });
</script>
