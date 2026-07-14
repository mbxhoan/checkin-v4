"use client";
import { useApp, AppProvider } from "@/lib/context";
import { EVENTS } from "@/data/events";
import { Sidebar } from "@/components/layout/sidebar";
import { Topbar } from "@/components/layout/topbar";
import { CommandPalette } from "@/components/layout/command-palette";

function DashboardShell({ children }: { children: React.ReactNode }) {
  const { railCollapsed, currentEventId } = useApp();
  const currentEvent = currentEventId ? EVENTS.find((e) => e.id === currentEventId) : undefined;

  return (
    <div className={`app${railCollapsed ? " app--rail-collapsed" : ""}`}>
      <Sidebar currentEvent={currentEvent} />
      <div className="main">
        <Topbar currentEvent={currentEvent} />
        {children}
      </div>
      <CommandPalette />
    </div>
  );
}

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <AppProvider>
      <DashboardShell>{children}</DashboardShell>
    </AppProvider>
  );
}
