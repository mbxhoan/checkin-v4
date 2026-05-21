// sections.jsx — individual config sections

// ============= 1. EVENT INFO =============
const SectionEventInfo = ({ data, setData }) => {
  const upd = (k, v) => setData({ ...data, [k]: v });
  return (
    <>
      <Card title="Thông tin chung" sub="Thông tin cơ bản về sự kiện hiển thị khắp nơi trong hệ thống" icon="calendar">
        <div className="grid grid--2">
          <Field
            label="Công ty tổ chức"
            required
            tip="Sự kiện sẽ thuộc về công ty này. Không thể thay đổi sau khi tạo."
          >
            <select className="select" value={data.companyId} onChange={(e) => upd("companyId", e.target.value)}>
              <option value="cmp1">CMP-L3IP2V1P — FOREST Medical</option>
              <option value="cmp2">CMP-DENT — Dental Vietnam</option>
            </select>
          </Field>
          <Field
            label="Tên sự kiện"
            required
            hint="VD: Hội thảo VIDEC 2026, Đại hội cổ đông…"
          >
            <input className="input" value={data.name} onChange={(e) => upd("name", e.target.value)} />
          </Field>
          <Field
            label="Mã định danh sự kiện"
            tip="Mã ngắn, không dấu, dùng trong URL và báo cáo. Tự sinh từ tên — chỉ đổi nếu bạn cần URL tùy chỉnh."
            hint="Sẽ xuất hiện trong link đăng ký: checkin.delfi.vn/r/videc-2026"
          >
            <input
              className="input"
              value={data.slug}
              onChange={(e) => upd("slug", toKey(e.target.value))}
              style={{ fontFamily: "'SF Mono', Menlo, monospace", fontSize: 13 }}
            />
          </Field>
          <Field label="Trạng thái" tip="Trạng thái nội bộ. Khi 'Đã kết thúc', sự kiện chỉ xem được, không nhận đăng ký mới.">
            <select className="select" value={data.status} onChange={(e) => upd("status", e.target.value)}>
              <option>Đang triển khai</option>
              <option>Sắp diễn ra</option>
              <option>Đã kết thúc</option>
              <option>Bản nháp</option>
            </select>
          </Field>
        </div>

        <div className="divider" />

        <div className="grid grid--3">
          <Field label="Tỉnh / Thành phố" required>
            <select className="select" value={data.city} onChange={(e) => upd("city", e.target.value)}>
              <option>Hồ Chí Minh</option><option>Hà Nội</option><option>Đà Nẵng</option><option>Cần Thơ</option>
            </select>
          </Field>
          <Field label="Ngày bắt đầu" required>
            <input className="input" type="date" value={data.startDate} onChange={(e) => upd("startDate", e.target.value)} />
          </Field>
          <Field label="Ngày kết thúc" required>
            <input className="input" type="date" value={data.endDate} onChange={(e) => upd("endDate", e.target.value)} />
          </Field>
        </div>

        <div style={{ marginTop: 16 }}>
          <Field label="Mô tả ngắn" hint="Hiển thị trên trang đăng ký công khai và email mời">
            <textarea className="textarea input" rows="3" value={data.description}
              onChange={(e) => upd("description", e.target.value)}
              placeholder="VD: Hội thảo nha khoa thường niên 2026 — quy tụ hơn 500 chuyên gia hàng đầu trong khu vực." />
          </Field>
        </div>
      </Card>
    </>
  );
};

// ============= 2. REGISTRATION FORM =============
const SectionForm = ({ fields, setFields, settings, setSettings }) => {
  const upd = (k, v) => setSettings({ ...settings, [k]: v });
  return (
    <>
      <Card
        title="Trang đăng ký công khai"
        sub="Bật tính năng cho phép khách tự đăng ký qua link. Khi tắt, chỉ ban tổ chức nhập khách."
        icon="layout"
      >
        <OptRow
          title="Cho phép khách tự đăng ký"
          desc="Mở form trên trang đăng ký công khai (link checkin.delfi.vn/r/…)"
          tip="Khi bật, bất cứ ai có link đều có thể đăng ký. Khi tắt, chỉ ban tổ chức thêm khách thủ công hoặc nạp danh sách."
          on={settings.formOpen}
          onChange={(v) => upd("formOpen", v)}
        />
        <OptRow
          title="Bảo vệ chống đăng ký tự động (Captcha)"
          desc="Yêu cầu khách xác minh không phải robot trước khi gửi"
          tip="Bật nếu sự kiện công khai để tránh bot spam. Khách sẽ thấy ô tích 'Tôi không phải là robot'."
          on={settings.captcha}
          onChange={(v) => upd("captcha", v)}
          disabled={!settings.formOpen}
        />
        <OptRow
          title="Cho phép quét thẻ căn cước (CCCD)"
          desc="Khách quét CCCD để tự động điền thông tin"
          tip="Khi bật, trên trang đăng ký xuất hiện nút quét CCCD. Hệ thống đọc QR mặt sau CCCD và điền sẵn họ tên, ngày sinh, địa chỉ."
          on={settings.cccd}
          onChange={(v) => upd("cccd", v)}
          disabled={!settings.formOpen}
        />
        <OptRow
          title="Tự động check-in khi đăng ký thành công"
          desc="Khách đăng ký xong sẽ được tính là đã có mặt"
          tip="Phù hợp cho sự kiện mở (drop-in). Đừng bật nếu khách đăng ký trước rồi mới đến."
          on={settings.autoCheckin}
          onChange={(v) => upd("autoCheckin", v)}
        />
        <OptRow
          title="Quét được thẻ tên đã in"
          desc="Cho phép dùng máy quét thẻ tên (NFC) để check-in"
          tip="Cần thiết bị NFC hỗ trợ. Khi bật, sự kiện này sẽ xuất hiện trong danh sách máy quét."
          on={settings.nfcBadge}
          onChange={(v) => upd("nfcBadge", v)}
        />
        <OptRow
          title="Gửi email xác nhận cho khách"
          desc="Sau khi đăng ký xong, khách nhận email kèm mã QR"
          tip="Yêu cầu trường Email được bật ở danh sách thông tin bên dưới. Mẫu email lấy từ tab 'Thư mời'."
          on={settings.confirmEmail}
          onChange={(v) => upd("confirmEmail", v)}
        />
      </Card>

      <Card
        title="Thông tin thu thập từ khách"
        sub="Sắp xếp các trường thông tin sẽ hỏi khách. Kéo thả để đổi thứ tự."
        icon="users"
        action={
          <span className="small">
            <b style={{ color: "var(--text)" }}>{fields.length}</b> trường —
            {" "}<b style={{ color: "var(--text)" }}>{fields.filter((f) => f.shownOnForm).length}</b> hiện trên form,
            {" "}<b style={{ color: "var(--text)" }}>{fields.filter((f) => f.required).length}</b> bắt buộc
          </span>
        }
      >
        <FieldEditor fields={fields} setFields={setFields} />
      </Card>
    </>
  );
};

// NOTE: SectionQR moved to qr-section.jsx (new 70/30 split + live preview)

// ============= 4. CHECK-IN =============
const SectionCheckin = ({ s, set }) => {
  const upd = (k, v) => set({ ...s, [k]: v });
  const Device = ({ icon, title, prefix }) => (
    <Card title={title} icon={icon} sub={icon === "desktop" ? "Khi check-in bằng máy tính / quầy lễ tân" : "Khi check-in bằng điện thoại / tablet"}>
      <OptRow title="Hiển thị số lần đã check-in"
        desc="Cho nhân viên thấy khách đã đến mấy lần (sự kiện nhiều ngày)"
        tip="Hữu ích khi 1 khách check-in nhiều lần — VD: vào cửa, vào hội thảo riêng, gala dinner."
        on={s[prefix + "ShowCount"]} onChange={(v) => upd(prefix + "ShowCount", v)} />
      <OptRow title="Tự in tem dán khi check-in"
        desc="Yêu cầu máy in tem được kết nối"
        tip="Khi check-in thành công, máy in sẽ tự động in tem dán có tên khách. Cần thiết bị in tem tương thích."
        on={s[prefix + "PrintLabel"]} onChange={(v) => upd(prefix + "PrintLabel", v)} />
      <OptRow title="Check-in bằng camera"
        desc="Quét mã QR khách qua camera của máy"
        tip="Bật để nhân viên có thể quét QR khách bằng webcam/camera tablet. Tắt nếu chỉ dùng máy quét chuyên dụng."
        on={s[prefix + "Camera"]} onChange={(v) => upd(prefix + "Camera", v)} />
      <OptRow title="Cho phép check-in thủ công (không quét)"
        desc="Nhân viên tìm khách theo tên rồi bấm check-in"
        tip="Dự phòng khi khách quên QR. Nhân viên gõ tên/SĐT để tra cứu."
        on={s[prefix + "Manual"]} onChange={(v) => upd(prefix + "Manual", v)} />
      <SetGroup icon="bell" title="Cảnh báo trùng lặp" sub="Ngăn 1 khách check-in 2 lần">
        <OptRow title="Theo mã QR" desc="Một mã QR chỉ check-in 1 lần"
          tip="Nếu cùng 1 QR được quét lần 2, hệ thống sẽ báo 'Đã check-in lúc HH:mm'."
          on={s[prefix + "DupQR"]} onChange={(v) => upd(prefix + "DupQR", v)} />
        <OptRow title="Theo ngày" desc="Cho phép check-in lại vào ngày hôm sau"
          tip="Phù hợp sự kiện nhiều ngày. Khách check-in lần 1 ngày 1, lần 2 ngày 2 — đều hợp lệ."
          on={s[prefix + "DupDay"]} onChange={(v) => upd(prefix + "DupDay", v)} />
        <OptRow title="Theo nhân viên check-in" desc="Cùng 1 khách không bị 2 nhân viên check-in cùng lúc"
          tip="Tránh tình huống 2 quầy cùng quét 1 khách. Một khi đã check-in, máy khác sẽ báo trùng."
          on={s[prefix + "DupUser"]} onChange={(v) => upd(prefix + "DupUser", v)} />
      </SetGroup>
      <OptRow title="Phát âm báo khi check-in thành công"
        desc="Phát tiếng 'beep' để xác nhận"
        tip="Hữu ích khi nhân viên không nhìn màn hình liên tục. Tắt trong phòng yên tĩnh."
        on={s[prefix + "Sound"]} onChange={(v) => upd(prefix + "Sound", v)} />
    </Card>
  );
  return (
    <>
      {/* IN app banner */}
      <div className="in-banner">
        <div className="in-banner__logo">
          <img src="assets/in-logo.png" alt="IN" />
        </div>
        <div className="in-banner__body">
          <div className="in-banner__title">
            <b>IN</b>
            <span className="in-banner__tag">App check-in chính thức</span>
          </div>
          <p className="in-banner__desc">
            Dùng app <b>IN</b> trên điện thoại / tablet để quét mã QR khách. Cài đặt bên dưới sẽ áp dụng ngay khi nhân viên đăng nhập app.
          </p>
        </div>
        <div className="in-banner__actions">
          <button className="qa__btn"><Icon name="phone" /><span className="qa__btn-label">Tải cho iOS</span></button>
          <button className="qa__btn"><Icon name="phone" /><span className="qa__btn-label">Tải cho Android</span></button>
        </div>
      </div>

      <Device icon="desktop" title="Trên máy tính (quầy lễ tân)" prefix="desktop" />
      <Device icon="phone" title="Trên điện thoại / tablet (app IN)" prefix="mobile" />
    </>
  );
};

// ============= 5. IMAGES =============
const SectionImages = ({ s, set }) => {
  const Slot = ({ label, sub, tip, name }) => (
    <div>
      <div style={{ display: "flex", alignItems: "center", gap: 6, marginBottom: 6, fontWeight: 500, fontSize: 13 }}>
        {label}
        {tip && <Tip text={tip} />}
      </div>
      <div className="upload" tabIndex={0}>
        <Icon name="upload" className="upload__icon" />
        <p className="upload__title">Kéo thả ảnh vào đây</p>
        <p className="upload__sub">{sub}</p>
      </div>
    </div>
  );
  return (
    <Card title="Hình ảnh & nhận diện sự kiện" sub="Logo, ảnh nền, biểu tượng dùng cho trang đăng ký, email và màn check-in" icon="image">
      <div className="grid grid--2">
        <Slot label="Logo công ty / sự kiện" sub="PNG nền trong suốt, tối thiểu 200×200"
          tip="Xuất hiện ở góc trên trang đăng ký, email mời và mặt trước mã QR (nếu bật)." />
        <Slot label="Biểu tượng (Favicon)" sub="32×32 hoặc 64×64, định dạng PNG/ICO"
          tip="Hình nhỏ hiện trên tab trình duyệt khi khách mở trang đăng ký. Thường là logo thu nhỏ." />
        <Slot label="Ảnh nền màn check-in (máy tính)" sub="1920×1080, JPG/PNG"
          tip="Hiển thị toàn màn hình khi check-in trên máy tính tại quầy. Tránh dùng ảnh quá rối — chữ check-in sẽ chồng lên." />
        <Slot label="Ảnh nền màn check-in (điện thoại)" sub="1080×1920 (dọc), JPG/PNG"
          tip="Cho thiết bị di động khi check-in dọc. Ảnh khác với máy tính." />
      </div>
      <div className="divider" />
      <Slot label="Ảnh tùy chọn khác" sub="Banner, tài liệu, ảnh ghi nhớ… (tùy chọn)" />
    </Card>
  );
};

// NOTE: SectionGuests moved to guests-section.jsx (with mock list + import/add modals)

// ============= 7. LANDING / 8. INVITATION / 9. PRINT (lighter placeholders) =============
const SectionLanding = () => (
  <Card title="Trang đăng ký công khai" sub="Tùy biến giao diện link đăng ký" icon="layout">
    <div className="empty">
      <Icon name="layout" size={44} className="empty__icon" />
      <p className="empty__title">Sẵn sàng cấu hình</p>
      <p>Phần này thiết kế chi tiết giao diện trang đăng ký — màu sắc, banner, mẫu giới thiệu, sơ đồ địa điểm.</p>
      <button className="qa__btn qa__btn--primary" style={{ marginTop: 12 }}>
        <Icon name="eye" /> Mở trình thiết kế
      </button>
    </div>
  </Card>
);

const SectionInvite = () => (
  <Card title="Thiệp mời & Email" sub="Soạn email mời, thiệp điện tử, mẫu xác nhận đăng ký" icon="invite">
    <div className="empty">
      <Icon name="mail" size={44} className="empty__icon" />
      <p className="empty__title">3 mẫu thư đang dùng</p>
      <p>Email mời • Email xác nhận đăng ký • Email nhắc lịch trước sự kiện 1 ngày</p>
      <button className="qa__btn qa__btn--primary" style={{ marginTop: 12 }}>
        <Icon name="mail" /> Sửa mẫu thư
      </button>
    </div>
  </Card>
);

const SectionPrint = () => (
  <Card title="Mẫu in thẻ tên / vé" sub="Thiết kế thẻ in cho khách khi check-in" icon="print">
    <div className="empty">
      <Icon name="print" size={44} className="empty__icon" />
      <p className="empty__title">Chưa có mẫu in</p>
      <p>Thiết kế mẫu thẻ tên/vé in ra giấy hoặc thẻ nhựa.</p>
      <button className="qa__btn qa__btn--primary" style={{ marginTop: 12 }}>
        <Icon name="plus" /> Tạo mẫu mới
      </button>
    </div>
  </Card>
);

Object.assign(window, {
  SectionEventInfo, SectionForm, SectionCheckin,
  SectionImages, SectionLanding, SectionInvite, SectionPrint,
});
