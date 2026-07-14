"use client";
import { use, useState } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";
import { OptRow } from "@/components/ui/opt-row";
import { Card } from "@/components/ui/card";
import { Toggle } from "@/components/ui/toggle";
import { Tooltip } from "@/components/ui/tooltip";
import type { Field } from "@/lib/types";

const DEFAULT_FIELDS: Field[] = [
  { id: 1, label: "Mã QR cá nhân", key: "qrcode", type: "code", required: true, unique: true, shownOnForm: false, locked: true },
  { id: 2, label: "Họ và tên", key: "name", type: "text", required: true, unique: false, shownOnForm: true, locked: true },
  { id: 3, label: "Email", key: "email", type: "email", required: false, unique: true, shownOnForm: true, locked: true },
  { id: 4, label: "Địa chỉ", key: "address", type: "text", required: false, unique: false, shownOnForm: true },
  { id: 5, label: "Nghề nghiệp", key: "job_title", type: "select", required: false, unique: false, shownOnForm: true, options: [
    { label: "Bác sĩ", value: "dentist" }, { label: "Điều dưỡng", value: "assistant" },
    { label: "Kỹ thuật viên", value: "technician" }, { label: "Sinh viên", value: "student" },
  ] },
  { id: 6, label: "Số điện thoại", key: "phone_number", type: "phone", required: false, unique: false, shownOnForm: true },
  { id: 7, label: "Nơi công tác", key: "company_name", type: "text", required: false, unique: false, shownOnForm: true },
  { id: 8, label: "Ngày sinh", key: "date_of_birth", type: "date", required: false, unique: false, shownOnForm: false },
  { id: 9, label: "File bằng cấp", key: "qualification_file", type: "file", required: false, unique: false, shownOnForm: true },
  { id: 10, label: "Danh xưng", key: "title", type: "radio", required: false, unique: false, shownOnForm: true, options: [
    { label: "Dr.", value: "dr" }, { label: "Mr.", value: "mr" }, { label: "Ms.", value: "ms" }, { label: "Mrs.", value: "mrs" },
  ] },
];

const FIELD_TYPE_LABELS: Record<string, string> = {
  text: "Văn bản", email: "Email", phone: "Số điện thoại",
  number: "Số", date: "Ngày tháng", select: "Danh sách chọn",
  radio: "Nút chọn", file: "File đính kèm", code: "Mã đặc biệt",
};

function FieldItem({ field, index, onUpdate, onRemove }: {
  field: Field;
  index: number;
  onUpdate: (f: Field) => void;
  onRemove: () => void;
}) {
  const t = useT();
  const [open, setOpen] = useState(false);

  return (
    <div className={`fld${open ? " fld--open" : ""}`}>
      <div className="fld__row" onClick={() => setOpen(!open)}>
        <div className="fld__handle">
          {field.locked ? <Icon name="lock" size={13} /> : <Icon name="drag" size={13} />}
        </div>
        <div className="fld__main">
          <div className="fld__title">
            {field.label}
            {field.locked && <span style={{ fontSize: 11, color: "var(--text-faint)" }}>({t("Hệ thống", "System")})</span>}
          </div>
          <div className="fld__meta">
            <span className="fld__chip">{FIELD_TYPE_LABELS[field.type] || field.type}</span>
            {field.required && <span className="fld__chip fld__chip--req">{t("Bắt buộc", "Required")}</span>}
            {field.unique && <span className="fld__chip fld__chip--unique">{t("Duy nhất", "Unique")}</span>}
            {field.shownOnForm && <span className="fld__chip fld__chip--shown">{t("Hiển thị", "Shown")}</span>}
          </div>
        </div>
        <div className="fld__actions" onClick={(e) => e.stopPropagation()}>
          {!field.locked && (
            <button className="icon-btn icon-btn--danger" onClick={onRemove} title={t("Xóa trường", "Delete field")}>
              <Icon name="trash" size={14} />
            </button>
          )}
          <div className="fld__caret"><Icon name="chevron" size={15} /></div>
        </div>
      </div>

      {open && (
        <div className="fld__body">
          <div className="field">
            <label className="field__label">{t("Tên hiển thị", "Display name")}</label>
            <input
              className="input"
              value={field.label}
              onChange={(e) => onUpdate({ ...field, label: e.target.value })}
              readOnly={!!field.locked}
            />
          </div>
          <div className="field">
            <label className="field__label">{t("Tên khóa (key)", "Field key")}</label>
            <input className="input" value={field.key} readOnly />
            <div className="field__hint">{t("Tên kỹ thuật, không thay đổi sau khi tạo", "Technical name, cannot change after creation")}</div>
          </div>
          <div className="field">
            <label className="field__label">{t("Loại trường", "Field type")}</label>
            <select className="select" value={field.type} disabled={!!field.locked} onChange={(e) => onUpdate({ ...field, type: e.target.value as Field["type"] })}>
              {Object.entries(FIELD_TYPE_LABELS).map(([v, l]) => <option key={v} value={v}>{l}</option>)}
            </select>
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            <div className="opt-row">
              <div className="opt-row__main"><div className="opt-row__title">{t("Bắt buộc", "Required")}</div></div>
              <Toggle on={field.required} onChange={(v) => onUpdate({ ...field, required: v })} disabled={!!field.locked} />
            </div>
            <div className="opt-row">
              <div className="opt-row__main"><div className="opt-row__title">{t("Hiển thị trên form", "Show on form")}</div></div>
              <Toggle on={field.shownOnForm} onChange={(v) => onUpdate({ ...field, shownOnForm: v })} />
            </div>
          </div>

          {(field.type === "select" || field.type === "radio") && field.options && (
            <div className="fld__options">
              <div className="field__label" style={{ marginBottom: 10 }}>{t("Danh sách lựa chọn", "Options")}</div>
              <div className="opt-list">
                {field.options.map((opt, oi) => (
                  <div key={oi} className="opt-list__row">
                    <input className="input" value={opt.label} onChange={(e) => {
                      const opts = [...field.options!]; opts[oi] = { ...opt, label: e.target.value };
                      onUpdate({ ...field, options: opts });
                    }} />
                    <input className="input" value={opt.value} readOnly style={{ fontFamily: "monospace", fontSize: 12 }} />
                    <button className="icon-btn icon-btn--danger" onClick={() => {
                      onUpdate({ ...field, options: field.options!.filter((_, i) => i !== oi) });
                    }}><Icon name="trash" size={13} /></button>
                  </div>
                ))}
                <button className="opt-list__add" onClick={() => onUpdate({ ...field, options: [...(field.options || []), { label: "Lựa chọn mới", value: `option_${Date.now()}` }] })}>
                  + {t("Thêm lựa chọn", "Add option")}
                </button>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

export default function FormPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);
  const [fields, setFields] = useState<Field[]>(DEFAULT_FIELDS.map((f) => ({ ...f })));
  const [captcha, setCaptcha] = useState(true);
  const [cccd, setCccd] = useState(false);
  const [autoCheckin, setAutoCheckin] = useState(false);
  const [nfcBadge, setNfcBadge] = useState(false);
  const [confirmEmail, setConfirmEmail] = useState(true);

  const updateField = (id: number, f: Field) => setFields((prev) => prev.map((x) => (x.id === id ? f : x)));
  const removeField = (id: number) => setFields((prev) => prev.filter((x) => x.id !== id));

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="users" size={20} />
        <h2>{t("Trang đăng ký", "Registration form")}</h2>
        <span className="small">{t("Cấu hình form đăng ký cho khách", "Configure the guest registration form")}</span>
      </div>

      <div className="content">
        <Card title={t("Cài đặt form", "Form settings")} sub={t("Tùy chọn bảo mật và xác minh", "Security and verification options")} icon="settings">
          <OptRow title={t("Bật CAPTCHA", "Enable CAPTCHA")} desc={t("Yêu cầu xác minh robot trên form đăng ký", "Require robot verification on registration form")} on={captcha} onChange={setCaptcha} />
          <OptRow title={t("Quét CCCD/Passport", "Scan ID/Passport")} desc={t("Khách quét căn cước công dân khi đăng ký", "Guests scan their national ID during registration")} on={cccd} onChange={setCccd} />
          <OptRow title={t("Tự động check-in sau đăng ký", "Auto check-in after registration")} desc={t("Khách được mark là đã check-in ngay sau khi đăng ký thành công", "Guests are marked as checked in immediately after successful registration")} on={autoCheckin} onChange={setAutoCheckin} />
          <OptRow title={t("Badge NFC", "NFC Badge")} desc={t("Hỗ trợ ghi thông tin lên badge NFC", "Support writing info to NFC badges")} on={nfcBadge} onChange={setNfcBadge} />
          <OptRow title={t("Email xác nhận đăng ký", "Registration confirmation email")} desc={t("Gửi email kèm mã QR khi đăng ký thành công", "Send email with QR code on successful registration")} on={confirmEmail} onChange={setConfirmEmail} />
        </Card>

        <Card title={t("Trường thông tin", "Form fields")} sub={t("Kéo để sắp xếp thứ tự hiển thị trên form", "Drag to reorder fields on the form")} icon="users">
          {fields.map((f, i) => (
            <FieldItem
              key={f.id}
              field={f}
              index={i}
              onUpdate={(updated) => updateField(f.id, updated)}
              onRemove={() => removeField(f.id)}
            />
          ))}
          <button className="add-field" onClick={() => setFields((prev) => [...prev, {
            id: Date.now(), label: "Trường mới", key: `field_${Date.now()}`, type: "text",
            required: false, unique: false, shownOnForm: true,
          }])}>
            <Icon name="plus" size={16} />
            {t("Thêm trường thông tin", "Add field")}
          </button>
        </Card>
      </div>
    </div>
  );
}
