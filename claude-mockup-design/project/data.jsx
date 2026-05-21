// data.jsx — mock data for events / companies

const COMPANIES = [
  { id: "forest", name: "FOREST Medical Group", code: "CMP-L3IP2V1P", color: "#2563eb", initials: "FM", plan: "Doanh nghiệp", eventCount: 8 },
  { id: "dentalvn", name: "Dental Vietnam JSC", code: "CMP-DENT-001", color: "#7c3aed", initials: "DV", plan: "Tiêu chuẩn", eventCount: 3 },
  { id: "savico", name: "Savico Group", code: "CMP-SAV-22", color: "#16a34a", initials: "SV", plan: "Doanh nghiệp", eventCount: 12 },
];

const EVENTS = [
  {
    id: "videc-2026", companyId: "forest", name: "VIDEC 2026",
    subtitle: "Hội nghị Nha khoa Quốc tế Việt Nam",
    startDate: "2026-08-01", endDate: "2026-08-31",
    city: "Hồ Chí Minh", venue: "Trung tâm Hội nghị SECC",
    status: "active", statusLabel: "Đang triển khai",
    color: "#2563eb",
    guestCount: 1247, registered: 892, checkedIn: 0,
    capacity: 1500,
    lastActivity: "2 phút trước",
    cover: "linear-gradient(135deg, #2563eb, #1e3a8a)",
    emoji: "🦷",
  },
  {
    id: "savico-agm-2026", companyId: "forest", name: "Đại hội cổ đông 2026",
    subtitle: "Annual General Meeting — Q2 2026",
    startDate: "2026-06-15", endDate: "2026-06-15",
    city: "Hà Nội", venue: "Khách sạn JW Marriott",
    status: "upcoming", statusLabel: "Sắp diễn ra",
    color: "#16a34a",
    guestCount: 320, registered: 245, checkedIn: 0,
    capacity: 400,
    lastActivity: "1 giờ trước",
    cover: "linear-gradient(135deg, #16a34a, #064e3b)",
    emoji: "📊",
  },
  {
    id: "forest-symposium", companyId: "forest", name: "Forest Dental Symposium",
    subtitle: "Hội thảo chuyên đề tháng 5",
    startDate: "2026-05-20", endDate: "2026-05-22",
    city: "Đà Nẵng", venue: "Furama Resort",
    status: "active", statusLabel: "Đang diễn ra",
    color: "#dc2626",
    guestCount: 540, registered: 540, checkedIn: 387,
    capacity: 600,
    lastActivity: "Đang diễn ra • 5 phút trước",
    cover: "linear-gradient(135deg, #dc2626, #7f1d1d)",
    emoji: "🎤",
  },
  {
    id: "workshop-q1", companyId: "forest", name: "Workshop Nha khoa thẩm mỹ Q1",
    subtitle: "Đã kết thúc — báo cáo cuối kỳ",
    startDate: "2026-03-12", endDate: "2026-03-13",
    city: "Hồ Chí Minh", venue: "Sheraton Saigon",
    status: "ended", statusLabel: "Đã kết thúc",
    color: "#6b7280",
    guestCount: 180, registered: 180, checkedIn: 165,
    capacity: 200,
    lastActivity: "2 tháng trước",
    cover: "linear-gradient(135deg, #6b7280, #374151)",
    emoji: "✨",
  },
  {
    id: "hanoi-expo", companyId: "forest", name: "Triển lãm vật tư nha khoa Hà Nội",
    subtitle: "Northern Dental Expo 2026",
    startDate: "2026-07-10", endDate: "2026-07-12",
    city: "Hà Nội", venue: "Cung Triển lãm Giảng Võ",
    status: "upcoming", statusLabel: "Sắp diễn ra",
    color: "#7c3aed",
    guestCount: 0, registered: 0, checkedIn: 0,
    capacity: 2000,
    lastActivity: "Hôm qua",
    cover: "linear-gradient(135deg, #7c3aed, #4c1d95)",
    emoji: "🏛",
  },
  {
    id: "training-may", companyId: "forest", name: "Đào tạo Implant chuyên sâu",
    subtitle: "Khóa 12 — chứng nhận Quốc tế",
    startDate: "2026-09-01", endDate: "2026-09-05",
    city: "Hồ Chí Minh", venue: "FOREST Training Center",
    status: "draft", statusLabel: "Bản nháp",
    color: "#ca8a04",
    guestCount: 0, registered: 0, checkedIn: 0,
    capacity: 80,
    lastActivity: "3 ngày trước",
    cover: "linear-gradient(135deg, #ca8a04, #713f12)",
    emoji: "🦾",
  },
  {
    id: "year-end", companyId: "forest", name: "Year-end Party 2026",
    subtitle: "Tiệc tất niên & vinh danh đối tác",
    startDate: "2026-12-20", endDate: "2026-12-20",
    city: "Hồ Chí Minh", venue: "Khách sạn Reverie",
    status: "draft", statusLabel: "Bản nháp",
    color: "#db2777",
    guestCount: 0, registered: 0, checkedIn: 0,
    capacity: 800,
    lastActivity: "1 tuần trước",
    cover: "linear-gradient(135deg, #db2777, #831843)",
    emoji: "🎉",
  },
  {
    id: "gala-2026", companyId: "forest", name: "Gala đối tác chiến lược",
    subtitle: "Tri ân khách hàng VIP",
    startDate: "2026-10-15", endDate: "2026-10-15",
    city: "Hồ Chí Minh", venue: "Reverie Saigon",
    status: "upcoming", statusLabel: "Sắp diễn ra",
    color: "#0891b2",
    guestCount: 95, registered: 62, checkedIn: 0,
    capacity: 150,
    lastActivity: "5 ngày trước",
    cover: "linear-gradient(135deg, #0891b2, #164e63)",
    emoji: "🥂",
  },
];

const STATUS_COLORS = {
  active: { fg: "#16a34a", bg: "#e8f7ee", label: "Đang triển khai", labelEn: "Active" },
  upcoming: { fg: "#0891b2", bg: "#e0f2fe", label: "Sắp diễn ra", labelEn: "Upcoming" },
  ended: { fg: "#6b7280", bg: "#f3f4f6", label: "Đã kết thúc", labelEn: "Ended" },
  draft: { fg: "#ca8a04", bg: "#fef3c7", label: "Bản nháp", labelEn: "Draft" },
};

const formatDate = (d) => {
  const [y, m, day] = d.split("-");
  return `${day}/${m}/${y}`;
};
const formatDateShort = (d) => {
  const [, m, day] = d.split("-");
  return `${day}/${m}`;
};
const formatRange = (s, e) => {
  if (s === e) return formatDate(s);
  const [sy, sm] = s.split("-"); const [ey, em] = e.split("-");
  if (sy === ey && sm === em) return `${s.split("-")[2]}–${formatDate(e)}`;
  return `${formatDate(s)} → ${formatDate(e)}`;
};

Object.assign(window, { COMPANIES, EVENTS, STATUS_COLORS, formatDate, formatDateShort, formatRange });
