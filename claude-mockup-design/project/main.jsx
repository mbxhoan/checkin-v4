// main.jsx — SaaS shell with global navigation, event switcher, and routing

const { useState: uS, useEffect: uE, useMemo: uM, useCallback: uC } = React;

const DEFAULT_FIELDS = [
  { id: 1, label: "Mã QR cá nhân", key: "qrcode", type: "code", required: true, unique: true, shownOnForm: false, locked: true },
  { id: 2, label: "Họ và tên", key: "name", type: "text", required: true, unique: false, shownOnForm: true, locked: true },
  { id: 3, label: "Email", key: "email", type: "email", required: false, unique: true, shownOnForm: true, locked: true },
  { id: 4, label: "Địa chỉ", key: "address", type: "text", required: false, unique: false, shownOnForm: true },
  { id: 5, label: "Nghề nghiệp", key: "job_title", type: "select", required: false, unique: false, shownOnForm: true,
    options: [
      { label: "Bác sĩ", value: "dentist" },
      { label: "Điều dưỡng", value: "assistant" },
      { label: "Kỹ thuật viên", value: "technician" },
      { label: "Sinh viên", value: "student" },
    ] },
  { id: 6, label: "Số điện thoại", key: "phone_number", type: "phone", required: false, unique: false, shownOnForm: true },
  { id: 7, label: "Nơi công tác", key: "company_name", type: "text", required: false, unique: false, shownOnForm: true },
  { id: 8, label: "Ngày sinh", key: "date_of_birth", type: "date", required: false, unique: false, shownOnForm: false },
  { id: 9, label: "File bằng cấp", key: "qualification_file", type: "file", required: false, unique: false, shownOnForm: true },
  { id: 10, label: "Danh xưng", key: "title", type: "radio", required: false, unique: false, shownOnForm: true,
    options: [
      { label: "Dr.", value: "dr" }, { label: "Mr.", value: "mr" },
      { label: "Ms.", value: "ms" }, { label: "Mrs.", value: "mrs" },
    ] },
];

const EVENT_SECTIONS = [
  { id: "info", icon: "calendar", labelVi: "Tổng quan", labelEn: "Overview" },
  { id: "form", icon: "users", labelVi: "Trang đăng ký", labelEn: "Registration form" },
  { id: "qr", icon: "qr", labelVi: "Mã QR", labelEn: "QR codes" },
  { id: "checkin", icon: "ticket", labelVi: "Check-in", labelEn: "Check-in" },
  { id: "images", icon: "image", labelVi: "Hình ảnh", labelEn: "Images" },
  { id: "guests", icon: "users", labelVi: "Khách của sự kiện", labelEn: "Guest list" },
  { id: "invite", icon: "mail", labelVi: "Thiệp & email", labelEn: "Invites & email" },
  { id: "print", icon: "print", labelVi: "Mẫu in thẻ", labelEn: "Badge templates" },
];

const GLOBAL_NAV = [
  { id: "events", icon: "calendar", labelVi: "Sự kiện", labelEn: "Events" },
  { id: "crm", icon: "users", labelVi: "Khách hàng", labelEn: "Customers (CRM)" },
  { id: "templates", icon: "mail", labelVi: "Mẫu thư & QR", labelEn: "Templates & QR" },
  { id: "reports", icon: "chart", labelVi: "Báo cáo", labelEn: "Reports" },
  { id: "settings", icon: "settings", labelVi: "Cài đặt công ty", labelEn: "Company settings" },
];

const DEFAULT_TWEAKS = /*EDITMODE-BEGIN*/{
  "density": "comfortable",
  "primaryColor": "#2563eb",
  "showHints": true
}/*EDITMODE-END*/;

function App() {
  const t = useT();
  const [tw, setTweak] = useTweaks(DEFAULT_TWEAKS);
  const [company, setCompany] = uS(COMPANIES[0]);
  const [railCollapsed, setRailCollapsed] = uS(() => typeof window !== "undefined" && window.innerWidth < 768);
  const [mobileOpen, setMobileOpen] = uS(false);
  const [userMenu, setUserMenu] = uS(false);

  // view: { kind: 'events' | 'crm' | 'templates' | 'reports' | 'settings' | 'event', eventId?, section? }
  const [view, setView] = uS({ kind: "events" });
  const [switching, setSwitching] = uS(false);
  const [cmdkOpen, setCmdkOpen] = uS(false);

  // Per-event state (would be backend-loaded in real app)
  const [eventState, setEventState] = uS({});

  const companyEvents = uM(() => EVENTS.filter((e) => e.companyId === company.id || COMPANIES[0].id === "forest"), [company]);
  const currentEvent = uM(() => companyEvents.find((e) => e.id === view.eventId), [companyEvents, view.eventId]);

  const ensureEvent = (id) => {
    if (eventState[id]) return eventState[id];
    const next = {
      fields: DEFAULT_FIELDS.map((f) => ({ ...f })),
      formSettings: { formOpen: true, captcha: true, cccd: false, autoCheckin: false, nfcBadge: false, confirmEmail: true },
      qrSettings: { fgColor: "#000000", bgColor: "#ffffff", format: ".png", errorCorrection: "M", logoEmbed: false, logoSize: "30%", qrPerType: false },
      checkinSettings: {
        desktopShowCount: true, desktopPrintLabel: false, desktopCamera: true, desktopManual: true,
        desktopDupQR: true, desktopDupDay: false, desktopDupUser: false, desktopSound: true,
        mobileShowCount: true, mobilePrintLabel: false, mobileCamera: true, mobileManual: true,
        mobileDupQR: true, mobileDupDay: false, mobileDupUser: false, mobileSound: false,
      },
    };
    setEventState((s) => ({ ...s, [id]: next }));
    return next;
  };

  const updateEventState = (id, patch) => {
    setEventState((s) => ({ ...s, [id]: { ...ensureEvent(id), ...patch } }));
  };

  // ---- Smooth event switching: fade -> swap -> fade back ----
  const switchToEvent = uC((event, section) => {
    if (view.kind === "event" && view.eventId !== event.id) {
      // Cross-event switch: animate
      setSwitching(true);
      setTimeout(() => {
        setView({ kind: "event", eventId: event.id, section: section || view.section || "info" });
        ensureEvent(event.id);
        requestAnimationFrame(() => setSwitching(false));
      }, 160);
    } else {
      setView({ kind: "event", eventId: event.id, section: section || view.section || "info" });
      ensureEvent(event.id);
    }
  }, [view]);

  const goToList = () => setView({ kind: "events" });
  const goTo = (kind) => setView({ kind });

  // ---- Cmd+K ----
  uE(() => {
    const onKey = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") { e.preventDefault(); setCmdkOpen(true); }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  // Apply primary color
  uE(() => { document.documentElement.style.setProperty("--primary", tw.primaryColor); }, [tw.primaryColor]);

  // Close mobile drawer on view change
  uE(() => { setMobileOpen(false); }, [view]);

  // -------- Render --------

  // Sidebar varies by context
  const isEventView = view.kind === "event";
  const railContent = (
    <>
      <div className="rail__top">
        <CompanySwitcher company={company} onSwitch={setCompany} />
        <div className="rail__search" onClick={() => setCmdkOpen(true)}>
          <Icon name="sparkles" size={13} />
          <span className="rail__item-label">{t("Tìm nhanh...", "Quick search...")}</span>
          <kbd>⌘K</kbd>
        </div>
      </div>

      <button
        className="rail__collapse"
        onClick={() => setRailCollapsed(!railCollapsed)}
        title={railCollapsed ? t("Mở rộng menu", "Expand menu") : t("Thu gọn menu", "Collapse menu")}
      >
        <Icon name={railCollapsed ? "chevron_right" : "chevron_left"} size={13} />
      </button>

      <nav className="rail__nav rail__nav--fade" key={isEventView ? "e-" + view.eventId : "g"}>
        {isEventView && currentEvent ? (
          <>
            <button className="rail__back" onClick={goToList} title={t("Trở về danh sách sự kiện", "Back to events list")}>
              <Icon name="chevron_left" size={14} />
              <span className="rail__item-label">{t("Trở về danh sách sự kiện", "Back to events list")}</span>
            </button>

            <div className="rail__evt-card" title={currentEvent.name}>
              <div className="rail__evt-emoji" style={{ background: currentEvent.color }}>
                {currentEvent.emoji}
              </div>
              <div className="rail__evt-info rail__item-label">
                <div className="rail__evt-name">{currentEvent.name}</div>
                <div className="rail__evt-meta">
                  <span className={"rail__evt-status rail__evt-status--" + currentEvent.status}>
                    {t(STATUS_COLORS[currentEvent.status].label, STATUS_COLORS[currentEvent.status].labelEn || STATUS_COLORS[currentEvent.status].label)}
                  </span>
                </div>
              </div>
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Cấu hình sự kiện", "Event configuration")}</div>
              {EVENT_SECTIONS.map((s) => (
                <div
                  key={s.id}
                  className={"rail__item" + (view.section === s.id ? " rail__item--active" : "")}
                  onClick={() => setView({ ...view, section: s.id })}
                  title={t(s.labelVi, s.labelEn)}
                >
                  <span className="rail__icon"><Icon name={s.icon} /></span>
                  <span className="rail__item-label">{t(s.labelVi, s.labelEn)}</span>
                </div>
              ))}
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Chuyển nhanh sự kiện", "Switch event")}</div>
              {companyEvents.filter((e) => e.id !== currentEvent.id).slice(0, 5).map((e) => (
                <div
                  key={e.id}
                  className="rail__pin"
                  onClick={() => switchToEvent(e)}
                  title={e.name}
                >
                  <span className="rail__pin-dot" style={{ background: e.color }} />
                  <span className="rail__pin-name">{e.name}</span>
                </div>
              ))}
            </div>
          </>
        ) : (
          <>
            <div className="rail__group">
              <div className="rail__group-label">{t("Tổng quan", "Overview")}</div>
              {GLOBAL_NAV.map((n) => (
                <div
                  key={n.id}
                  className={"rail__item" + ((!isEventView && view.kind === n.id) ? " rail__item--active" : "")}
                  onClick={() => goTo(n.id)}
                  title={t(n.labelVi, n.labelEn)}
                >
                  <span className="rail__icon"><Icon name={n.icon} /></span>
                  <span className="rail__item-label">{t(n.labelVi, n.labelEn)}</span>
                  {n.id === "events" && <span className="rail__item-count">{companyEvents.length}</span>}
                </div>
              ))}
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Sự kiện gần đây", "Recent events")}</div>
              {companyEvents.slice(0, 5).map((e) => (
                <div
                  key={e.id}
                  className="rail__pin"
                  onClick={() => switchToEvent(e)}
                  title={e.name}
                >
                  <span className="rail__pin-dot" style={{ background: e.color }} />
                  <span className="rail__pin-name">{e.name}</span>
                </div>
              ))}
            </div>
          </>
        )}
      </nav>

      <div className="rail__bottom">
        <div className="rail__user">SA</div>
        <div className="rail__user-info">
          <b>System Admin</b>
          <span>admin@delfi.vn</span>
        </div>
        <button className="topbar__icon-btn" style={{ width: 28, height: 28, color: "#7a8398" }}>
          <Icon name="settings" size={15} />
        </button>
      </div>
    </>
  );

  // Topbar varies based on view
  const renderTopbar = () => {
    const mobileMenu = (
      <button className="rail__menubtn" onClick={() => setMobileOpen(true)} aria-label="Mở menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M3 6h18M3 12h18M3 18h18" /></svg>
      </button>
    );
    const userBtn = (
      <div style={{ position: "relative" }}>
        <button className="topbar__icon-btn" onClick={() => setUserMenu(!userMenu)} aria-label={t("Tài khoản", "Account")} style={{ borderRadius: 999 }}>
          <span style={{ width: 28, height: 28, borderRadius: "50%", background: "linear-gradient(135deg,#3b82f6,#1d4ed8)", color: "#fff", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600 }}>SA</span>
        </button>
        {userMenu && (
          <>
            <div className="popover-backdrop" onClick={() => setUserMenu(false)} />
            <div className="popover popover--anchored" style={{ width: 240, right: 0, left: "auto" }}>
              <div style={{ padding: "12px 14px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>System Admin</div>
                <div className="small">admin@delfi.vn</div>
              </div>
              <div className="popover__list" style={{ padding: 4 }}>
                <div className="popover__item" onClick={() => { setUserMenu(false); goTo("settings"); }}>
                  <Icon name="user" size={16} /><span>{t("Tài khoản của tôi", "My account")}</span>
                </div>
                <div className="popover__item" onClick={() => { setUserMenu(false); goTo("settings"); }}>
                  <Icon name="settings" size={16} /><span>{t("Cài đặt công ty", "Company settings")}</span>
                </div>
                <div className="popover__item" onClick={() => { setUserMenu(false); alert(t("Mời thành viên", "Invite team member")); }}>
                  <Icon name="users" size={16} /><span>{t("Mời thành viên", "Invite team member")}</span>
                </div>
              </div>
              <div style={{ borderTop: "1px solid var(--border)", padding: 4 }}>
                <a className="popover__item" href="auth.html" style={{ color: "var(--danger)" }}>
                  <Icon name="chevron_left" size={16} /><span>{t("Đăng xuất", "Sign out")}</span>
                </a>
              </div>
            </div>
          </>
        )}
      </div>
    );

    if (isEventView && currentEvent) {
      return (
        <header className="topbar">
          {mobileMenu}
          <EventSwitcher
            event={currentEvent}
            events={companyEvents}
            onSwitch={(e) => switchToEvent(e)}
            onGoToList={goToList}
          />
          <div className="topbar__spacer" />
          <button className="qa__btn" title={t("Xem trang đăng ký", "View registration page")}><Icon name="eye" /><span className="qa__btn-label">{t("Xem trang đăng ký", "View registration")}</span></button>
          <button className="qa__btn" title={`${currentEvent.guestCount.toLocaleString()} ${t("khách", "guests")}`}><Icon name="users" /><span className="qa__btn-label">{currentEvent.guestCount.toLocaleString()} {t("khách", "guests")}</span></button>
          <button className="qa__btn qa__btn--primary" title={t("Lưu thay đổi", "Save changes")}><Icon name="save" /><span className="qa__btn-label">{t("Lưu thay đổi", "Save changes")}</span></button>
          <LangSwitcher compact />
          <button className="topbar__icon-btn topbar__icon-btn--dot" title={t("Thông báo", "Notifications")}><Icon name="bell" size={17} /></button>
          {userBtn}
        </header>
      );
    }
    // Global view topbar
    return (
      <header className="topbar">
        {mobileMenu}
        <div className="topbar__spacer" />
        <button className="qa__btn" onClick={() => setCmdkOpen(true)}>
          <Icon name="sparkles" /><span className="qa__btn-label">{t("Tìm nhanh", "Quick search")}</span>
          <kbd style={{ marginLeft: 4, background: "var(--surface-2)", padding: "1px 6px", borderRadius: 4, fontSize: 11, color: "var(--text-muted)" }}>⌘K</kbd>
        </button>
        <LangSwitcher compact />
        <button className="topbar__icon-btn topbar__icon-btn--dot" title={t("Thông báo", "Notifications")}><Icon name="bell" size={17} /></button>
        {userBtn}
      </header>
    );
  };

  // Event subnav removed — sections moved into sidebar
  const eventSubnav = null;

  // Body
  const renderBody = () => {
    if (!isEventView) {
      switch (view.kind) {
        case "events":
          return <EventsListPage events={companyEvents} company={company} onOpen={switchToEvent} onNew={() => alert(t("Tạo sự kiện mới", "Create new event"))} />;
        case "crm": return <PlaceholderPage title={t("Khách hàng", "Customers")} sub={t("Cơ sở dữ liệu khách hàng dùng chung trong công ty", "Shared customer database across the company")} icon="users" />;
        case "templates": return <PlaceholderPage title={t("Mẫu thư & QR", "Templates & QR")} sub={t("Thư viện mẫu dùng cho nhiều sự kiện", "Template library reusable across events")} icon="mail" />;
        case "reports": return <PlaceholderPage title={t("Báo cáo", "Reports")} sub={t("Phân tích & báo cáo tổng hợp toàn công ty", "Company-wide analytics and reports")} icon="chart" />;
        case "settings": return <PlaceholderPage title={t("Cài đặt công ty", "Company settings")} sub={t("Tài khoản, nhân viên, thanh toán", "Account, members, billing")} icon="settings" />;
        default: return null;
      }
    }

    if (!currentEvent) return null;
    const es = ensureEvent(currentEvent.id);
    const upd = (patch) => updateEventState(currentEvent.id, patch);
    const section = view.section || "info";

    return (
      <>
        <div className="section-title">
          <Icon name={EVENT_SECTIONS.find((s) => s.id === section)?.icon} size={20} />
          <h2>{(() => { const sx = EVENT_SECTIONS.find((s) => s.id === section); return sx ? t(sx.labelVi, sx.labelEn) : ""; })()}</h2>
          <span className="small">
            {t("Thay đổi được lưu khi bạn bấm", "Changes are saved when you click")} <b style={{ color: "var(--text)" }}>{t("Lưu thay đổi", "Save changes")}</b> {t("ở đầu trang", "at the top")}
          </span>
        </div>
        <div className="content">
          {section === "info" && <EventOverviewPanel event={currentEvent} />}
          {section === "form" && <SectionForm fields={es.fields} setFields={(f) => upd({ fields: f })} settings={es.formSettings} setSettings={(s) => upd({ formSettings: s })} />}
          {section === "qr" && (() => { const C = window.SectionQR; return <C s={es.qrSettings} set={(s) => upd({ qrSettings: s })} />; })()}
          {section === "checkin" && <SectionCheckin s={es.checkinSettings} set={(s) => upd({ checkinSettings: s })} />}
          {section === "images" && <SectionImages s={{}} set={() => {}} />}
          {section === "guests" && (() => { const C = window.SectionGuests; return <C eventId={currentEvent.id} />; })()}
          {section === "invite" && <SectionInvite />}
          {section === "print" && <SectionPrint />}
        </div>
      </>
    );
  };

  return (
    <div className={"app" + (tw.density === "compact" ? " app--compact" : "") + (railCollapsed ? " app--rail-collapsed" : "") + (mobileOpen ? " app--mobile-open" : "")}>
      <div className="rail__scrim" onClick={() => setMobileOpen(false)} />
      <aside className="rail">{railContent}</aside>

      <div className={"main event-shell" + (switching ? " event-shell--switching" : "")}>
        {renderTopbar()}
        {eventSubnav}
        <div className="detail-fade" key={isEventView ? view.eventId + "/" + view.section : view.kind}>
          {renderBody()}
        </div>
      </div>

      <CommandPalette
        open={cmdkOpen}
        onClose={() => setCmdkOpen(false)}
        events={companyEvents}
        onJump={switchToEvent}
        onGoTo={goTo}
      />

      <TweaksPanel>
        <TweakSection label={t("Hiển thị", "Display")}>
          <TweakRadio
            label={t("Mật độ", "Density")}
            value={tw.density}
            onChange={(v) => setTweak("density", v)}
            options={[{ value: "comfortable", label: t("Thoáng", "Comfortable") }, { value: "compact", label: t("Gọn", "Compact") }]}
          />
          <TweakColor
            label={t("Màu chủ đạo", "Primary color")}
            value={tw.primaryColor}
            onChange={(v) => setTweak("primaryColor", v)}
            options={["#2563eb", "#0a1430", "#16a34a", "#7c3aed", "#dc2626"]}
          />
          <TweakToggle label={t("Hiển thị câu gợi ý", "Show hint tooltips")} value={tw.showHints} onChange={(v) => setTweak("showHints", v)} />
        </TweakSection>
      </TweaksPanel>
    </div>
  );
}

// ---- Event overview panel (when in event detail / Tổng quan tab) ----
const EventOverviewPanel = ({ event }) => {
  const t = useT();
  const fillPct = event.capacity ? Math.round((event.registered / event.capacity) * 100) : 0;
  return (
    <>
      <div
        className="card"
        style={{
          backgroundImage: event.cover,
          color: "#fff", border: "none",
          padding: "26px 28px", marginBottom: 18,
        }}
      >
        <div style={{ display: "flex", alignItems: "flex-start", gap: 18 }}>
          <div style={{ width: 56, height: 56, borderRadius: 14, background: "rgba(255,255,255,0.92)", display: "grid", placeItems: "center", fontSize: 28 }}>{event.emoji}</div>
          <div style={{ flex: 1 }}>
            <div style={{ opacity: 0.85, fontSize: 13, marginBottom: 4 }}>{event.subtitle}</div>
            <h2 style={{ margin: 0, fontSize: 26, letterSpacing: "-0.01em" }}>{event.name}</h2>
            <div style={{ display: "flex", gap: 18, marginTop: 12, fontSize: 13.5, opacity: 0.92, flexWrap: "wrap" }}>
              <span><Icon name="calendar" size={14} /> {formatRange(event.startDate, event.endDate)}</span>
              <span><Icon name="flag" size={14} /> {event.venue} • {event.city}</span>
            </div>
          </div>
        </div>
      </div>

      <div className="kpi-row">
        <KPIInline label={t("Khách đã tạo", "Guests created")} value={event.guestCount.toLocaleString()} icon="users" tip={t("Tổng số khách bạn đã nhập hoặc đã đăng ký vào sự kiện này", "Total guests created or registered for this event")} />
        <KPIInline label={t("Đã đăng ký", "Registered")} value={event.registered.toLocaleString()} icon="ticket" tip={t("Khách đã hoàn tất form đăng ký", "Guests who completed registration")} />
        <KPIInline label={t("Đã check-in", "Checked in")} value={event.checkedIn.toLocaleString()} icon="check" tip={t("Khách đã có mặt và được xác nhận tại sự kiện", "Guests confirmed present at the venue")} />
        <KPIInline label={t("Sức chứa", "Capacity")} value={`${fillPct}%`} icon="chart" tip={`${t("Đã đăng ký", "Registered")} ${event.registered}/${event.capacity}`} />
      </div>

      <Card title={t("Cài đặt nhanh", "Quick settings")} sub={t("Bật/tắt nhanh các tính năng phổ biến — chi tiết ở từng tab", "Toggle common features quickly — full details in each tab")} icon="settings">
        <OptRow title={t("Mở trang đăng ký công khai", "Open public registration")} desc={t("Cho khách tự đăng ký qua link", "Let guests self-register via link")} on={true} onChange={() => {}}
          tip={t("Tab 'Trang đăng ký' để cấu hình thông tin thu thập", "Use the 'Registration form' tab to configure fields")} />
        <OptRow title={t("Gửi email xác nhận tự động", "Auto-send confirmation email")} desc={t("Khách nhận email kèm mã QR sau khi đăng ký", "Guests get an email with QR after registering")} on={true} onChange={() => {}}
          tip={t("Sửa mẫu thư ở tab 'Thiệp & email'", "Edit templates in the 'Invites & email' tab")} />
        <OptRow title={t("Đang nhận check-in", "Check-in is open")} desc={t("Quầy lễ tân có thể quét mã QR khách", "Reception can scan guest QRs")} on={true} onChange={() => {}}
          tip={t("Tắt khi sự kiện đã kết thúc để dừng nhận check-in", "Turn off when the event ends to stop accepting check-ins")} />
      </Card>

      <Card title={t("Hoạt động gần đây", "Recent activity")} sub={t("Nhật ký thay đổi cấu hình", "Configuration change log")} icon="history">
        <div style={{ display: "flex", flexDirection: "column", gap: 0 }}>
          {[
            { t: t("5 phút trước", "5 min ago"), who: "System Admin", what: t("đã sửa mẫu email mời", "edited the invitation email template") },
            { t: t("1 giờ trước", "1 hour ago"), who: "Nguyễn Lan", what: t("đã bật quét CCCD trên trang đăng ký", "enabled ID-card scan on the registration page") },
            { t: t("Hôm qua", "Yesterday"), who: "System Admin", what: t("đã thêm trường thông tin 'Nơi công tác'", "added the 'Workplace' field") },
            { t: t("3 ngày trước", "3 days ago"), who: "Phạm Hùng", what: t("đã nạp 120 khách từ Excel", "imported 120 guests from Excel") },
          ].map((a, i) => (
            <div key={i} style={{ padding: "10px 0", borderBottom: i < 3 ? "1px solid var(--border)" : "none", display: "flex", gap: 12, fontSize: 13 }}>
              <div style={{ width: 28, height: 28, borderRadius: 999, background: "var(--surface-2)", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600, color: "var(--text-muted)" }}>
                {a.who.split(" ").map((w) => w[0]).join("").slice(0, 2)}
              </div>
              <div style={{ flex: 1 }}>
                <b style={{ fontWeight: 500 }}>{a.who}</b> {a.what}
              </div>
              <span className="small">{a.t}</span>
            </div>
          ))}
        </div>
      </Card>
    </>
  );
};

const KPIInline = ({ label, value, icon, tip }) => (
  <div className="kpi">
    <div className="kpi__head">
      <div className="kpi__label">{label}{tip && <Tip text={tip} />}</div>
      {icon && <div className="kpi__icon" style={{ background: "var(--primary-soft)", color: "var(--primary)" }}><Icon name={icon} size={16} /></div>}
    </div>
    <div className="kpi__value">{value}</div>
  </div>
);

const PlaceholderPage = ({ title, sub, icon }) => {
  const t = useT();
  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{title}</h1>
          <p className="page__sub">{sub}</p>
        </div>
      </div>
      <div className="card" style={{ padding: 60, textAlign: "center" }}>
        <Icon name={icon} size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px" }} />
        <h3 style={{ margin: "0 0 6px" }}>{t("Module này đang được thiết kế", "This module is being designed")}</h3>
        <p className="small" style={{ margin: 0 }}>{t("Đây là khu vực toàn công ty — dữ liệu dùng chung cho mọi sự kiện.", "This is a company-wide area — data shared across all events.")}</p>
      </div>
    </div>
  );
};

ReactDOM.createRoot(document.getElementById("root")).render(
  <LangProvider>
    <App />
  </LangProvider>
);
