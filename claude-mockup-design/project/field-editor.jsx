// field-editor.jsx — dynamic field config editor (the most complex section)

const FIELD_TYPES = [
  { value: "text", label: "Văn bản ngắn", desc: "Họ tên, địa chỉ, ghi chú…", icon: "🅰" },
  { value: "email", label: "Email", desc: "Tự kiểm tra định dạng email", icon: "✉" },
  { value: "phone", label: "Số điện thoại", desc: "Có chọn mã quốc gia", icon: "☎" },
  { value: "number", label: "Số", desc: "Chỉ nhận chữ số", icon: "#" },
  { value: "date", label: "Ngày", desc: "Khách chọn từ lịch", icon: "📅" },
  { value: "select", label: "Danh sách thả xuống", desc: "Chọn 1 trong nhiều giá trị", icon: "▾" },
  { value: "radio", label: "Lựa chọn (radio)", desc: "Hiện tất cả lựa chọn", icon: "◉" },
  { value: "file", label: "Tệp đính kèm", desc: "Cho khách tải lên file", icon: "📎" },
  { value: "code", label: "Mã định danh", desc: "Mã vé / QR code", icon: "▥" },
];

const TYPE_LABEL = Object.fromEntries(FIELD_TYPES.map((t) => [t.value, t.label]));

// Smart key generator from label (Vietnamese accent removal)
const toKey = (s) => {
  if (!s) return "";
  return s
    .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d").replace(/Đ/g, "D")
    .toLowerCase().trim()
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_|_$/g, "");
};

const FieldRow = ({ field, index, onChange, onDelete, onDuplicate, open, onToggle }) => {
  const set = (patch) => onChange({ ...field, ...patch });
  const needsOptions = field.type === "select" || field.type === "radio";

  return (
    <div className={"fld" + (open ? " fld--open" : "")}>
      <div className="fld__row" onClick={onToggle}>
        <div className="fld__handle" title="Kéo để sắp xếp" onClick={(e) => e.stopPropagation()}>
          <Icon name="drag" />
        </div>
        <div className="fld__main">
          <h4 className="fld__title">
            <span className="fld__index">{index + 1}</span>
            {field.label || <span style={{ color: "var(--text-faint)" }}>Trường chưa đặt tên</span>}
            {field.locked && <Tip text="Trường mặc định, không thể xóa"><span className="fld__chip">Mặc định</span></Tip>}
          </h4>
          <div className="fld__meta">
            <span className="fld__chip">{TYPE_LABEL[field.type] || field.type}</span>
            {field.required && <span className="fld__chip fld__chip--req">Bắt buộc</span>}
            {field.unique && <span className="fld__chip fld__chip--unique">Không trùng</span>}
            {field.shownOnForm && <span className="fld__chip fld__chip--shown">Hiện trên trang đăng ký</span>}
          </div>
        </div>
        <div className="fld__actions" onClick={(e) => e.stopPropagation()}>
          <button className="icon-btn" title="Nhân bản trường" onClick={onDuplicate}>
            <Icon name="copy" />
          </button>
          {!field.locked && (
            <button className="icon-btn icon-btn--danger" title="Xóa trường" onClick={onDelete}>
              <Icon name="trash" />
            </button>
          )}
          <button className="icon-btn fld__caret" title={open ? "Thu gọn" : "Mở rộng"} onClick={onToggle}>
            <Icon name="chevron" />
          </button>
        </div>
      </div>

      {open && (
        <div className="fld__body">
          <Field
            label="Nhãn hiển thị cho khách"
            required
            hint="Đây là chữ khách sẽ thấy trên form. VD: Họ và tên"
          >
            <input
              className="input"
              value={field.label}
              onChange={(e) => set({ label: e.target.value, key: field.keyManual ? field.key : toKey(e.target.value) })}
              placeholder="VD: Họ và tên"
            />
          </Field>

          <Field
            label="Kiểu thông tin"
            tip="Quyết định khách sẽ nhập kiểu dữ liệu gì (chữ, số, email…) và form sẽ tự kiểm tra"
          >
            <select className="select" value={field.type} onChange={(e) => set({ type: e.target.value })}>
              {FIELD_TYPES.map((t) => (
                <option key={t.value} value={t.value}>{t.label} — {t.desc}</option>
              ))}
            </select>
          </Field>

          <div style={{ gridColumn: "1 / -1" }}>
            <OptRow
              title="Bắt khách điền"
              desc="Không cho gửi form nếu trường này còn trống"
              tip="Bật khi đây là thông tin bắt buộc thu thập. Khách sẽ thấy dấu sao đỏ (*)."
              on={field.required}
              onChange={(v) => set({ required: v })}
            />
            <OptRow
              title="Không cho phép trùng"
              desc="Mỗi giá trị chỉ được dùng cho 1 khách trong sự kiện này"
              tip="Phù hợp cho email, số điện thoại, mã vé. Nếu khách thứ 2 nhập trùng, hệ thống sẽ báo lỗi."
              on={field.unique}
              onChange={(v) => set({ unique: v })}
            />
            <OptRow
              title="Hiển thị trên trang đăng ký công khai"
              desc="Khi tắt, trường này chỉ dùng cho dữ liệu nội bộ (vẫn lưu trong danh sách khách)"
              tip="Tắt nếu là trường nội bộ (như mã tham chiếu, mã hội viên) — khách sẽ không nhìn thấy trên form đăng ký mở."
              on={field.shownOnForm}
              onChange={(v) => set({ shownOnForm: v })}
            />
          </div>

          {needsOptions && (
            <div className="fld__options">
              <div className="setgrp__head" style={{ padding: 0, marginBottom: 10 }}>
                <Icon name="grid" />
                Các lựa chọn
                <span className="setgrp__head-sub">{(field.options || []).length} lựa chọn</span>
              </div>
              <div className="opt-list">
                {(field.options || []).map((o, i) => (
                  <div className="opt-list__row" key={i}>
                    <input
                      className="input"
                      placeholder="Tên hiển thị (VD: Bác sĩ)"
                      value={o.label}
                      onChange={(e) => {
                        const next = [...field.options];
                        next[i] = { ...o, label: e.target.value, value: o.valueManual ? o.value : toKey(e.target.value) };
                        set({ options: next });
                      }}
                    />
                    <input
                      className="input"
                      placeholder="Mã nội bộ (tự tạo)"
                      value={o.value}
                      onChange={(e) => {
                        const next = [...field.options];
                        next[i] = { ...o, value: e.target.value, valueManual: true };
                        set({ options: next });
                      }}
                      style={{ fontFamily: "'SF Mono', Menlo, monospace", fontSize: 12.5, color: "var(--text-muted)" }}
                    />
                    <button
                      className="icon-btn icon-btn--danger"
                      title="Xóa lựa chọn"
                      onClick={() => set({ options: field.options.filter((_, j) => j !== i) })}
                    >
                      <Icon name="trash" />
                    </button>
                  </div>
                ))}
                <button
                  className="opt-list__add"
                  onClick={() =>
                    set({ options: [...(field.options || []), { label: "", value: "" }] })
                  }
                >
                  + Thêm lựa chọn
                </button>
              </div>
            </div>
          )}

          <div className="fld__advanced">
            <details>
              <summary style={{ cursor: "pointer", fontSize: 12.5, color: "var(--text-muted)", padding: "4px 0" }}>
                Cài đặt nâng cao (mã nội bộ)
              </summary>
              <div style={{ marginTop: 10 }}>
                <Field
                  label="Mã nội bộ của trường"
                  hint="Tự sinh từ nhãn — chỉ thay đổi nếu bạn dùng tích hợp API. Chỉ chứa chữ thường, số và gạch dưới."
                  tip="Đây là mã hệ thống dùng để tham chiếu trường dữ liệu. Người dùng không bao giờ thấy mã này."
                >
                  <input
                    className="input"
                    value={field.key}
                    onChange={(e) => set({ key: toKey(e.target.value), keyManual: true })}
                    style={{ fontFamily: "'SF Mono', Menlo, monospace", fontSize: 12.5 }}
                    disabled={field.locked}
                  />
                </Field>
              </div>
            </details>
          </div>
        </div>
      )}
    </div>
  );
};

const FieldEditor = ({ fields, setFields }) => {
  const [openIds, setOpenIds] = useState(new Set());
  const toggle = (id) => {
    const next = new Set(openIds);
    if (next.has(id)) next.delete(id); else next.add(id);
    setOpenIds(next);
  };
  const update = (i, f) => {
    const next = [...fields];
    next[i] = f;
    setFields(next);
  };
  const remove = (i) => {
    if (fields[i].locked) return;
    if (!confirm(`Xóa trường "${fields[i].label}"? Dữ liệu khách đã có sẽ vẫn được giữ.`)) return;
    setFields(fields.filter((_, j) => j !== i));
  };
  const duplicate = (i) => {
    const f = fields[i];
    const copy = { ...f, id: Date.now(), locked: false, label: f.label + " (sao)", key: toKey(f.label + " sao") };
    const next = [...fields];
    next.splice(i + 1, 0, copy);
    setFields(next);
  };
  const add = () => {
    const id = Date.now();
    const f = { id, label: "", key: "", type: "text", required: false, unique: false, shownOnForm: true };
    setFields([...fields, f]);
    setOpenIds(new Set([...openIds, id]));
  };

  return (
    <div>
      {fields.map((f, i) => (
        <FieldRow
          key={f.id}
          field={f}
          index={i}
          open={openIds.has(f.id)}
          onToggle={() => toggle(f.id)}
          onChange={(n) => update(i, n)}
          onDelete={() => remove(i)}
          onDuplicate={() => duplicate(i)}
        />
      ))}
      <button className="add-field" onClick={add}>
        <Icon name="plus" /> Thêm trường thông tin
      </button>
    </div>
  );
};

Object.assign(window, { FieldEditor, FIELD_TYPES, toKey });
