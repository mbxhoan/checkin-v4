// i18n.jsx — bilingual VI/EN support
//
// Usage:
//   const t = useT();
//   <h1>{t("Sự kiện", "Events")}</h1>
//
// Translations are colocated with usage (no key dictionary).
// Default language is Vietnamese. Stored in localStorage as 'lang'.

const LangContext = React.createContext({ lang: "vi", setLang: () => {} });

function LangProvider({ children }) {
  const [lang, setLangState] = React.useState(() => {
    try { return localStorage.getItem("lang") || "vi"; } catch (e) { return "vi"; }
  });
  const setLang = React.useCallback((l) => {
    setLangState(l);
    try { localStorage.setItem("lang", l); } catch (e) {}
    document.documentElement.setAttribute("lang", l);
  }, []);
  React.useEffect(() => {
    document.documentElement.setAttribute("lang", lang);
  }, [lang]);
  const value = React.useMemo(() => ({ lang, setLang }), [lang, setLang]);
  return <LangContext.Provider value={value}>{children}</LangContext.Provider>;
}

// Returns a translator function. Call as t(vi, en).
const useT = () => {
  const { lang } = React.useContext(LangContext);
  return React.useCallback((vi, en) => (lang === "en" && en !== undefined ? en : vi), [lang]);
};

const useLang = () => React.useContext(LangContext);

// Inline component for places where you'd otherwise need to call t() out of context
const T = ({ vi, en }) => {
  const t = useT();
  return t(vi, en);
};

// Language switcher button (renders flag + dropdown)
const LangSwitcher = ({ compact = false }) => {
  const { lang, setLang } = useLang();
  const [open, setOpen] = React.useState(false);
  const items = [
    { code: "vi", label: "Tiếng Việt", short: "VI", flag: "🇻🇳" },
    { code: "en", label: "English", short: "EN", flag: "🇬🇧" },
  ];
  const cur = items.find((i) => i.code === lang) || items[0];
  return (
    <div className="lang-switch" style={{ position: "relative" }}>
      <button className="lang-switch__btn" onClick={() => setOpen(!open)} title="Đổi ngôn ngữ / Change language">
        <span className="lang-switch__flag" aria-hidden="true">{cur.flag}</span>
        {!compact && <span className="lang-switch__label">{cur.short}</span>}
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ opacity: 0.6 }}><path d="M6 9l6 6 6-6" /></svg>
      </button>
      {open && (
        <>
          <div className="popover-backdrop" onClick={() => setOpen(false)} />
          <div className="popover popover--anchored lang-switch__pop">
            {items.map((it) => (
              <div
                key={it.code}
                className={"popover__item" + (it.code === lang ? " popover__item--active" : "")}
                onClick={() => { setLang(it.code); setOpen(false); }}
              >
                <span style={{ fontSize: 18 }}>{it.flag}</span>
                <span style={{ flex: 1 }}>{it.label}</span>
                {it.code === lang && (
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" style={{ color: "var(--primary)" }}><path d="M5 12l5 5L20 7" /></svg>
                )}
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  );
};

Object.assign(window, { LangContext, LangProvider, useT, useLang, T, LangSwitcher });
