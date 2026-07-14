export interface Company {
  id: string;
  name: string;
  code: string;
  color: string;
  initials: string;
  plan: string;
  eventCount: number;
}

export interface Event {
  id: string;
  companyId: string;
  name: string;
  subtitle: string;
  startDate: string;
  endDate: string;
  city: string;
  venue: string;
  status: "active" | "upcoming" | "ended" | "draft";
  statusLabel: string;
  color: string;
  guestCount: number;
  registered: number;
  checkedIn: number;
  capacity: number;
  lastActivity: string;
  cover: string;
  emoji: string;
}

export interface Guest {
  id: string;
  eventId: string;
  name: string;
  email: string;
  phone: string;
  group: string;
  source: "manual" | "import" | "registration";
  registeredAt: string;
  checkedIn: boolean;
  checkedInAt?: string;
  status: "registered" | "checked_in" | "checked_out" | "cancelled";
  qrcode: string;
}

export type Lang = "vi" | "en";

export interface Field {
  id: number;
  label: string;
  key: string;
  type: "text" | "email" | "phone" | "number" | "date" | "select" | "radio" | "file" | "code";
  required: boolean;
  unique: boolean;
  shownOnForm: boolean;
  locked?: boolean;
  options?: { label: string; value: string }[];
}

export const STATUS_COLORS: Record<string, { fg: string; bg: string; label: string; labelEn: string }> = {
  active: { fg: "#16a34a", bg: "#e8f7ee", label: "Đang triển khai", labelEn: "Active" },
  upcoming: { fg: "#0891b2", bg: "#e0f2fe", label: "Sắp diễn ra", labelEn: "Upcoming" },
  ended: { fg: "#6b7280", bg: "#f3f4f6", label: "Đã kết thúc", labelEn: "Ended" },
  draft: { fg: "#ca8a04", bg: "#fef3c7", label: "Bản nháp", labelEn: "Draft" },
};

export function formatDate(d: string): string {
  const [y, m, day] = d.split("-");
  return `${day}/${m}/${y}`;
}
export function formatDateShort(d: string): string {
  const [, m, day] = d.split("-");
  return `${day}/${m}`;
}
export function formatRange(s: string, e: string): string {
  if (s === e) return formatDate(s);
  const [sy, sm] = s.split("-");
  const [ey, em] = e.split("-");
  if (sy === ey && sm === em) return `${s.split("-")[2]}–${formatDate(e)}`;
  return `${formatDate(s)} → ${formatDate(e)}`;
}
