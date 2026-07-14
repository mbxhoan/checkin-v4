"use client";
import { useState } from "react";
import { useApp, useT } from "@/lib/context";
import { COMPANIES } from "@/data/companies";
import { Icon } from "@/components/ui/icon";

export function CompanySwitcher() {
  const { company, setCompany } = useApp();
  const t = useT();
  const [open, setOpen] = useState(false);

  return (
    <div className="cmp-switch-wrap" style={{ position: "relative" }}>
      <div className="cmp-switch" onClick={() => setOpen(true)}>
        <div className="cmp-switch__logo" style={{ background: company.color }}>{company.initials}</div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div className="cmp-switch__name">{company.name}</div>
          <div className="cmp-switch__plan">{t("Gói", "Plan:")} {company.plan}</div>
        </div>
        <Icon name="chevron" size={14} className="cmp-switch__caret" />
      </div>
      {open && (
        <>
          <div className="popover-backdrop" onClick={() => setOpen(false)} />
          <div className="popover" style={{ top: "calc(100% + 8px)", left: 0, width: 300 }}>
            <div className="popover__section-label">{t("Chuyển công ty", "Switch company")}</div>
            <div className="popover__list">
              {COMPANIES.map((c) => (
                <div
                  key={c.id}
                  className={`popover__item${c.id === company.id ? " popover__item--active" : ""}`}
                  onClick={() => { setCompany(c); setOpen(false); }}
                >
                  <div className="popover__item-icon" style={{ background: c.color }}>{c.initials}</div>
                  <div className="popover__item-main">
                    <div className="popover__item-name">{c.name}</div>
                    <div className="popover__item-meta">{c.eventCount} {t("sự kiện", "events")} • {c.plan}</div>
                  </div>
                  {c.id === company.id && <Icon name="check" size={16} style={{ color: "var(--primary)" }} />}
                </div>
              ))}
            </div>
            <div className="popover__footer">
              <button className="qa__btn"><Icon name="plus" size={14} />{t("Thêm công ty", "Add company")}</button>
              <button className="qa__btn"><Icon name="settings" size={14} />{t("Cài đặt", "Settings")}</button>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
