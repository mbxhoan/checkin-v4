import { STATUS_COLORS } from "@/lib/types";

interface StatusBadgeProps {
  status: "active" | "upcoming" | "ended" | "draft";
  size?: "sm" | "md";
}

export function StatusBadge({ status, size = "md" }: StatusBadgeProps) {
  const s = STATUS_COLORS[status];
  return (
    <span
      className={`status-pill status-pill--${status}`}
      style={size === "sm" ? { fontSize: 11, padding: "2px 8px" } : undefined}
    >
      {s.label}
    </span>
  );
}
