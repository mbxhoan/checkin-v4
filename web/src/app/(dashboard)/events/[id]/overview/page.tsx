"use client";
import { use, useState } from "react";
import { EVENTS } from "@/data/events";
import { useT } from "@/lib/context";
import { formatRange } from "@/lib/types";
import { Icon } from "@/components/ui/icon";
import { Card } from "@/components/ui/card";
import { OptRow } from "@/components/ui/opt-row";
import { Tooltip } from "@/components/ui/tooltip";

function KPI({ label, value, icon, tip }: { label: string; value: string | number; icon: Parameters<typeof Icon>[0]["name"]; tip?: string }) {
  return (
    <div className="kpi">
      <div className="kpi__head">
        <div className="kpi__label">{label}{tip && <Tooltip text={tip} />}</div>
        <div className="kpi__icon" style={{ background: "var(--primary-soft)", color: "var(--primary)" }}>
          <Icon name={icon} size={16} />
        </div>
      </div>
      <div className="kpi__value">{typeof value === "number" ? value.toLocaleString() : value}</div>
    </div>
  );
}

export default function OverviewPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  const { id } = use(params);
  const event = EVENTS.find((e) => e.id === id)!;
  const fillPct = event.capacity ? Math.round((event.registered / event.capacity) * 100) : 0;

  const [formOpen, setFormOpen] = useState(true);
  const [confirmEmail, setConfirmEmail] = useState(true);
  const [checkinOpen, setCheckinOpen] = useState(event.status === "active");

  const activity = [
    { time: t("5 phút trước", "5 min ago"), who: "System Admin", what: t("đã sửa mẫu email mời", "edited the invitation email template") },
    { time: t("1 giờ trước", "1 hour ago"), who: "Nguyễn Lan", what: t("đã bật quét CCCD trên trang đăng ký", "enabled ID-card scan on the registration page") },
    { time: t("Hôm qua", "Yesterday"), who: "System Admin", what: t("đã thêm trường thông tin 'Nơi công tác'", "added the 'Workplace' field") },
    { time: t("3 ngày trước", "3 days ago"), who: "Phạm Hùng", what: t("đã nạp 120 khách từ Excel", "imported 120 guests from Excel") },
  ];

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="calendar" size={20} />
        <h2>{t("Tổng quan", "Overview")}</h2>
        <span className="small">
          {t("Thay đổi được lưu khi bạn bấm", "Changes saved when you click")} <b style={{ color: "var(--text)" }}>{t("Lưu thay đổi", "Save changes")}</b>
        </span>
      </div>

      <div className="content">
        {/* Hero card */}
        <div className="card" style={{ backgroundImage: event.cover, color: "#fff", border: "none", padding: "26px 28px", marginBottom: 18 }}>
          <div style={{ display: "flex", alignItems: "flex-start", gap: 18 }}>
            <div style={{ width: 56, height: 56, borderRadius: 14, background: "rgba(255,255,255,0.92)", display: "grid", placeItems: "center", fontSize: 28 }}>
              {event.emoji}
            </div>
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

        {/* KPI row */}
        <div className="kpi-row">
          <KPI label={t("Khách đã tạo", "Guests created")} value={event.guestCount} icon="users" tip={t("Tổng số khách đã nhập hoặc đã đăng ký", "Total guests created or registered")} />
          <KPI label={t("Đã đăng ký", "Registered")} value={event.registered} icon="ticket" tip={t("Khách đã hoàn tất form đăng ký", "Guests who completed registration")} />
          <KPI label={t("Đã check-in", "Checked in")} value={event.checkedIn} icon="check" tip={t("Khách đã có mặt và được xác nhận", "Guests confirmed present at the venue")} />
          <KPI label={t("Sức chứa", "Capacity")} value={`${fillPct}%`} icon="chart" tip={`${t("Đã đăng ký", "Registered")} ${event.registered}/${event.capacity}`} />
        </div>

        {/* Quick settings */}
        <Card
          title={t("Cài đặt nhanh", "Quick settings")}
          sub={t("Bật/tắt nhanh các tính năng phổ biến", "Toggle common features quickly")}
          icon="settings"
        >
          <OptRow
            title={t("Mở trang đăng ký công khai", "Open public registration")}
            desc={t("Cho khách tự đăng ký qua link", "Let guests self-register via link")}
            on={formOpen}
            onChange={setFormOpen}
            tip={t("Tab 'Trang đăng ký' để cấu hình thông tin thu thập", "Use the 'Registration form' tab to configure fields")}
          />
          <OptRow
            title={t("Gửi email xác nhận tự động", "Auto-send confirmation email")}
            desc={t("Khách nhận email kèm mã QR sau khi đăng ký", "Guests get an email with QR after registering")}
            on={confirmEmail}
            onChange={setConfirmEmail}
            tip={t("Sửa mẫu thư ở tab 'Thiệp & email'", "Edit templates in the 'Invites & email' tab")}
          />
          <OptRow
            title={t("Đang nhận check-in", "Check-in is open")}
            desc={t("Quầy lễ tân có thể quét mã QR khách", "Reception can scan guest QRs")}
            on={checkinOpen}
            onChange={setCheckinOpen}
            tip={t("Tắt khi sự kiện đã kết thúc để dừng nhận check-in", "Turn off when the event ends to stop accepting check-ins")}
          />
        </Card>

        {/* Activity log */}
        <Card
          title={t("Hoạt động gần đây", "Recent activity")}
          sub={t("Nhật ký thay đổi cấu hình", "Configuration change log")}
          icon="history"
        >
          <div style={{ display: "flex", flexDirection: "column", gap: 0 }}>
            {activity.map((a, i) => (
              <div key={i} style={{ padding: "10px 0", borderBottom: i < activity.length - 1 ? "1px solid var(--border)" : "none", display: "flex", gap: 12, fontSize: 13 }}>
                <div style={{ width: 28, height: 28, borderRadius: 999, background: "var(--surface-2)", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600, color: "var(--text-muted)", flexShrink: 0 }}>
                  {a.who.split(" ").map((w) => w[0]).join("").slice(0, 2)}
                </div>
                <div style={{ flex: 1 }}>
                  <b style={{ fontWeight: 500 }}>{a.who}</b> {a.what}
                </div>
                <span className="small" style={{ whiteSpace: "nowrap" }}>{a.time}</span>
              </div>
            ))}
          </div>
        </Card>
      </div>
    </div>
  );
}
