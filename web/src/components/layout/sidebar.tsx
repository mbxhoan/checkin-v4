"use client";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useApp, useT } from "@/lib/context";
import { EVENTS } from "@/data/events";
import { STATUS_COLORS, type Event } from "@/lib/types";
import { Icon } from "@/components/ui/icon";
import { CompanySwitcher } from "./company-switcher";

const GLOBAL_NAV = [
  { id: "events", href: "/events", icon: "calendar" as const, labelVi: "Sự kiện", labelEn: "Events" },
  { id: "crm", href: "/crm", icon: "users" as const, labelVi: "Khách hàng", labelEn: "Customers" },
  { id: "templates", href: "/templates", icon: "mail" as const, labelVi: "Mẫu thư & QR", labelEn: "Templates & QR" },
  { id: "reports", href: "/reports", icon: "chart" as const, labelVi: "Báo cáo", labelEn: "Reports" },
  { id: "settings", href: "/settings", icon: "settings" as const, labelVi: "Cài đặt công ty", labelEn: "Company settings" },
];

const EVENT_SECTIONS = [
  { id: "overview", icon: "calendar" as const, labelVi: "Tổng quan", labelEn: "Overview" },
  { id: "form", icon: "users" as const, labelVi: "Trang đăng ký", labelEn: "Registration form" },
  { id: "qr", icon: "qr" as const, labelVi: "Mã QR", labelEn: "QR codes" },
  { id: "checkin", icon: "ticket" as const, labelVi: "Check-in", labelEn: "Check-in" },
  { id: "images", icon: "image" as const, labelVi: "Hình ảnh", labelEn: "Images" },
  { id: "guests", icon: "users" as const, labelVi: "Khách của sự kiện", labelEn: "Guest list" },
  { id: "invite", icon: "mail" as const, labelVi: "Thiệp & email", labelEn: "Invites & email" },
  { id: "print", icon: "print" as const, labelVi: "Mẫu in thẻ", labelEn: "Badge templates" },
];

interface SidebarProps {
  currentEvent?: Event;
}

export function Sidebar({ currentEvent }: SidebarProps) {
  const t = useT();
  const pathname = usePathname();
  const router = useRouter();
  const { railCollapsed, setRailCollapsed, setCmdkOpen } = useApp();
  const isEventView = !!currentEvent;

  const currentSection = isEventView ? pathname.split("/").pop() : null;

  return (
    <aside className="rail">
      <div className="rail__top">
        <CompanySwitcher />
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

      <nav className="rail__nav rail__nav--fade">
        {isEventView && currentEvent ? (
          <>
            <button className="rail__back" onClick={() => router.push("/events")}>
              <Icon name="chevron_left" size={14} />
              <span className="rail__item-label">{t("Trở về danh sách sự kiện", "Back to events list")}</span>
            </button>

            <div className="rail__evt-card">
              <div className="rail__evt-emoji" style={{ background: currentEvent.color }}>{currentEvent.emoji}</div>
              <div className="rail__evt-info rail__item-label">
                <div className="rail__evt-name">{currentEvent.name}</div>
                <div className="rail__evt-meta">
                  <span className={`rail__evt-status rail__evt-status--${currentEvent.status}`}>
                    {t(STATUS_COLORS[currentEvent.status].label, STATUS_COLORS[currentEvent.status].labelEn)}
                  </span>
                </div>
              </div>
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Cấu hình sự kiện", "Event configuration")}</div>
              {EVENT_SECTIONS.map((s) => (
                <Link
                  key={s.id}
                  href={`/events/${currentEvent.id}/${s.id}`}
                  className={`rail__item${currentSection === s.id ? " rail__item--active" : ""}`}
                  title={t(s.labelVi, s.labelEn)}
                >
                  <span className="rail__icon"><Icon name={s.icon} /></span>
                  <span className="rail__item-label">{t(s.labelVi, s.labelEn)}</span>
                </Link>
              ))}
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Chuyển nhanh sự kiện", "Switch event")}</div>
              {EVENTS.filter((e) => e.id !== currentEvent.id).slice(0, 5).map((e) => (
                <div key={e.id} className="rail__pin" onClick={() => router.push(`/events/${e.id}`)} title={e.name}>
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
                <Link
                  key={n.id}
                  href={n.href}
                  className={`rail__item${pathname.startsWith(n.href) ? " rail__item--active" : ""}`}
                  title={t(n.labelVi, n.labelEn)}
                >
                  <span className="rail__icon"><Icon name={n.icon} /></span>
                  <span className="rail__item-label">{t(n.labelVi, n.labelEn)}</span>
                  {n.id === "events" && <span className="rail__item-count">{EVENTS.length}</span>}
                </Link>
              ))}
            </div>

            <div className="rail__group">
              <div className="rail__group-label">{t("Sự kiện gần đây", "Recent events")}</div>
              {EVENTS.slice(0, 5).map((e) => (
                <div key={e.id} className="rail__pin" onClick={() => router.push(`/events/${e.id}`)} title={e.name}>
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
        <div className="rail__user-info rail__item-label">
          <b>System Admin</b>
          <span>admin@delfi.vn</span>
        </div>
        <button className="topbar__icon-btn" style={{ width: 28, height: 28, color: "#7a8398", flexShrink: 0 }}>
          <Icon name="settings" size={15} />
        </button>
      </div>
    </aside>
  );
}
