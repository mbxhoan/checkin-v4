"use client";
import { useState, useMemo } from "react";
import { useRouter } from "next/navigation";
import { useT } from "@/lib/context";
import { EVENTS } from "@/data/events";
import { STATUS_COLORS, formatRange, type Event } from "@/lib/types";
import { Topbar } from "@/components/layout/topbar";
import { Icon } from "@/components/ui/icon";
import { StatusBadge } from "@/components/ui/status-badge";

const FILTERS: { id: string; labelVi: string; labelEn: string }[] = [
  { id: "all", labelVi: "Tất cả", labelEn: "All" },
  { id: "active", labelVi: "Đang diễn ra", labelEn: "Active" },
  { id: "upcoming", labelVi: "Sắp diễn ra", labelEn: "Upcoming" },
  { id: "draft", labelVi: "Bản nháp", labelEn: "Draft" },
  { id: "ended", labelVi: "Đã kết thúc", labelEn: "Ended" },
];

function KPICard({ label, value, sub, color }: { label: string; value: string | number; sub?: string; color?: string }) {
  return (
    <div className="kpi">
      <div className="kpi__label">{label}</div>
      <div className="kpi__value" style={color ? { color } : undefined}>{value}</div>
      {sub && <div className="kpi__trend">{sub}</div>}
    </div>
  );
}

function EventCard({ event, onClick }: { event: Event; onClick: () => void }) {
  const t = useT();
  const fillPct = event.capacity ? Math.round((event.registered / event.capacity) * 100) : 0;
  const checkinPct = event.registered ? Math.round((event.checkedIn / event.registered) * 100) : 0;

  return (
    <div className="evt-card" onClick={onClick}>
      <div className="evt-card__cover" style={{ backgroundImage: event.cover }}>
        <div className="evt-card__emoji">{event.emoji}</div>
        <div className="evt-card__cover-status">
          <StatusBadge status={event.status} size="sm" />
        </div>
      </div>
      <div className="evt-card__body">
        <div>
          <h3 className="evt-card__name">{event.name}</h3>
          <p className="evt-card__sub">{event.subtitle}</p>
        </div>

        <div className="evt-card__progress">
          <div className="evt-card__bar">
            <div style={{ width: `${fillPct}%`, background: event.color }} />
          </div>
          <span className="evt-card__progress-label">
            <b>{event.registered.toLocaleString()}</b>/{event.capacity.toLocaleString()} {t("đăng ký", "registered")}
          </span>
        </div>

        {event.checkedIn > 0 && (
          <div className="evt-card__progress">
            <div className="evt-card__bar">
              <div style={{ width: `${checkinPct}%`, background: "var(--success)" }} />
            </div>
            <span className="evt-card__progress-label">
              <b>{event.checkedIn.toLocaleString()}</b> {t("đã check-in", "checked in")}
            </span>
          </div>
        )}

        <div className="evt-card__meta">
          <span className="evt-card__meta-item">
            <Icon name="calendar" size={12} />{formatRange(event.startDate, event.endDate)}
          </span>
          <span className="evt-card__meta-item">
            <Icon name="flag" size={12} />{event.city}
          </span>
        </div>
      </div>
    </div>
  );
}

export default function EventsPage() {
  const t = useT();
  const router = useRouter();
  const [filter, setFilter] = useState("all");
  const [search, setSearch] = useState("");

  const filtered = useMemo(() => {
    const ql = search.toLowerCase();
    return EVENTS.filter((e) => {
      const matchStatus = filter === "all" || e.status === filter;
      const matchSearch = !ql || e.name.toLowerCase().includes(ql) || e.subtitle.toLowerCase().includes(ql) || e.city.toLowerCase().includes(ql);
      return matchStatus && matchSearch;
    });
  }, [filter, search]);

  const counts = useMemo(() => ({
    active: EVENTS.filter((e) => e.status === "active").length,
    upcoming: EVENTS.filter((e) => e.status === "upcoming").length,
    draft: EVENTS.filter((e) => e.status === "draft").length,
    ended: EVENTS.filter((e) => e.status === "ended").length,
    totalGuests: EVENTS.reduce((a, e) => a + e.guestCount, 0),
    totalCheckedIn: EVENTS.reduce((a, e) => a + e.checkedIn, 0),
  }), []);

  return (
    <>
      <Topbar />
      <div className="page">
        <div className="page__head">
          <div>
            <h1 className="page__title">{t("Sự kiện", "Events")}</h1>
            <p className="page__sub">{t("Quản lý tất cả sự kiện của công ty", "Manage all company events")}</p>
          </div>
          <div className="page__head-actions">
            <button className="qa__btn qa__btn--primary" onClick={() => alert(t("Tạo sự kiện mới", "Create new event"))}>
              <Icon name="plus" size={14} />{t("Tạo sự kiện mới", "Create new event")}
            </button>
          </div>
        </div>

        <div className="kpi-row">
          <KPICard label={t("Đang diễn ra", "Active")} value={counts.active} sub={t("sự kiện", "events")} color="var(--success)" />
          <KPICard label={t("Sắp diễn ra", "Upcoming")} value={counts.upcoming} sub={t("sự kiện", "events")} color="#0891b2" />
          <KPICard label={t("Tổng khách", "Total guests")} value={counts.totalGuests.toLocaleString()} sub={t("trên tất cả sự kiện", "across all events")} />
          <KPICard label={t("Đã check-in", "Checked in")} value={counts.totalCheckedIn.toLocaleString()} sub={t("khách đã có mặt", "guests present")} color="var(--primary)" />
        </div>

        <div className="filters">
          {FILTERS.map((f) => {
            const count = f.id === "all" ? EVENTS.length : EVENTS.filter((e) => e.status === f.id).length;
            return (
              <button
                key={f.id}
                className={`filter-pill${filter === f.id ? " filter-pill--active" : ""}`}
                onClick={() => setFilter(f.id)}
              >
                {t(f.labelVi, f.labelEn)}
                <span className="count">{count}</span>
              </button>
            );
          })}
          <div style={{ marginLeft: "auto" }}>
            <div className="search-input">
              <Icon name="search" size={15} />
              <input
                placeholder={t("Tìm sự kiện...", "Search events...")}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
          </div>
        </div>

        <div className="evt-grid">
          {filtered.map((e) => (
            <EventCard key={e.id} event={e} onClick={() => router.push(`/events/${e.id}/overview`)} />
          ))}
        </div>

        {filtered.length === 0 && (
          <div className="empty" style={{ marginTop: 60 }}>
            <div className="empty__title">{t("Không tìm thấy sự kiện nào", "No events found")}</div>
            <p className="small">{t("Thử tìm từ khóa khác hoặc xóa bộ lọc", "Try a different search or clear filters")}</p>
          </div>
        )}
      </div>
    </>
  );
}
