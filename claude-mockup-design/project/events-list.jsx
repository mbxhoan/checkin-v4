// events-list.jsx — overview page listing all events in a company

const { useState: useSt, useMemo: useMm } = React;

const STATUS_FILTER_DEFS = [
  { id: "all", labelVi: "Tất cả", labelEn: "All" },
  { id: "active", labelVi: "Đang diễn ra", labelEn: "Active" },
  { id: "upcoming", labelVi: "Sắp diễn ra", labelEn: "Upcoming" },
  { id: "draft", labelVi: "Bản nháp", labelEn: "Draft" },
  { id: "ended", labelVi: "Đã kết thúc", labelEn: "Ended" },
];

const KPI = ({ label, value, tip, trend, icon, iconBg, iconColor }) => (
  <div className="kpi">
    <div className="kpi__head">
      <div className="kpi__label">
        {label}
        {tip && <Tip text={tip} />}
      </div>
      {icon && (
        <div className="kpi__icon" style={{ background: iconBg, color: iconColor }}>
          <Icon name={icon} size={16} />
        </div>
      )}
    </div>
    <div className="kpi__value">{value}</div>
    {trend && <div className={"kpi__trend" + (trend.startsWith("-") ? " kpi__trend--down" : "")}>{trend}</div>}
  </div>
);

const EventCard = ({ event, onOpen }) => {
  const t = useT();
  const status = STATUS_COLORS[event.status];
  const fillPct = event.capacity ? Math.round((event.registered / event.capacity) * 100) : 0;
  return (
    <div className="evt-card" onClick={() => onOpen(event)}>
      <div className="evt-card__cover" style={{ background: event.cover }}>
        <div className="evt-card__emoji">{event.emoji}</div>
        <div className="evt-card__cover-status">
          <span className={"status-pill status-pill--" + event.status}>{t(status.label, status.labelEn)}</span>
        </div>
      </div>
      <div className="evt-card__body">
        <h3 className="evt-card__name">{event.name}</h3>
        <p className="evt-card__sub">{event.subtitle}</p>
        <div className="evt-card__progress">
          <div className="evt-card__bar"><div style={{ width: fillPct + "%", background: event.color }} /></div>
          <span className="evt-card__progress-label">
            <b>{event.registered.toLocaleString()}</b>/{event.capacity.toLocaleString()}
          </span>
        </div>
        <div className="evt-card__meta">
          <span className="evt-card__meta-item">
            <Icon name="calendar" size={13} /> {formatRange(event.startDate, event.endDate)}
          </span>
          <span className="evt-card__meta-item">
            <Icon name="flag" size={13} /> {event.city}
          </span>
        </div>
      </div>
    </div>
  );
};

const EventsListPage = ({ events, company, onOpen, onNew }) => {
  const t = useT();
  const [statusFilter, setStatusFilter] = useSt("all");
  const [q, setQ] = useSt("");

  const counts = useMm(() => {
    const c = { all: events.length };
    for (const s of ["active", "upcoming", "draft", "ended"]) c[s] = events.filter((e) => e.status === s).length;
    return c;
  }, [events]);

  const filtered = useMm(() => {
    const ql = q.toLowerCase();
    return events.filter((e) => {
      if (statusFilter !== "all" && e.status !== statusFilter) return false;
      if (ql && !e.name.toLowerCase().includes(ql) && !e.subtitle.toLowerCase().includes(ql) && !e.city.toLowerCase().includes(ql)) return false;
      return true;
    });
  }, [events, statusFilter, q]);

  // Top-line stats across all events
  const totalGuests = events.reduce((a, e) => a + e.guestCount, 0);
  const totalRegistered = events.reduce((a, e) => a + e.registered, 0);
  const totalCheckedIn = events.reduce((a, e) => a + e.checkedIn, 0);
  const activeCount = counts.active + counts.upcoming;

  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{t("Sự kiện", "Events")}</h1>
          <p className="page__sub">{t("Quản lý mọi sự kiện trong", "Manage all events in")} <b style={{ color: "var(--text)" }}>{company.name}</b></p>
        </div>
        <div className="page__head-actions">
          <button className="qa__btn"><Icon name="upload" />{t("Nhập từ file", "Import from file")}</button>
          <button className="qa__btn qa__btn--primary" onClick={onNew}>
            <Icon name="plus" />{t("Tạo sự kiện mới", "Create new event")}
          </button>
        </div>
      </div>

      <div className="kpi-row">
        <KPI
          label={t("Sự kiện đang hoạt động", "Active events")}
          tip={t("Bao gồm các sự kiện 'Đang diễn ra' và 'Sắp diễn ra'", "Includes 'Active' and 'Upcoming' events")}
          value={activeCount}
          trend={t(`+${counts.upcoming} sắp diễn ra`, `+${counts.upcoming} upcoming`)}
          icon="calendar" iconBg="#dbe8ff" iconColor="#2563eb"
        />
        <KPI
          label={t("Tổng số khách", "Total guests")}
          tip={t("Tổng số khách đã tạo trên tất cả sự kiện trong công ty", "Total guests created across all company events")}
          value={totalGuests.toLocaleString()}
          icon="users" iconBg="#e8f7ee" iconColor="#16a34a"
        />
        <KPI
          label={t("Đã đăng ký", "Registered")}
          tip={t("Số khách đã hoàn tất đăng ký qua trang đăng ký công khai hoặc nhập thủ công", "Guests who completed registration via the public form or manual entry")}
          value={totalRegistered.toLocaleString()}
          trend={t(`${Math.round((totalRegistered / Math.max(totalGuests, 1)) * 100)}% tỷ lệ chuyển đổi`, `${Math.round((totalRegistered / Math.max(totalGuests, 1)) * 100)}% conversion`)}
          icon="ticket" iconBg="#fff3e7" iconColor="#b45309"
        />
        <KPI
          label={t("Đã check-in", "Checked in")}
          tip={t("Số khách đã có mặt và được check-in tại sự kiện", "Guests who arrived and were checked in")}
          value={totalCheckedIn.toLocaleString()}
          icon="check" iconBg="#fdecec" iconColor="#dc2626"
        />
      </div>

      <div className="filters">
        {STATUS_FILTER_DEFS.map((f) => (
          <button
            key={f.id}
            className={"filter-pill" + (statusFilter === f.id ? " filter-pill--active" : "")}
            onClick={() => setStatusFilter(f.id)}
          >
            {t(f.labelVi, f.labelEn)}
            <span className="count">{counts[f.id]}</span>
          </button>
        ))}
        <div style={{ flex: 1 }} />
        <div className="search-input">
          <Icon name="settings" size={14} />
          <input placeholder={t("Tìm sự kiện...", "Search events...")} value={q} onChange={(e) => setQ(e.target.value)} />
        </div>
      </div>

      {filtered.length === 0 ? (
        <div className="empty">
          <Icon name="calendar" size={44} className="empty__icon" />
          <p className="empty__title">{t("Không có sự kiện phù hợp", "No matching events")}</p>
          <p>{t("Thử bỏ bộ lọc hoặc tạo sự kiện mới.", "Try clearing filters or create a new event.")}</p>
        </div>
      ) : (
        <div className="evt-grid">
          {filtered.map((e) => <EventCard key={e.id} event={e} onOpen={onOpen} />)}
        </div>
      )}
    </div>
  );
};

Object.assign(window, { EventsListPage });
