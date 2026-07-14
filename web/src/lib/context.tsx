"use client";
import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from "react";
import type { Company, Lang } from "./types";
import { COMPANIES } from "@/data/companies";

interface AppContextValue {
  company: Company;
  setCompany: (c: Company) => void;
  railCollapsed: boolean;
  setRailCollapsed: (v: boolean) => void;
  lang: Lang;
  setLang: (l: Lang) => void;
  cmdkOpen: boolean;
  setCmdkOpen: (v: boolean) => void;
  currentEventId: string | null;
  setCurrentEventId: (id: string | null) => void;
}

const AppContext = createContext<AppContextValue | null>(null);

export function AppProvider({ children }: { children: ReactNode }) {
  const [company, setCompany] = useState<Company>(COMPANIES[0]);
  const [railCollapsed, setRailCollapsed] = useState(false);
  const [lang, setLangState] = useState<Lang>("vi");
  const [cmdkOpen, setCmdkOpen] = useState(false);
  const [currentEventId, setCurrentEventId] = useState<string | null>(null);

  useEffect(() => {
    const stored = localStorage.getItem("lang") as Lang | null;
    if (stored === "vi" || stored === "en") setLangState(stored);
    const collapsed = localStorage.getItem("rail-collapsed");
    if (collapsed === "true") setRailCollapsed(true);
  }, []);

  const setLang = useCallback((l: Lang) => {
    setLangState(l);
    localStorage.setItem("lang", l);
  }, []);

  const handleSetRailCollapsed = useCallback((v: boolean) => {
    setRailCollapsed(v);
    localStorage.setItem("rail-collapsed", String(v));
  }, []);

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        setCmdkOpen(true);
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  return (
    <AppContext value={{ company, setCompany, railCollapsed, setRailCollapsed: handleSetRailCollapsed, lang, setLang, cmdkOpen, setCmdkOpen, currentEventId, setCurrentEventId }}>
      {children}
    </AppContext>
  );
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useApp must be inside AppProvider");
  return ctx;
}

export function useT() {
  const { lang } = useApp();
  return (vi: string, en: string) => lang === "en" ? en : vi;
}
