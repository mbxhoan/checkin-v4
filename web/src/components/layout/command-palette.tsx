"use client";
import { useRef, useEffect, useState, useMemo } from "react";
import { useRouter } from "next/navigation";
import { useApp } from "@/lib/context";
import { EVENTS } from "@/data/events";
import { STATUS_COLORS, formatRange } from "@/lib/types";
import { Icon } from "@/components/ui/icon";

const ACTIONS = [
  { id: "list", label: "Xem tất cả sự kiện", icon: "grid" as const, path: "/events" },
  { id: "crm", label: "Khách hàng toàn công ty (CRM)", icon: "users" as const, path: "/crm" },
  { id: "reports", label: "Báo cáo toàn công ty", icon: "chart" as const, path: "/reports" },
  { id: "settings", label: "Cài đặt công ty", icon: "settings" as const, path: "/settings" },
  { id: "templates", label: "Mẫu thư & QR dùng chung", icon: "mail" as const, path: "/templates" },
];

export function CommandPalette() {
  const { cmdkOpen, setCmdkOpen } = useApp();
  const router = useRouter();
  const [q, setQ] = useState("");
  const [focusIdx, setFocusIdx] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (cmdkOpen) { setQ(""); setFocusIdx(0); setTimeout(() => inputRef.current?.focus(), 30); }
  }, [cmdkOpen]);

  const ql = q.toLowerCase();
  const evtMatches = useMemo(() => EVENTS.filter((e) => !ql || e.name.toLowerCase().includes(ql) || e.subtitle.toLowerCase().includes(ql)), [ql]);
  const actMatches = useMemo(() => ACTIONS.filter((a) => !ql || a.label.toLowerCase().includes(ql)), [ql]);

  const flat = [...evtMatches.map((e) => ({ type: "event" as const, ...e })), ...actMatches.map((a) => ({ type: "action" as const, ...a }))];

  const onKey = (e: React.KeyboardEvent) => {
    if (e.key === "ArrowDown") { e.preventDefault(); setFocusIdx((i) => Math.min(i + 1, flat.length - 1)); }
    else if (e.key === "ArrowUp") { e.preventDefault(); setFocusIdx((i) => Math.max(i - 1, 0)); }
    else if (e.key === "Enter") {
      const item = flat[focusIdx];
      if (!item) return;
      setCmdkOpen(false);
      if (item.type === "event") router.push(`/events/${item.id}`);
      else router.push(item.path);
    }
    else if (e.key === "Escape") { setCmdkOpen(false); }
  };

  if (!cmdkOpen) return null;

  return (
    <div className="cmdk-backdrop" onClick={() => setCmdkOpen(false)}>
      <div className="cmdk" onClick={(e) => e.stopPropagation()}>
        <div className="cmdk__search">
          <Icon name="sparkles" size={18} style={{ color: "var(--text-muted)" }} />
          <input
            ref={inputRef}
            placeholder="Tìm sự kiện, mục cấu hình, hành động..."
            value={q}
            onChange={(ev) => { setQ(ev.target.value); setFocusIdx(0); }}
            onKeyDown={onKey}
          />
          <span className="cmdk__hint">⌘K</span>
        </div>
        <div className="cmdk__list">
          {evtMatches.length > 0 && (
            <>
              <div className="cmdk__group-label">Sự kiện ({evtMatches.length})</div>
              {evtMatches.map((e, i) => (
                <div
                  key={e.id}
                  className={`cmdk__item${i === focusIdx ? " cmdk__item--focused" : ""}`}
                  onMouseEnter={() => setFocusIdx(i)}
                  onClick={() => { setCmdkOpen(false); router.push(`/events/${e.id}`); }}
                >
                  <div className="cmdk__item-icon" style={{ background: e.color, color: "#fff" }}>{e.emoji}</div>
                  <div className="cmdk__item-main">
                    <div className="cmdk__item-name">{e.name}</div>
                    <div className="cmdk__item-sub">{formatRange(e.startDate, e.endDate)} • {e.city}</div>
                  </div>
                  <span className="cmdk__item-trail">{STATUS_COLORS[e.status].label}</span>
                </div>
              ))}
            </>
          )}
          {actMatches.length > 0 && (
            <>
              <div className="cmdk__group-label">Hành động</div>
              {actMatches.map((a, i) => {
                const idx = evtMatches.length + i;
                return (
                  <div
                    key={a.id}
                    className={`cmdk__item${idx === focusIdx ? " cmdk__item--focused" : ""}`}
                    onMouseEnter={() => setFocusIdx(idx)}
                    onClick={() => { setCmdkOpen(false); router.push(a.path); }}
                  >
                    <div className="cmdk__item-icon"><Icon name={a.icon} size={14} /></div>
                    <div className="cmdk__item-main"><div className="cmdk__item-name">{a.label}</div></div>
                  </div>
                );
              })}
            </>
          )}
          {flat.length === 0 && <div className="popover__empty">Không có kết quả phù hợp</div>}
        </div>
        <div className="cmdk__footer">
          <span><kbd>↑↓</kbd> Di chuyển</span>
          <span><kbd>↵</kbd> Chọn</span>
          <span><kbd>Esc</kbd> Đóng</span>
        </div>
      </div>
    </div>
  );
}
