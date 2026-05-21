// shell.jsx — global SaaS shell pieces: switchers, command palette

const { useState: useS, useRef: useR, useEffect: useE, useMemo: useM } = React;

// ============= Company switcher =============
const CompanySwitcher = ({ company, onSwitch }) => {
  const t = useT();
  const [open, setOpen] = useS(false);
  const ref = useR();

  return (
    <div className="cmp-switch-wrap">
      <div className="cmp-switch" onClick={() => setOpen(true)} ref={ref}>
        <div className="cmp-switch__logo" style={{ background: company.color }}>
          {company.initials}
        </div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div className="cmp-switch__name">{company.name}</div>
          <div className="cmp-switch__plan">{t("Gói", "Plan:")} {company.plan}</div>
        </div>
        <Icon name="chevron" size={14} className="cmp-switch__caret" />
      </div>
      {open && (
        <>
          <div className="popover-backdrop" onClick={() => setOpen(false)} />
          <div className="popover popover--anchored popover--cmp">
            <div className="popover__section-label">{t("Chuyển công ty", "Switch company")}</div>
            <div className="popover__list" style={{ maxHeight: 240 }}>
              {COMPANIES.map((c) => (
                <div
                  key={c.id}
                  className={"popover__item" + (c.id === company.id ? " popover__item--active" : "")}
                  onClick={() => { onSwitch(c); setOpen(false); }}
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
              <button className="qa__btn"><Icon name="plus" />{t("Thêm công ty", "Add company")}</button>
              <button className="qa__btn"><Icon name="settings" />{t("Cài đặt", "Settings")}</button>
            </div>
          </div>
        </>
      )}
    </div>
  );
};

// ============= Event switcher (topbar pill) =============
const EventSwitcher = ({ event, events, onSwitch, onGoToList }) => {
  const t = useT();
  const [open, setOpen] = useS(false);
  const [q, setQ] = useS("");
  const [focusIdx, setFocusIdx] = useS(0);
  const inputRef = useR();

  useE(() => {
    if (open) setTimeout(() => inputRef.current?.focus(), 30);
  }, [open]);

  const filtered = useM(() => {
    const ql = q.toLowerCase();
    return events.filter(
      (e) => !ql || e.name.toLowerCase().includes(ql) || e.subtitle.toLowerCase().includes(ql) || e.city.toLowerCase().includes(ql),
    );
  }, [events, q]);

  // Group by status
  const groups = useM(() => {
    const order = ["active", "upcoming", "draft", "ended"];
    const g = {};
    for (const e of filtered) (g[e.status] = g[e.status] || []).push(e);
    return order.filter((s) => g[s]?.length).map((s) => [s, g[s]]);
  }, [filtered]);

  const flat = useM(() => groups.flatMap(([, items]) => items), [groups]);

  const onKey = (e) => {
    if (e.key === "ArrowDown") { e.preventDefault(); setFocusIdx((i) => Math.min(i + 1, flat.length - 1)); }
    else if (e.key === "ArrowUp") { e.preventDefault(); setFocusIdx((i) => Math.max(i - 1, 0)); }
    else if (e.key === "Enter") {
      const t = flat[focusIdx];
      if (t) { onSwitch(t); setOpen(false); setQ(""); }
    } else if (e.key === "Escape") { setOpen(false); setQ(""); }
  };

  return (
    <div className="evt-switch-wrap">
      <div className="evt-switch" onClick={() => setOpen(true)}>
        <div className="evt-switch__icon" style={{ background: event.color }}>{event.emoji}</div>
        <div className="evt-switch__main">
          <div className="evt-switch__name">{event.name}</div>
          <div className="evt-switch__meta">{formatRange(event.startDate, event.endDate)} • {event.city}</div>
        </div>
        <Icon name="chevron" size={14} className="evt-switch__caret" />
      </div>
      {open && (
        <>
          <div className="popover-backdrop" onClick={() => { setOpen(false); setQ(""); }} />
          <div className="popover popover--anchored">
            <div className="popover__search">
              <Icon name="settings" size={15} style={{ color: "var(--text-muted)" }} />
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
                        className={"popover__item" +
                          (e.id === event.id ? " popover__item--active" : "") +
                          (idx === focusIdx ? " popover__item--focused" : "")}
                        onMouseEnter={() => setFocusIdx(idx)}
                        onClick={() => { onSwitch(e); setOpen(false); setQ(""); }}
                      >
                        <div className="popover__item-icon" style={{ background: e.color }}>{e.emoji}</div>
                        <div className="popover__item-main">
                          <div className="popover__item-name">{e.name}</div>
                          <div className="popover__item-meta">{formatRange(e.startDate, e.endDate)} • {e.city}</div>
                        </div>
                        {e.id === event.id ? (
                          <Icon name="check" size={16} style={{ color: "var(--primary)" }} />
                        ) : (
                          <span className="popover__item-trail">{e.guestCount.toLocaleString()} {t("khách", "guests")}</span>
                        )}
                      </div>
                    );
                  })}
                </div>
              ))}
            </div>
            <div className="popover__footer">
              <button className="qa__btn" onClick={() => { setOpen(false); onGoToList(); }}>
                <Icon name="grid" />{t("Tất cả sự kiện", "All events")}
              </button>
              <button className="qa__btn qa__btn--primary">
                <Icon name="plus" />{t("Tạo sự kiện mới", "Create new event")}
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
};

// ============= Command palette (Cmd+K) =============
const CommandPalette = ({ open, onClose, events, onJump, onGoTo }) => {
  const [q, setQ] = useS("");
  const [focusIdx, setFocusIdx] = useS(0);
  const inputRef = useR();

  useE(() => {
    if (open) { setQ(""); setFocusIdx(0); setTimeout(() => inputRef.current?.focus(), 30); }
  }, [open]);

  const ACTIONS = [
    { id: "list", label: "Xem tất cả sự kiện", icon: "grid", group: "Điều hướng", run: () => onGoTo("events") },
    { id: "new", label: "Tạo sự kiện mới", icon: "plus", group: "Hành động", run: () => alert("Tạo sự kiện mới") },
    { id: "guests", label: "Khách hàng toàn công ty (CRM)", icon: "users", group: "Điều hướng", run: () => onGoTo("crm") },
    { id: "reports", label: "Báo cáo toàn công ty", icon: "chart", group: "Điều hướng", run: () => onGoTo("reports") },
    { id: "settings", label: "Cài đặt công ty", icon: "settings", group: "Điều hướng", run: () => onGoTo("settings") },
    { id: "templates", label: "Mẫu thư & QR dùng chung", icon: "mail", group: "Điều hướng", run: () => onGoTo("templates") },
  ];

  const ql = q.toLowerCase();
  const evtMatches = events.filter((e) => !ql || e.name.toLowerCase().includes(ql) || e.subtitle.toLowerCase().includes(ql));
  const actMatches = ACTIONS.filter((a) => !ql || a.label.toLowerCase().includes(ql));

  const flat = [
    ...evtMatches.map((e) => ({ type: "event", ...e })),
    ...actMatches.map((a) => ({ type: "action", ...a })),
  ];

  const onKey = (e) => {
    if (e.key === "ArrowDown") { e.preventDefault(); setFocusIdx((i) => Math.min(i + 1, flat.length - 1)); }
    else if (e.key === "ArrowUp") { e.preventDefault(); setFocusIdx((i) => Math.max(i - 1, 0)); }
    else if (e.key === "Enter") {
      const t = flat[focusIdx];
      if (!t) return;
      onClose();
      if (t.type === "event") onJump(t); else t.run();
    } else if (e.key === "Escape") { onClose(); }
  };

  if (!open) return null;

  return (
    <div className="cmdk-backdrop" onClick={onClose}>
      <div className="cmdk" onClick={(e) => e.stopPropagation()}>
        <div className="cmdk__search">
          <Icon name="sparkles" />
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
                  className={"cmdk__item" + (i === focusIdx ? " cmdk__item--focused" : "")}
                  onMouseEnter={() => setFocusIdx(i)}
                  onClick={() => { onClose(); onJump(e); }}
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
                    className={"cmdk__item" + (idx === focusIdx ? " cmdk__item--focused" : "")}
                    onMouseEnter={() => setFocusIdx(idx)}
                    onClick={() => { onClose(); a.run(); }}
                  >
                    <div className="cmdk__item-icon"><Icon name={a.icon} /></div>
                    <div className="cmdk__item-main">
                      <div className="cmdk__item-name">{a.label}</div>
                    </div>
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
};

Object.assign(window, { CompanySwitcher, EventSwitcher, CommandPalette });
