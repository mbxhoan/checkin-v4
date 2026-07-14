"use client";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { useApp, useT } from "@/lib/context";
import { type Event } from "@/lib/types";
import { Icon } from "@/components/ui/icon";
import { LangSwitcher } from "./lang-switcher";
import { EventSwitcher } from "./event-switcher";

interface TopbarProps {
  currentEvent?: Event;
}

export function Topbar({ currentEvent }: TopbarProps) {
  const t = useT();
  const router = useRouter();
  const { setCmdkOpen } = useApp();
  const [userMenu, setUserMenu] = useState(false);
  const isEventView = !!currentEvent;

  const userBtn = (
    <div style={{ position: "relative" }}>
      <button className="topbar__icon-btn" onClick={() => setUserMenu(!userMenu)} style={{ borderRadius: 999 }}>
        <span style={{ width: 28, height: 28, borderRadius: "50%", background: "linear-gradient(135deg,#3b82f6,#1d4ed8)", color: "#fff", display: "grid", placeItems: "center", fontSize: 11, fontWeight: 600 }}>SA</span>
      </button>
      {userMenu && (
        <>
          <div className="popover-backdrop" onClick={() => setUserMenu(false)} />
          <div className="popover" style={{ width: 240, right: 0, left: "auto", top: "calc(100% + 6px)" }}>
            <div style={{ padding: "12px 14px 8px", borderBottom: "1px solid var(--border)" }}>
              <div style={{ fontWeight: 600 }}>System Admin</div>
              <div className="small">admin@delfi.vn</div>
            </div>
            <div className="popover__list" style={{ padding: 4 }}>
              <div className="popover__item" onClick={() => { setUserMenu(false); router.push("/settings"); }}>
                <Icon name="user" size={16} /><span>{t("Tài khoản của tôi", "My account")}</span>
              </div>
              <div className="popover__item" onClick={() => { setUserMenu(false); router.push("/settings"); }}>
                <Icon name="settings" size={16} /><span>{t("Cài đặt công ty", "Company settings")}</span>
              </div>
            </div>
            <div style={{ borderTop: "1px solid var(--border)", padding: 4 }}>
              <div className="popover__item" style={{ color: "var(--danger)" }} onClick={() => { setUserMenu(false); router.push("/login"); }}>
                <Icon name="chevron_left" size={16} /><span>{t("Đăng xuất", "Sign out")}</span>
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );

  if (isEventView && currentEvent) {
    return (
      <header className="topbar">
        <EventSwitcher currentEvent={currentEvent} />
        <div className="topbar__spacer" />
        <button className="qa__btn" title={t("Xem trang đăng ký", "View registration page")}>
          <Icon name="eye" size={14} /><span className="qa__btn-label">{t("Xem trang đăng ký", "View registration")}</span>
        </button>
        <button className="qa__btn" title={`${currentEvent.guestCount.toLocaleString()} ${t("khách", "guests")}`}>
          <Icon name="users" size={14} /><span className="qa__btn-label">{currentEvent.guestCount.toLocaleString()} {t("khách", "guests")}</span>
        </button>
        <button className="qa__btn qa__btn--primary">
          <Icon name="save" size={14} /><span className="qa__btn-label">{t("Lưu thay đổi", "Save changes")}</span>
        </button>
        <LangSwitcher />
        <button className="topbar__icon-btn topbar__icon-btn--dot" title={t("Thông báo", "Notifications")}>
          <Icon name="bell" size={17} />
        </button>
        {userBtn}
      </header>
    );
  }

  return (
    <header className="topbar">
      <div className="topbar__spacer" />
      <button className="qa__btn" onClick={() => setCmdkOpen(true)}>
        <Icon name="sparkles" size={14} /><span className="qa__btn-label">{t("Tìm nhanh", "Quick search")}</span>
        <kbd style={{ marginLeft: 4, background: "var(--surface-2)", padding: "1px 6px", borderRadius: 4, fontSize: 11, color: "var(--text-muted)" }}>⌘K</kbd>
      </button>
      <LangSwitcher />
      <button className="topbar__icon-btn topbar__icon-btn--dot" title={t("Thông báo", "Notifications")}>
        <Icon name="bell" size={17} />
      </button>
      {userBtn}
    </header>
  );
}
