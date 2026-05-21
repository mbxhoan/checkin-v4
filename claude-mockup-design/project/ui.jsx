// ui.jsx — shared UI primitives & icons
const { useState, useRef, useEffect, useCallback } = React;

// ---------------- Icons (inline SVG) ----------------
const Icon = ({ name, size = 16, className = "", style }) => {
  const paths = {
    info: <><circle cx="12" cy="12" r="9.5" /><path d="M12 8h.01M11 12h1v5h1" /></>,
    help: <><circle cx="12" cy="12" r="9.5" /><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .8-1 1.7M12 17h.01" /></>,
    home: <path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9z" />,
    calendar: <><rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M3.5 9.5h17M8 3.5v3M16 3.5v3"/></>,
    ticket: <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8zM10 6v12"/>,
    chart: <><path d="M3 21h18"/><path d="M7 17v-6M12 17V7M17 17v-9"/></>,
    mail: <><rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M4 7l8 6 8-6"/></>,
    layout: <><rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M3.5 9h17M9 9v11.5"/></>,
    invite: <><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="M3.5 7.5l8.5 6 8.5-6"/></>,
    print: <><path d="M7 9V4h10v5M7 18H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v6H7z"/></>,
    raffle: <><path d="M5 7h14M9 7v-.5a3 3 0 0 1 3-3 3 3 0 0 1 3 3V7"/><rect x="3.5" y="7" width="17" height="13.5" rx="2"/></>,
    user: <><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></>,
    users: <><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-6 7-6s7 2.5 7 6"/><circle cx="17" cy="9" r="3"/><path d="M22 18c0-2.5-2-4.5-5-4.5"/></>,
    settings: <><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.5 1.5 0 0 0 .3 1.65l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.5 1.5 0 0 0-1.65-.3 1.5 1.5 0 0 0-.9 1.37V21a2 2 0 0 1-4 0v-.09a1.5 1.5 0 0 0-1-1.37 1.5 1.5 0 0 0-1.65.3l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.5 1.5 0 0 0 .3-1.65 1.5 1.5 0 0 0-1.37-.9H3a2 2 0 0 1 0-4h.09A1.5 1.5 0 0 0 4.46 9a1.5 1.5 0 0 0-.3-1.65l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.5 1.5 0 0 0 1.65.3H9a1.5 1.5 0 0 0 .9-1.37V3a2 2 0 0 1 4 0v.09a1.5 1.5 0 0 0 .9 1.37 1.5 1.5 0 0 0 1.65-.3l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.5 1.5 0 0 0-.3 1.65V9a1.5 1.5 0 0 0 1.37.9H21a2 2 0 0 1 0 4h-.09a1.5 1.5 0 0 0-1.37.9z"/></>,
    plus: <path d="M12 5v14M5 12h14"/>,
    trash: <><path d="M4 7h16M9 7V4h6v3M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13M10 11v7M14 11v7"/></>,
    drag: <><circle cx="9" cy="6" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="9" cy="18" r="1.2"/><circle cx="15" cy="6" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="15" cy="18" r="1.2"/></>,
    chevron: <path d="M6 9l6 6 6-6"/>,
    copy: <><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></>,
    eye: <><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></>,
    upload: <><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></>,
    qr: <><rect x="3.5" y="3.5" width="6" height="6"/><rect x="14.5" y="3.5" width="6" height="6"/><rect x="3.5" y="14.5" width="6" height="6"/><path d="M14.5 14.5h2v2M18.5 14.5v2M16.5 18.5h4M14.5 20.5h2"/></>,
    payment: <><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/></>,
    camera: <><path d="M3 8a2 2 0 0 1 2-2h2l2-2h6l2 2h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8z"/><circle cx="12" cy="13" r="3.5"/></>,
    image: <><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 17l-4-4-9 7"/></>,
    check: <path d="M5 12l5 5L20 7"/>,
    bell: <><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 21a2 2 0 0 0 4 0"/></>,
    sparkles: <path d="M12 3v4M12 17v4M5 12H1M23 12h-4M6 6l-2-2M20 20l-2-2M6 18l-2 2M20 4l-2 2"/>,
    save: <><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></>,
    history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></>,
    chevron_left: <path d="M15 18l-6-6 6-6"/>,
    chevron_right: <path d="M9 18l6-6-6-6"/>,
    refresh: <><path d="M21 12a9 9 0 1 1-3-6.7M21 4v5h-5"/></>,
    desktop: <><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></>,
    phone: <><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/></>,
    file: <><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6"/></>,
    speaker: <><path d="M3 9v6h4l5 4V5L7 9H3z"/><path d="M16 8a5 5 0 0 1 0 8"/></>,
    nfc: <><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 8c2 2 2 6 0 8M12 6c4 3 4 9 0 12"/></>,
    grid: <><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></>,
    flag: <><path d="M4 21V4M4 4h12l-2 4 2 4H4"/></>,
  };
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 24 24"
      fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"
      className={className} style={style}>
      {paths[name] || null}
    </svg>
  );
};

// ---------------- Tooltip ----------------
const Tip = ({ text, children, align = "center" }) => {
  if (!text) return children || null;
  return (
    <span className={"tip" + (align === "right" ? " tip--right" : "")} tabIndex={children ? -1 : 0}>
      {children || <span className="tip__trigger" aria-label="Trợ giúp">?</span>}
      <span className="tip__bubble" role="tooltip">{text}</span>
    </span>
  );
};

// ---------------- Toggle ----------------
const Toggle = ({ on, onChange, disabled }) => (
  <button
    type="button"
    className={"toggle" + (on ? " toggle--on" : "") + (disabled ? " toggle--disabled" : "")}
    aria-pressed={on}
    aria-disabled={disabled}
    onClick={() => !disabled && onChange(!on)}
  />
);

// ---------------- OptRow (settings row) ----------------
const OptRow = ({ title, desc, tip, on, onChange, disabled, children, right }) => (
  <div className="opt-row">
    <div className="opt-row__main">
      <div className="opt-row__title">
        {title}
        {tip && <Tip text={tip} />}
      </div>
      {desc && <div className="opt-row__desc">{desc}</div>}
    </div>
    {right}
    {onChange && <Toggle on={on} onChange={onChange} disabled={disabled} />}
    {children}
  </div>
);

// ---------------- Section group header ----------------
const SetGroup = ({ icon, title, sub, children }) => (
  <div className="setgrp">
    <div className="setgrp__head">
      {icon && <Icon name={icon} />}
      {title}
      {sub && <span className="setgrp__head-sub">{sub}</span>}
    </div>
    {children}
  </div>
);

// ---------------- Field ----------------
const Field = ({ label, required, hint, tip, children }) => (
  <div className="field">
    <label className={"field__label" + (required ? " field__label--req" : "")}>
      {label}
      {tip && <Tip text={tip} />}
    </label>
    {children}
    {hint && <div className="field__hint">{hint}</div>}
  </div>
);

// ---------------- Color picker ----------------
const ColorPick = ({ value, onChange }) => {
  const ref = useRef();
  return (
    <button type="button" className="color-pick" onClick={() => ref.current?.click()}>
      <span className="color-pick__sw" style={{ background: value }} />
      <span className="color-pick__hex">{value.toUpperCase()}</span>
      <input ref={ref} type="color" value={value} onChange={(e) => onChange(e.target.value)} style={{ display: "none" }} />
    </button>
  );
};

// ---------------- Card ----------------
const Card = ({ title, sub, icon, action, children, padded = true }) => (
  <section className="card">
    {(title || action) && (
      <header className="card__head">
        {icon && <Icon name={icon} />}
        <div>
          {title && <h3>{title}</h3>}
          {sub && <p className="card__sub">{sub}</p>}
        </div>
        {action && <div className="qa">{action}</div>}
      </header>
    )}
    <div className={padded ? "card__body" : ""}>{children}</div>
  </section>
);

Object.assign(window, { Icon, Tip, Toggle, OptRow, SetGroup, Field, ColorPick, Card });
