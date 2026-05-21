// guests-section.jsx — full guest list panel inside an event

const { useState: gUS, useMemo: gUM } = React;

// Use the QR renderer from qr-section.jsx via window (cross-script scope)
const QRRenderer = (props) => {
  const C = window.QRPreview;
  return C ? <C {...props} /> : null;
};

// Mock guest data — different per event
const GUEST_SOURCES = ["IMPORT", "FORM", "MANUAL", "API"];
const GUEST_GROUPS = ["VIP", "Báo chí", "Đối tác", "Diễn giả", "Khách thường", "Nhân viên"];
const STATUS = ["active", "active", "active", "inactive", "active"]; // weighted

const NAMES = [
  ["Nguyễn Lan Anh", "lananh.nguyen@gmail.com"],
  ["Trần Đức Minh", "ducminh.tran@viettel.com.vn"],
  ["Lê Thị Hằng", "hangle@dental-saigon.vn"],
  ["Phạm Hồng Sơn", "son.pham@forestmed.vn"],
  ["Hoàng Mai Phương", "mphuong@kingstella.com"],
  ["Đỗ Quang Hải", "haidq@smartlink.vn"],
  ["Bùi Khánh Linh", "klinh.bui@intrepid.asia"],
  ["Vũ Tuấn Kiệt", "kiet.vu@thekat.shop"],
  ["Đinh Thu Hà", "thuha.dinh@hcmus.edu.vn"],
  ["Lý Hữu Phước", "phuoc.ly@gmail.com"],
  ["Trương Bích Ngọc", "bngoc@vietsovpetro.com.vn"],
  ["Phan Minh Tâm", "tamphan@bigc.vn"],
  ["Ngô Thị Yến", "yenngo@vinmec.com"],
  ["Cao Thanh Tùng", "ctung@savico.com.vn"],
  ["Lâm Hoàng Phúc", "lphuc@dental-vn.com"],
  ["Tô Quốc Anh", "qanh.to@forest.health"],
  ["Mai Hữu Đăng", "mdang@delfi.tech"],
  ["Hồ Anh Quân", "quanho@hcm-eyecare.vn"],
  ["Trịnh Thu Trang", "trangtrinh@hcmpolice.org"],
  ["Đặng Khải Hoàn", "hoandk@hcmcc.vn"],
];

function generateGuests(eventId, count = 67) {
  const arr = [];
  for (let i = 0; i < count; i++) {
    const [name, email] = NAMES[i % NAMES.length];
    const suffix = i >= NAMES.length ? ` ${Math.floor(i / NAMES.length) + 1}` : "";
    arr.push({
      id: `g-${i + 1}`,
      qrId: `QR-${(eventId || "evt").slice(0, 3).toUpperCase()}-${String(i + 1).padStart(4, "0")}`,
      name: name + suffix,
      email: i % 7 === 0 ? "" : email,
      phone: `09${String(10000000 + i * 12347).slice(0, 8)}`,
      group: GUEST_GROUPS[i % GUEST_GROUPS.length],
      source: GUEST_SOURCES[i % GUEST_SOURCES.length],
      registeredAt: `0${(i % 9) + 1}/03/2026 ${String(8 + (i % 9)).padStart(2, "0")}:${String((i * 7) % 60).padStart(2, "0")}`,
      status: STATUS[i % STATUS.length],
      checkedIn: i % 3 === 0,
      checkedInAt: i % 3 === 0 ? `09:${String((i * 11) % 60).padStart(2, "0")}` : null,
    });
  }
  return arr;
}

// ---------------- Import modal ----------------
const ImportGuestsModal = ({ open, onClose, onComplete }) => {
  const [step, setStep] = gUS(1); // 1 download/upload, 2 mapping, 3 review
  const [file, setFile] = gUS(null);
  const [groupName, setGroupName] = gUS("");
  const [autoQR, setAutoQR] = gUS(true);
  const [dedupe, setDedupe] = gUS("email");

  const reset = () => { setStep(1); setFile(null); setGroupName(""); };
  const close = () => { reset(); onClose(); };

  const onFile = (f) => { setFile(f); setStep(2); };

  return (
    <Modal
      open={open}
      onClose={close}
      title="Nạp danh sách khách từ Excel"
      sub={`Bước ${step}/3 — ${step === 1 ? "Chọn file" : step === 2 ? "Đối chiếu cột" : "Xem trước & xác nhận"}`}
      icon="upload"
      size="lg"
      footer={
        <>
          <button className="qa__btn" onClick={close}>Hủy</button>
          <div style={{ flex: 1 }} />
          {step > 1 && <button className="qa__btn" onClick={() => setStep(step - 1)}>Quay lại</button>}
          {step < 3 && (
            <button
              className="qa__btn qa__btn--primary"
              disabled={step === 1 && !file}
              onClick={() => setStep(step + 1)}
            >
              Tiếp tục
            </button>
          )}
          {step === 3 && (
            <button className="qa__btn qa__btn--primary" onClick={() => { onComplete?.(); close(); }}>
              <Icon name="check" />Nạp {file ? "67" : "0"} khách
            </button>
          )}
        </>
      }
    >
      {step === 1 && (
        <>
          <div className="setgrp">
            <div className="setgrp__head">
              <Icon name="file" />
              Cách 1 · Tải mẫu Excel có sẵn
              <span className="setgrp__head-sub">Khuyến nghị cho lần đầu</span>
            </div>
            <p className="small" style={{ marginTop: 0 }}>
              Tải file mẫu đã có sẵn các cột (Họ tên, Email, SĐT, Nhóm…). Mở bằng Excel, điền thông tin khách rồi tải lên ở Cách 2.
            </p>
            <div className="qa">
              <Field label="Số mã QR sẵn trong file" tip="Mỗi dòng = 1 khách + 1 mã QR. Đặt thừa cũng được — không dùng hết không sao.">
                <input className="input" type="number" defaultValue="100" style={{ width: 120 }} />
              </Field>
              <Field label="Tên nhóm khách (tùy chọn)" hint="VD: VIP, Báo chí…">
                <input className="input" placeholder="Để trống nếu không phân nhóm" value={groupName} onChange={(e) => setGroupName(e.target.value)} style={{ width: 240 }} />
              </Field>
            </div>
            <div className="qa" style={{ marginTop: 12 }}>
              <button className="qa__btn"><Icon name="file" /> Tải file mẫu (.xlsx)</button>
              <button className="qa__btn"><Icon name="file" /> Tải file mẫu (.csv)</button>
            </div>
          </div>

          <div className="divider" />

          <div className="setgrp">
            <div className="setgrp__head">
              <Icon name="upload" />
              Cách 2 · Tải lên file đã điền
            </div>
            <label className="upload" htmlFor="excel-input" style={{ display: "block" }}>
              <Icon name="upload" className="upload__icon" />
              <p className="upload__title">{file ? file.name : "Kéo file Excel vào đây hoặc bấm để chọn"}</p>
              <p className="upload__sub">Hỗ trợ .xlsx, .xls, .csv — tối đa 10MB · ~5,000 khách / lần</p>
              <input
                id="excel-input"
                type="file"
                accept=".xlsx,.xls,.csv"
                style={{ display: "none" }}
                onChange={(e) => e.target.files?.[0] && onFile(e.target.files[0])}
              />
            </label>
          </div>
        </>
      )}

      {step === 2 && (
        <>
          <p className="small" style={{ marginTop: 0 }}>
            Hệ thống đã đọc <b style={{ color: "var(--text)" }}>67 dòng</b> từ file <b style={{ color: "var(--text)" }}>{file?.name || "khach-mk-thang3.xlsx"}</b>.
            Đối chiếu cột trong Excel với trường thông tin trong hệ thống.
          </p>
          <div className="map-table">
            <div className="map-table__head">
              <span>Cột trong Excel</span><span>↔</span><span>Trường trong hệ thống</span><span>Mẫu</span>
            </div>
            {[
              ["Họ và tên", "name", "Nguyễn Lan Anh"],
              ["Email", "email", "lananh@gmail.com"],
              ["Số điện thoại", "phone_number", "0901234567"],
              ["Đơn vị / Công ty", "company_name", "FOREST Medical"],
              ["Nhóm khách", "group", "VIP"],
              ["Mã giới thiệu", "ref_code", "REF-001"],
            ].map(([excel, target, sample], i) => (
              <div className="map-table__row" key={i}>
                <span className="map-table__col"><code>{excel}</code></span>
                <span style={{ color: "var(--text-faint)" }}>→</span>
                <select className="select" defaultValue={target}>
                  <option value="name">Họ và tên</option>
                  <option value="email">Email</option>
                  <option value="phone_number">Số điện thoại</option>
                  <option value="company_name">Nơi công tác</option>
                  <option value="group">Nhóm khách</option>
                  <option value="ref_code">Mã giới thiệu</option>
                  <option value="__skip">— Bỏ qua —</option>
                </select>
                <span className="map-table__sample">{sample}</span>
              </div>
            ))}
          </div>
        </>
      )}

      {step === 3 && (
        <>
          <p className="small" style={{ marginTop: 0 }}>Kiểm tra lại trước khi nạp dữ liệu vào sự kiện.</p>
          <div className="review-grid">
            <div className="review-grid__cell">
              <span className="review-grid__label">Số khách sẽ nạp</span>
              <b className="review-grid__value">67</b>
            </div>
            <div className="review-grid__cell">
              <span className="review-grid__label">Đã tồn tại (sẽ bỏ qua)</span>
              <b className="review-grid__value" style={{ color: "var(--warning)" }}>3</b>
            </div>
            <div className="review-grid__cell">
              <span className="review-grid__label">Trùng dòng trong file</span>
              <b className="review-grid__value">0</b>
            </div>
            <div className="review-grid__cell">
              <span className="review-grid__label">Sẽ sinh mã QR mới</span>
              <b className="review-grid__value" style={{ color: "var(--success)" }}>64</b>
            </div>
          </div>

          <SetGroup icon="settings" title="Quy tắc nạp">
            <Field label="Phát hiện trùng lặp theo">
              <select className="select" value={dedupe} onChange={(e) => setDedupe(e.target.value)}>
                <option value="email">Email</option>
                <option value="phone">Số điện thoại</option>
                <option value="email+phone">Email và Số điện thoại</option>
                <option value="none">Không kiểm tra — luôn thêm mới</option>
              </select>
            </Field>
            <OptRow
              title="Sinh mã QR tự động khi nạp"
              desc="Mỗi khách mới sẽ được cấp 1 mã QR riêng theo cấu hình tab Mã QR"
              on={autoQR}
              onChange={setAutoQR}
            />
            <OptRow title="Gửi email mời ngay sau khi nạp" desc="Khách sẽ nhận email kèm mã QR" on={false} onChange={() => {}} />
          </SetGroup>
        </>
      )}
    </Modal>
  );
};

// ---------------- Add guest modal ----------------
const AddGuestModal = ({ open, onClose, onAdd }) => {
  const [form, setForm] = gUS({
    name: "", email: "", phone: "", company: "", group: "Khách thường",
    title: "mr", sendEmail: true,
  });
  const upd = (k, v) => setForm({ ...form, [k]: v });
  const reset = () => setForm({ name: "", email: "", phone: "", company: "", group: "Khách thường", title: "mr", sendEmail: true });
  const close = () => { reset(); onClose(); };
  const submit = () => {
    if (!form.name.trim()) { alert("Vui lòng nhập họ và tên"); return; }
    onAdd?.(form);
    close();
  };

  return (
    <Modal
      open={open}
      onClose={close}
      title="Thêm khách mời mới"
      sub="Nhập thông tin khách — mã QR sẽ được sinh tự động"
      icon="plus"
      size="md"
      footer={
        <>
          <button className="qa__btn" onClick={close}>Hủy</button>
          <div style={{ flex: 1 }} />
          <button className="qa__btn" onClick={() => { submit(); /* keep open for next */ }}>Lưu & thêm tiếp</button>
          <button className="qa__btn qa__btn--primary" onClick={submit}><Icon name="check" />Lưu khách</button>
        </>
      }
    >
      <div className="grid grid--2">
        <Field label="Danh xưng">
          <select className="select" value={form.title} onChange={(e) => upd("title", e.target.value)}>
            <option value="dr">Dr.</option><option value="mr">Mr.</option>
            <option value="ms">Ms.</option><option value="mrs">Mrs.</option>
          </select>
        </Field>
        <Field label="Nhóm khách" tip="Dùng để lọc trong báo cáo và sinh mã QR riêng theo nhóm.">
          <select className="select" value={form.group} onChange={(e) => upd("group", e.target.value)}>
            {GUEST_GROUPS.map((g) => <option key={g}>{g}</option>)}
          </select>
        </Field>
        <Field label="Họ và tên" required>
          <input className="input" value={form.name} onChange={(e) => upd("name", e.target.value)} placeholder="Nguyễn Văn A" autoFocus />
        </Field>
        <Field label="Email" hint="Sẽ dùng để gửi mã QR">
          <input className="input" type="email" value={form.email} onChange={(e) => upd("email", e.target.value)} placeholder="a.nguyen@email.com" />
        </Field>
        <Field label="Số điện thoại">
          <input className="input" value={form.phone} onChange={(e) => upd("phone", e.target.value)} placeholder="09xxxxxxxx" />
        </Field>
        <Field label="Đơn vị / Công ty">
          <input className="input" value={form.company} onChange={(e) => upd("company", e.target.value)} placeholder="FOREST Medical" />
        </Field>
      </div>
      <div className="divider" />
      <OptRow
        title="Gửi email mời + mã QR ngay sau khi lưu"
        desc="Email gửi đến địa chỉ ở ô Email phía trên"
        on={form.sendEmail}
        onChange={(v) => upd("sendEmail", v)}
        tip="Mẫu email lấy từ tab 'Thiệp & email'."
      />
    </Modal>
  );
};

// ---------------- QR preview modal ----------------
const QRPreviewModal = ({ open, onClose, guest }) => {
  if (!guest) return null;
  return (
    <Modal open={open} onClose={onClose} title={"Mã QR · " + guest.name} sub={guest.qrId} icon="qr" size="sm"
      footer={
        <>
          <button className="qa__btn" onClick={onClose}>Đóng</button>
          <div style={{ flex: 1 }} />
          <button className="qa__btn"><Icon name="copy" />Sao chép mã</button>
          <button className="qa__btn qa__btn--primary"><Icon name="upload" style={{ transform: "rotate(180deg)" }} />Tải PNG</button>
        </>
      }
    >
      <div style={{ display: "flex", justifyContent: "center", padding: "10px 0" }}>
        <div style={{ width: 260, height: 260, padding: 18, background: "#fff", border: "1px solid var(--border)", borderRadius: 12 }}>
          <QRRenderer
              s={{ fgColor: "#000", bgColor: "#fff", logoSize: "30%" }}
              sample={guest.qrId}
              showLogo
              logoSrc="assets/in-logo.png"
            />
        </div>
      </div>
      <div className="grid grid--2" style={{ gap: 8, marginTop: 10 }}>
        <div className="kpi" style={{ padding: 10 }}>
          <div className="kpi__label">Trạng thái</div>
          <b style={{ color: guest.checkedIn ? "var(--success)" : "var(--text-muted)" }}>
            {guest.checkedIn ? "Đã check-in · " + guest.checkedInAt : "Chưa check-in"}
          </b>
        </div>
        <div className="kpi" style={{ padding: 10 }}>
          <div className="kpi__label">Đăng ký lúc</div>
          <b>{guest.registeredAt}</b>
        </div>
      </div>
    </Modal>
  );
};

// ---------------- Main section ----------------
const SectionGuests = ({ eventId }) => {
  const t = useT();
  const [guests, setGuests] = gUS(() => generateGuests(eventId, 67));
  const [q, setQ] = gUS("");
  const [statusF, setStatusF] = gUS("all"); // all | checked | unchecked
  const [groupF, setGroupF] = gUS("all");
  const [sourceF, setSourceF] = gUS("all");
  const [page, setPage] = gUS(1);
  const PER_PAGE = 8;

  const [showImport, setShowImport] = gUS(false);
  const [showAdd, setShowAdd] = gUS(false);
  const [previewGuest, setPreviewGuest] = gUS(null);

  const checkedCount = gUM(() => guests.filter((g) => g.checkedIn).length, [guests]);

  const filtered = gUM(() => {
    const ql = q.toLowerCase();
    return guests.filter((g) => {
      if (statusF === "checked" && !g.checkedIn) return false;
      if (statusF === "unchecked" && g.checkedIn) return false;
      if (groupF !== "all" && g.group !== groupF) return false;
      if (sourceF !== "all" && g.source !== sourceF) return false;
      if (ql && !g.name.toLowerCase().includes(ql) && !g.email.toLowerCase().includes(ql) && !g.qrId.toLowerCase().includes(ql)) return false;
      return true;
    });
  }, [guests, q, statusF, groupF, sourceF]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const pageItems = filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE);

  const resetFilters = () => { setQ(""); setStatusF("all"); setGroupF("all"); setSourceF("all"); };

  const checkin = (id) => {
    setGuests(guests.map((g) => g.id === id ? { ...g, checkedIn: !g.checkedIn, checkedInAt: !g.checkedIn ? "vừa xong" : null } : g));
  };

  const onAdd = (form) => {
    const newGuest = {
      id: "g-new-" + Date.now(),
      qrId: `QR-${String(guests.length + 1).padStart(4, "0")}`,
      name: form.name,
      email: form.email,
      phone: form.phone,
      group: form.group,
      source: "MANUAL",
      registeredAt: "vừa xong",
      status: "active",
      checkedIn: false,
      checkedInAt: null,
    };
    setGuests([newGuest, ...guests]);
  };

  return (
    <>
      <Card
        title={t("Tổng quan khách mời", "Guest overview")}
        icon="users"
        action={
          <>
            <button className="qa__btn" onClick={() => setShowImport(true)}>
              <Icon name="upload" />
              <span className="qa__btn-label">{t("Nạp từ Excel", "Import Excel")}</span>
            </button>
            <button className="qa__btn qa__btn--primary" onClick={() => setShowAdd(true)}>
              <Icon name="plus" />
              <span className="qa__btn-label">{t("Thêm khách", "Add guest")}</span>
            </button>
          </>
        }
      >
        <div className="guest-kpi-row">
          <div className="guest-kpi">
            <span className="guest-kpi__label">{t("Tổng khách mời", "Total guests")}</span>
            <b className="guest-kpi__value">{guests.length}</b>
          </div>
          <div className="guest-kpi">
            <span className="guest-kpi__label">{t("Đã check-in", "Checked in")}</span>
            <b className="guest-kpi__value" style={{ color: "var(--success)" }}>
              {checkedCount} <span style={{ fontSize: 13, color: "var(--text-muted)", fontWeight: 400 }}>/ {guests.length} ({Math.round(checkedCount / guests.length * 100)}%)</span>
            </b>
            <div className="guest-kpi__bar"><div style={{ width: (checkedCount / guests.length * 100) + "%" }} /></div>
          </div>
          <div className="guest-kpi">
            <span className="guest-kpi__label">{t("Đã gửi mã QR", "QR sent")}</span>
            <b className="guest-kpi__value">{guests.length}</b>
          </div>
          <div className="guest-kpi">
            <span className="guest-kpi__label">{t("Nguồn đăng ký", "Sources")}</span>
            <b className="guest-kpi__value" style={{ fontSize: 16 }}>
              Import {guests.filter((g) => g.source === "IMPORT").length} ·
              Form {guests.filter((g) => g.source === "FORM").length}
            </b>
          </div>
        </div>
      </Card>

      <Card
        title={t("Danh sách khách mời", "Guest list")}
        sub={t("Tìm kiếm, lọc, check-in và xem mã QR của từng khách", "Search, filter, check in and view QR for each guest")}
        icon="users"
      >
        {/* Filters */}
        <div className="guest-filters">
          <div className="guest-search">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
            <input placeholder={t("Tìm theo tên, email, mã QR…", "Search by name, email, QR code…")} value={q} onChange={(e) => { setQ(e.target.value); setPage(1); }} />
          </div>
          <select className="select select--inline" value={statusF} onChange={(e) => { setStatusF(e.target.value); setPage(1); }}>
            <option value="all">{t("Trạng thái: Tất cả", "Status: All")}</option>
            <option value="checked">{t("Đã check-in", "Checked in")}</option>
            <option value="unchecked">{t("Chưa check-in", "Not checked in")}</option>
          </select>
          <select className="select select--inline" value={groupF} onChange={(e) => { setGroupF(e.target.value); setPage(1); }}>
            <option value="all">{t("Nhóm: Tất cả", "Group: All")}</option>
            {GUEST_GROUPS.map((g) => <option key={g}>{g}</option>)}
          </select>
          <select className="select select--inline" value={sourceF} onChange={(e) => { setSourceF(e.target.value); setPage(1); }}>
            <option value="all">{t("Nguồn: Tất cả", "Source: All")}</option>
            {GUEST_SOURCES.map((s) => <option key={s}>{s}</option>)}
          </select>
          {(q || statusF !== "all" || groupF !== "all" || sourceF !== "all") && (
            <button className="qa__btn qa__btn--ghost" onClick={resetFilters}>{t("Xóa lọc", "Clear filters")}</button>
          )}
          <div style={{ flex: 1 }} />
          <span className="small">{t("Hiện", "Showing")} {filtered.length} / {guests.length} {t("khách", "guests")}</span>
        </div>

        {/* DESKTOP table */}
        <div className="guest-table-wrap">
          <table className="guest-table">
            <thead>
              <tr>
                <th style={{ width: 36 }}><input type="checkbox" /></th>
                <th>{t("Khách mời", "Guest")}</th>
                <th>{t("Nhóm", "Group")}</th>
                <th>{t("Nguồn", "Source")}</th>
                <th>{t("Đăng ký", "Registered")}</th>
                <th>{t("Trạng thái", "Status")}</th>
                <th>{t("Mã QR", "QR code")}</th>
                <th style={{ width: 80 }}></th>
              </tr>
            </thead>
            <tbody>
              {pageItems.map((g) => (
                <tr key={g.id}>
                  <td><input type="checkbox" /></td>
                  <td>
                    <div className="guest-cell">
                      <div className="guest-cell__avatar" style={{ background: avatarColor(g.name) }}>
                        {g.name.split(" ").slice(-2).map((w) => w[0]).join("")}
                      </div>
                      <div className="guest-cell__main">
                        <div className="guest-cell__name">{g.name}</div>
                        <div className="guest-cell__sub">{g.email || <span style={{ color: "var(--text-faint)" }}>—</span>} {g.phone && <span style={{ color: "var(--text-faint)" }}>· {g.phone}</span>}</div>
                      </div>
                    </div>
                  </td>
                  <td><span className="g-chip">{g.group}</span></td>
                  <td><span className="g-chip g-chip--src">{g.source}</span></td>
                  <td className="small">{g.registeredAt}</td>
                  <td>
                    {g.checkedIn ? (
                      <span className="g-status g-status--checked"><Icon name="check" size={12} />{t("Đã check-in", "Checked in")} {g.checkedInAt && <span style={{ opacity: 0.7 }}>· {g.checkedInAt}</span>}</span>
                    ) : (
                      <span className="g-status g-status--pending">{t("Chưa check-in", "Pending")}</span>
                    )}
                  </td>
                  <td>
                    <button className="g-qrbtn" onClick={() => setPreviewGuest(g)} title={t("Xem mã QR", "View QR code")}>
                      <span className="g-qrbtn__mini">
                        <QRRenderer s={{ fgColor: "#000", bgColor: "#fff" }} sample={g.qrId} dotShape="square" />
                      </span>
                      <span className="g-qrbtn__id">{g.qrId}</span>
                    </button>
                  </td>
                  <td>
                    <div style={{ display: "flex", gap: 4 }}>
                      <button className="icon-btn" title={t("Check-in nhanh", "Quick check-in")} onClick={() => checkin(g.id)}>
                        <Icon name={g.checkedIn ? "refresh" : "check"} />
                      </button>
                      <button className="icon-btn" title={t("Gửi email mời", "Send invite email")}><Icon name="mail" /></button>
                      <button className="icon-btn" title={t("Sửa", "Edit")}><Icon name="settings" /></button>
                    </div>
                  </td>
                </tr>
              ))}
              {pageItems.length === 0 && (
                <tr><td colSpan={8}>
                  <div className="empty" style={{ padding: "28px 12px" }}>
                    <Icon name="users" size={36} className="empty__icon" />
                    <p className="empty__title">{t("Không có khách nào phù hợp", "No matching guests")}</p>
                    <p>{t("Thử bỏ bộ lọc hoặc thêm khách mới.", "Try removing filters or add new guests.")}</p>
                  </div>
                </td></tr>
              )}
            </tbody>
          </table>
        </div>

        {/* MOBILE cards */}
        <div className="guest-cards">
          {pageItems.map((g) => (
            <div key={g.id} className="guest-mc">
              <div className="guest-mc__head">
                <div className="guest-cell__avatar" style={{ background: avatarColor(g.name) }}>
                  {g.name.split(" ").slice(-2).map((w) => w[0]).join("")}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div className="guest-cell__name">{g.name}</div>
                  <div className="guest-cell__sub">{g.email || "—"}</div>
                </div>
                <button className="g-qrbtn" onClick={() => setPreviewGuest(g)}>
                  <span className="g-qrbtn__mini">
                    <QRRenderer s={{ fgColor: "#000", bgColor: "#fff" }} sample={g.qrId} />
                  </span>
                </button>
              </div>
              <div className="guest-mc__meta">
                <span className="g-chip">{g.group}</span>
                <span className="g-chip g-chip--src">{g.source}</span>
                {g.checkedIn ? (
                  <span className="g-status g-status--checked"><Icon name="check" size={11} />{t("Đã check-in", "Checked in")}</span>
                ) : (
                  <span className="g-status g-status--pending">{t("Chưa check-in", "Pending")}</span>
                )}
              </div>
              <div className="guest-mc__foot">
                <span className="small">{g.qrId} · {g.registeredAt}</span>
                <div style={{ display: "flex", gap: 4 }}>
                  <button className="icon-btn" onClick={() => checkin(g.id)}><Icon name={g.checkedIn ? "refresh" : "check"} /></button>
                  <button className="icon-btn"><Icon name="mail" /></button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Pagination */}
        {pageCount > 1 && (
          <div className="guest-page">
            <button className="qa__btn qa__btn--ghost" disabled={page === 1} onClick={() => setPage(page - 1)}>
              <Icon name="chevron_left" />{t("Trước", "Prev")}
            </button>
            <div className="guest-page__nums">
              {Array.from({ length: pageCount }).map((_, i) => (
                <button
                  key={i}
                  className={"guest-page__num" + (i + 1 === page ? " is-active" : "")}
                  onClick={() => setPage(i + 1)}
                >{i + 1}</button>
              ))}
            </div>
            <button className="qa__btn qa__btn--ghost" disabled={page === pageCount} onClick={() => setPage(page + 1)}>
              {t("Sau", "Next")}<Icon name="chevron_right" />
            </button>
          </div>
        )}
      </Card>

      <ImportGuestsModal open={showImport} onClose={() => setShowImport(false)} onComplete={() => alert(t("Đã nạp 67 khách", "67 guests imported"))} />
      <AddGuestModal open={showAdd} onClose={() => setShowAdd(false)} onAdd={onAdd} />
      <QRPreviewModal open={!!previewGuest} guest={previewGuest} onClose={() => setPreviewGuest(null)} />
    </>
  );
};

function avatarColor(seed) {
  const palette = ["#2563eb", "#16a34a", "#dc2626", "#7c3aed", "#0891b2", "#ca8a04", "#db2777", "#0f766e"];
  let h = 0;
  for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) | 0;
  return palette[Math.abs(h) % palette.length];
}

Object.assign(window, { SectionGuests, ImportGuestsModal, AddGuestModal, QRPreviewModal });
