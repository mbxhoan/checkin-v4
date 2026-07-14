"use client";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

export default function ReportsPage() {
  const t = useT();
  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{t("Báo cáo", "Reports")}</h1>
          <p className="page__sub">{t("Phân tích & báo cáo tổng hợp toàn công ty", "Company-wide analytics and reports")}</p>
        </div>
      </div>
      <div className="card" style={{ padding: 60, textAlign: "center" }}>
        <Icon name="chart" size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px", display: "block" }} />
        <h3 style={{ margin: "0 0 6px" }}>{t("Module này đang được thiết kế", "This module is being designed")}</h3>
        <p className="small" style={{ margin: 0 }}>{t("Báo cáo tổng hợp, biểu đồ thống kê và phân tích chi tiết cho toàn bộ sự kiện trong công ty.", "Aggregate reports, charts and detailed analytics for all company events.")}</p>
      </div>
    </div>
  );
}
