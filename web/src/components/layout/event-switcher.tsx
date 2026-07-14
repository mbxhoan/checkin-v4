"use client";
import { useState, useRef, useEffect, useMemo } from "react";
import { useRouter } from "next/navigation";
import { useT } from "@/lib/context";
import { EVENTS } from "@/data/events";
import { STATUS_COLORS, formatRange, type Event } from "@/lib/types";
import { Icon } from "@/components/ui/icon";

interface EventSwitcherProps {
  currentEvent: Event;
}

export function EventSwitcher({ currentEvent }: EventSwitcherProps) {
  const t = useT();
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [q, setQ] = useState("");
  const [focusIdx, setFocusIdx] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (open) setTimeout(() => inputRef.current?.focus(), 30);
  }, [open]);

  const filtered = useMemo(() => {
    const ql = q.toLowerCase();
    return EVENTS.filter((e) => !ql || e.name.toLowerCase().includes(ql) || e.subtitle.toLowerCase().includes(ql) || e.city.toLowerCase().includes(ql));
  }, [q]);

  const groups = useMemo(() => {
    const order: Event["status"][] = ["active", "upcoming", "draft", "ended"];
    const g: Partial<Record<Event["status"], Event[]>> = {};
    for (const e of filtered) (g[e.status] = g[e.status] || []).push(e);
    return order.filter((s) => g[s]?.length).map((s) => [s, g[s]!] as [Event["status"], Event[]]);
  }, [filtered]);

  const flat = useMemo(() => groups.flatMap(([, items]) => items), [groups]);

  const onKey = (e: React.KeyboardEvent) => {
    if (e.key === "ArrowDown") { e.preventDefault(); setFocusIdx((i) => Math.min(i + 1, flat.length - 1)); }
    else if (e.key === "ArrowUp") { e.preventDefault(); setFocusIdx((i) => Math.max(i - 1, 0)); }
    else if (e.key === "Enter") { const ev = flat[focusIdx]; if (ev) { router.push(`/events/${ev.id}`); setOpen(false); setQ(""); } }
    else if (e.key === "Escape") { setOpen(false); setQ(""); }
  };

  return (
    <div className="evt-switch-wrap" style={{ position: "relative" }}>
      <div className="evt-switch" onClick={() => setOpen(true)}>
        <div className="evt-switch__icon" style={{ background: currentEvent.color }}>{currentEvent.emoji}</div>
        <div className="evt-switch__main">
          <div className="evt-switch__name">{currentEvent.name}</div>
          <div className="evt-switch__meta">{formatRange(currentEvent.startDate, currentEvent.endDate)} • {currentEvent.city}</div>
        </div>
        <Icon name="chevron" size={14} className="evt-switch__caret" />
      </div>

      {open && (
        <>
          <div className="popover-backdrop" onClick={() => { setOpen(false); setQ(""); }} />
          <div className="popover" style={{ top: "calc(100% + 8px)", left: 0 }}>
            <div className="popover__search">
              <Icon name="search" size={15} style={{ color: "var(--text-muted)" }} />
              <input
                ref={inputRef}
                placeholder={t("Tìm sự kiện theo tên, thành phố...", "Find event by name, city...")}
                value={q}
                onChange={(e) => { setQ(e.target.value); setFocusIdx(0); }}
                onKeyDown={onKey}
              />
              <span className="cmdk__hint">Esc</span>
            </div>
            <div className="popover__list">
              {groups.length === 0 && <div className="popover__empty">{t("Không tìm thấy sự kiện nào", "No events found")}</div>}
              {groups.map(([status, items], gi) => (
                <div key={status}>
                  <div className="popover__section-label">{t(STATUS_COLORS[status].label, STATUS_COLORS[status].labelEn)}</div>
                  {items.map((e, ii) => {
                    const idx = groups.slice(0, gi).reduce((a, [, b]) => a + b.length, 0) + ii;
                    return (
                      <div
                        key={e.id}
                        className={`popover__item${e.id === currentEvent.id ? " popover__item--active" : ""}${idx === focusIdx ? " popover__item--focused" : ""}`}
                        onMouseEnter={() => setFocusIdx(idx)}
                        onClick={() => { router.push(`/events/${e.id}`); setOpen(false); setQ(""); }}
                      >
                        <div className="popover__item-icon" style={{ background: e.color }}>{e.emoji}</div>
                        <div className="popover__item-main">
                          <div className="popover__item-name">{e.name}</div>
                          <div className="popover__item-meta">{formatRange(e.startDate, e.endDate)} • {e.city}</div>
                        </div>
                        {e.id === currentEvent.id
                          ? <Icon name="check" size={16} style={{ color: "var(--primary)" }} />
                          : <span className="popover__item-trail">{e.guestCount.toLocaleString()} {t("khách", "guests")}</span>}
                      </div>
                    );
                  })}
                </div>
              ))}
            </div>
            <div className="popover__footer">
              <button className="qa__btn" onClick={() => { setOpen(false); router.push("/events"); }}>
                <Icon name="grid" size={14} />{t("Tất cả sự kiện", "All events")}
              </button>
              <button className="qa__btn qa__btn--primary">
                <Icon name="plus" size={14} />{t("Tạo sự kiện mới", "Create new event")}
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
