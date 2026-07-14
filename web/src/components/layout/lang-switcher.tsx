"use client";
import { useState } from "react";
import { useApp } from "@/lib/context";
import type { Lang } from "@/lib/types";

export function LangSwitcher() {
  const { lang, setLang } = useApp();
  const [open, setOpen] = useState(false);

  return (
    <div className="lang-switch">
      <button className="lang-switch__btn" onClick={() => setOpen(!open)}>
        <span className="lang-switch__flag">{lang === "vi" ? "🇻🇳" : "🇬🇧"}</span>
        {lang === "vi" ? "VI" : "EN"}
      </button>
      {open && (
        <>
          <div className="popover-backdrop" onClick={() => setOpen(false)} />
          <div className="popover" style={{ width: 160, top: "calc(100% + 6px)", right: 0, left: "auto" }}>
            <div className="popover__list" style={{ padding: 4 }}>
              {(["vi", "en"] as Lang[]).map((l) => (
                <div
                  key={l}
                  className={`popover__item${lang === l ? " popover__item--active" : ""}`}
                  onClick={() => { setLang(l); setOpen(false); }}
                >
                  <span style={{ fontSize: 18 }}>{l === "vi" ? "🇻🇳" : "🇬🇧"}</span>
                  <span>{l === "vi" ? "Tiếng Việt" : "English"}</span>
                </div>
              ))}
            </div>
          </div>
        </>
      )}
    </div>
  );
}
