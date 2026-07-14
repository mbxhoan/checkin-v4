"use client";
import { use, useState, useMemo } from "react";
import { useT } from "@/lib/context";
import { GUESTS } from "@/data/guests";
import { Icon } from "@/components/ui/icon";
import { Modal } from "@/components/ui/modal";
import type { Guest } from "@/lib/types";

const STATUS_LABELS: Record<Guest["status"], { label: string; labelEn: string; color: string }> = {
  registered: { label: "Đã đăng ký", labelEn: "Registered", color: "#0891b2" },
  checked_in: { label: "Đã check-in", labelEn: "Checked in", color: "#16a34a" },
  checked_out: { label: "Đã check-out", labelEn: "Checked out", color: "#6b7280" },
  cancelled: { label: "Đã hủy", labelEn: "Cancelled", color: "#dc2626" },
};

function GuestStatusBadge({ status }: { status: Guest["status"] }) {
  const s = STATUS_LABELS[status];
  return (
    <span style={{ display: "inline-flex", alignItems: "center", gap: 5, padding: "2px 8px", borderRadius: 999, fontSize: 11.5, fontWeight: 500, background: `${s.color}18`, color: s.color }}>
      <span style={{ width: 5, height: 5, borderRadius: "50%", background: s.color, display: "inline-block" }} />
      {s.label}
    </span>
  );
}

function AddGuestModal({ open, onClose }: { open: boolean; onClose: () => void }) {
  const t = useT();
  return (
    <Modal open={open} onClose={onClose} title={t("Thêm khách mới", "Add new guest")} sub={t("Nhập thông tin khách tham dự", "Enter guest information")} icon="users"
      footer={
        <>
          <button className="qa__btn" onClick={onClose}>{t("Hủy", "Cancel")}</button>
          <button className="qa__btn qa__btn--primary" onClick={onClose}>{t("Thêm khách", "Add guest")}</button>
        </>
      }
    >
      <div className="grid grid--2">
        <div className="field">
          <label className="field__label field__label--req">{t("Họ và tên", "Full name")}</label>
          <input className="input" placeholder="Nguyễn Văn A" />
        </div>
        <div className="field">
          <label className="field__label">{t("Email", "Email")}</label>
          <input className="input" type="email" placeholder="email@company.vn" />
        </div>
        <div className="field">
          <label className="field__label">{t("Số điện thoại", "Phone")}</label>
          <input className="input" type="tel" placeholder="0901 234 567" />
        </div>
        <div className="field">
          <label className="field__label">{t("Nhóm", "Group")}</label>
          <select className="select">
            <option>VIP</option>
            <option>Bác sĩ</option>
            <option>Điều dưỡng</option>
            <option>Kỹ thuật viên</option>
            <option>Sinh viên</option>
          </select>
        </div>
      </div>
    </Modal>
  );
}

function ImportModal({ open, onClose }: { open: boolean; onClose: () => void }) {
  const t = useT();
  const [step, setStep] = useState(1);

  const steps = [
    { num: 1, label: t("Tải mẫu", "Download template") },
    { num: 2, label: t("Ánh xạ cột", "Map columns") },
    { num: 3, label: t("Xem lại", "Review") },
  ];

  return (
    <Modal open={open} onClose={() => { setStep(1); onClose(); }} title={t("Nạp khách từ Excel", "Import guests from Excel")} icon="upload" size="lg"
      footer={
        <>
          <button className="qa__btn" onClick={() => { if (step > 1) setStep(s => s - 1); else onClose(); }}>
            {step === 1 ? t("Hủy", "Cancel") : t("Quay lại", "Back")}
          </button>
          {step < 3
            ? <button className="qa__btn qa__btn--primary" onClick={() => setStep(s => s + 1)}>{t("Tiếp theo", "Next")}</button>
            : <button className="qa__btn qa__btn--primary" onClick={() => { setStep(1); onClose(); }}>{t("Nạp dữ liệu", "Import data")}</button>
          }
        </>
      }
    >
      <div className="steps">
        {steps.map((s, i) => (
          <>
            <div key={s.num} className={`steps__item${step === s.num ? " steps__item--active" : ""}${step > s.num ? " steps__item--done" : ""}`}>
              <div className="steps__num">{step > s.num ? "✓" : s.num}</div>
              <span>{s.label}</span>
            </div>
            {i < steps.length - 1 && <div className="steps__connector" key={`conn-${i}`} />}
          </>
        ))}
      </div>

      {step === 1 && (
        <div style={{ textAlign: "center", padding: "20px 0" }}>
          <div style={{ width: 64, height: 64, borderRadius: 16, background: "var(--primary-soft)", display: "grid", placeItems: "center", margin: "0 auto 16px" }}>
            <Icon name="download" size={28} style={{ color: "var(--primary)" }} />
          </div>
          <h3 style={{ margin: "0 0 8px" }}>{t("Tải file mẫu Excel", "Download Excel template")}</h3>
          <p className="small">{t("File mẫu chứa tất cả trường thông tin yêu cầu của sự kiện này", "Template contains all required fields for this event")}</p>
          <button className="qa__btn qa__btn--primary" style={{ marginTop: 16 }}>
            <Icon name="download" size={14} />{t("Tải mẫu Excel", "Download template")}
          </button>
        </div>
      )}

      {step === 2 && (
        <div>
          <p className="small" style={{ marginBottom: 16 }}>{t("Ánh xạ cột trong file Excel với trường thông tin của hệ thống", "Map columns from your Excel file to system fields")}</p>
          <div className="upload" style={{ marginBottom: 16 }}>
            <Icon name="upload" size={28} style={{ color: "var(--text-faint)", margin: "0 auto 8px" }} />
            <div className="upload__title">{t("Chọn file Excel", "Select Excel file")}</div>
            <div className="upload__sub">.xlsx, .csv • {t("Tối đa 10MB", "Max 10MB")}</div>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8 }}>
            {["Họ tên", "Email", "Số điện thoại", "Nhóm"].map((col) => (
              <div key={col} style={{ display: "flex", alignItems: "center", gap: 8, padding: "8px 10px", border: "1px solid var(--border)", borderRadius: 7 }}>
                <span style={{ flex: 1, fontSize: 13 }}>{col}</span>
                <Icon name="chevron_right" size={14} style={{ color: "var(--text-faint)" }} />
                <select className="select" style={{ width: 120 }}>
                  <option>{col}</option>
                  <option>—</option>
                </select>
              </div>
            ))}
          </div>
        </div>
      )}

      {step === 3 && (
        <div>
          <div style={{ display: "flex", gap: 12, marginBottom: 16, padding: 16, background: "var(--success-soft)", borderRadius: 10 }}>
            <Icon name="check_circle" size={20} style={{ color: "var(--success)", flexShrink: 0 }} />
            <div>
              <div style={{ fontWeight: 600, color: "var(--success)" }}>{t("Sẵn sàng nạp 120 khách", "Ready to import 120 guests")}</div>
              <div className="small">{t("3 dòng bỏ qua do thiếu email", "3 rows skipped due to missing email")}</div>
            </div>
          </div>
          <table className="guest-table">
            <thead>
              <tr>
                <th>{t("Họ và tên", "Name")}</th>
                <th>Email</th>
                <th>{t("Số điện thoại", "Phone")}</th>
                <th>{t("Nhóm", "Group")}</th>
              </tr>
            </thead>
            <tbody>
              {[
                { name: "Nguyễn Văn A", email: "a@test.vn", phone: "0901234567", group: "VIP" },
                { name: "Trần Thị B", email: "b@test.vn", phone: "0912345678", group: "Bác sĩ" },
                { name: "Lê Minh C", email: "c@test.vn", phone: "0923456789", group: "Điều dưỡng" },
              ].map((g, i) => (
                <tr key={i}>
                  <td>{g.name}</td>
                  <td>{g.email}</td>
                  <td>{g.phone}</td>
                  <td>{g.group}</td>
                </tr>
              ))}
              <tr>
                <td colSpan={4} style={{ textAlign: "center", color: "var(--text-faint)", fontStyle: "italic" }}>...và 117 khách khác</td>
              </tr>
            </tbody>
          </table>
        </div>
      )}
    </Modal>
  );
}

function QRPreviewModal({ guest, open, onClose }: { guest: Guest | null; open: boolean; onClose: () => void }) {
  const t = useT();
  if (!guest) return null;
  return (
    <Modal open={open} onClose={onClose} title={t("Mã QR của khách", "Guest QR code")} sub={guest.name} icon="qrcode" size="sm"
      footer={
        <>
          <button className="qa__btn" onClick={onClose}>{t("Đóng", "Close")}</button>
          <button className="qa__btn qa__btn--primary"><Icon name="download" size={14} />{t("Tải xuống", "Download")}</button>
        </>
      }
    >
      <div style={{ textAlign: "center", padding: "16px 0" }}>
        <div style={{ width: 160, height: 160, background: "#000", borderRadius: 10, margin: "0 auto 12px", display: "grid", placeItems: "center" }}>
          <div style={{ display: "grid", gridTemplateColumns: "repeat(10, 12px)", gap: 2 }}>
            {Array.from({ length: 100 }, (_, i) => (
              <div key={i} style={{ width: 12, height: 12, background: Math.random() > 0.5 ? "#fff" : "#000", borderRadius: 1 }} />
            ))}
          </div>
        </div>
        <div style={{ fontFamily: "monospace", fontSize: 13, color: "var(--text-muted)" }}>{guest.qrcode}</div>
        <div style={{ fontSize: 12, color: "var(--text-faint)", marginTop: 4 }}>{guest.name} • {guest.email}</div>
      </div>
    </Modal>
  );
}

const PAGE_SIZE = 8;

export default function GuestsPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  const { id } = use(params);
  const [addOpen, setAddOpen] = useState(false);
  const [importOpen, setImportOpen] = useState(false);
  const [qrGuest, setQrGuest] = useState<Guest | null>(null);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [page, setPage] = useState(1);

  const guests = GUESTS.filter((g) => g.eventId === id);

  const filtered = useMemo(() => {
    const ql = search.toLowerCase();
    return guests.filter((g) => {
      const matchStatus = statusFilter === "all" || g.status === statusFilter;
      const matchSearch = !ql || g.name.toLowerCase().includes(ql) || g.email.toLowerCase().includes(ql) || g.phone.includes(ql);
      return matchStatus && matchSearch;
    });
  }, [guests, search, statusFilter]);

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const pageGuests = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  const checkedInCount = guests.filter((g) => g.status === "checked_in").length;

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="users" size={20} />
        <h2>{t("Khách của sự kiện", "Guest list")}</h2>
        <span className="small">{guests.length} {t("khách", "guests")} • {checkedInCount} {t("đã check-in", "checked in")}</span>
      </div>

      <div className="content content--wide">
        {/* Toolbar */}
        <div style={{ display: "flex", gap: 8, marginBottom: 14, flexWrap: "wrap", alignItems: "center" }}>
          <div className="search-input" style={{ minWidth: 240 }}>
            <Icon name="search" size={15} />
            <input
              placeholder={t("Tìm theo tên, email, SĐT...", "Search by name, email, phone...")}
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            />
          </div>

          {["all", "registered", "checked_in"].map((s) => (
            <button
              key={s}
              className={`filter-pill${statusFilter === s ? " filter-pill--active" : ""}`}
              onClick={() => { setStatusFilter(s); setPage(1); }}
            >
              {s === "all" ? t("Tất cả", "All") : STATUS_LABELS[s as Guest["status"]].label}
              <span className="count">{s === "all" ? guests.length : guests.filter((g) => g.status === s).length}</span>
            </button>
          ))}

          <div style={{ marginLeft: "auto", display: "flex", gap: 8 }}>
            <button className="qa__btn" onClick={() => setImportOpen(true)}>
              <Icon name="upload" size={14} />{t("Nạp từ Excel", "Import Excel")}
            </button>
            <button className="qa__btn" onClick={() => {}}>
              <Icon name="download" size={14} />{t("Xuất", "Export")}
            </button>
            <button className="qa__btn qa__btn--primary" onClick={() => setAddOpen(true)}>
              <Icon name="plus" size={14} />{t("Thêm khách", "Add guest")}
            </button>
          </div>
        </div>

        {/* Table */}
        <div className="card">
          <table className="guest-table">
            <thead>
              <tr>
                <th>#</th>
                <th>{t("Họ và tên", "Name")}</th>
                <th>Email</th>
                <th>{t("Số điện thoại", "Phone")}</th>
                <th>{t("Nhóm", "Group")}</th>
                <th>{t("Nguồn", "Source")}</th>
                <th>{t("Trạng thái", "Status")}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {pageGuests.map((g, i) => (
                <tr key={g.id}>
                  <td style={{ color: "var(--text-faint)", fontSize: 12 }}>{(page - 1) * PAGE_SIZE + i + 1}</td>
                  <td>
                    <div style={{ fontWeight: 500 }}>{g.name}</div>
                  </td>
                  <td style={{ color: "var(--text-muted)" }}>{g.email}</td>
                  <td style={{ color: "var(--text-muted)" }}>{g.phone}</td>
                  <td>
                    <span style={{ fontSize: 12, padding: "2px 8px", borderRadius: 999, background: "var(--surface-2)", color: "var(--text-muted)" }}>{g.group}</span>
                  </td>
                  <td style={{ color: "var(--text-muted)", fontSize: 12.5 }}>
                    {g.source === "registration" ? t("Đăng ký", "Registration") : g.source === "import" ? t("Nạp Excel", "Excel import") : t("Thủ công", "Manual")}
                  </td>
                  <td><GuestStatusBadge status={g.status} /></td>
                  <td>
                    <div style={{ display: "flex", gap: 4 }}>
                      <button className="icon-btn" onClick={() => setQrGuest(g)} title={t("Xem mã QR", "View QR code")}>
                        <Icon name="qrcode" size={14} />
                      </button>
                      <button className="icon-btn" title={t("Sửa", "Edit")}>
                        <Icon name="edit" size={14} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {pageGuests.length === 0 && (
                <tr><td colSpan={8} style={{ textAlign: "center", padding: "40px 0", color: "var(--text-muted)" }}>{t("Không tìm thấy khách nào", "No guests found")}</td></tr>
              )}
            </tbody>
          </table>

          {/* Pagination */}
          {totalPages > 1 && (
            <div style={{ padding: "12px 16px", borderTop: "1px solid var(--border)", display: "flex", alignItems: "center", gap: 8, justifyContent: "center" }}>
              <button className="icon-btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page === 1}>
                <Icon name="chevron_left" size={14} />
              </button>
              {Array.from({ length: totalPages }, (_, i) => (
                <button
                  key={i}
                  className={`icon-btn${page === i + 1 ? "" : ""}`}
                  onClick={() => setPage(i + 1)}
                  style={{ background: page === i + 1 ? "var(--primary)" : undefined, color: page === i + 1 ? "#fff" : undefined, minWidth: 32 }}
                >
                  {i + 1}
                </button>
              ))}
              <button className="icon-btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page === totalPages}>
                <Icon name="chevron_right" size={14} />
              </button>
              <span className="small">{filtered.length} {t("khách", "guests")}</span>
            </div>
          )}
        </div>
      </div>

      <AddGuestModal open={addOpen} onClose={() => setAddOpen(false)} />
      <ImportModal open={importOpen} onClose={() => setImportOpen(false)} />
      <QRPreviewModal guest={qrGuest} open={!!qrGuest} onClose={() => setQrGuest(null)} />
    </div>
  );
}
