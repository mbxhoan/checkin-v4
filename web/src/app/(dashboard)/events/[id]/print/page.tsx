"use client";
import { use } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

export default function PrintPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);
  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="print" size={20} />
        <h2>{t("Mẫu in thẻ", "Badge templates")}</h2>
      </div>
      <div className="content">
        <div className="card" style={{ padding: 60, textAlign: "center" }}>
          <Icon name="print" size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px", display: "block" }} />
          <h3 style={{ margin: "0 0 6px" }}>{t("Module này đang được thiết kế", "This module is being designed")}</h3>
          <p className="small" style={{ margin: 0 }}>{t("Thiết kế mẫu thẻ đeo, nhãn tên và tài liệu in cho khách tham dự.", "Design badge, name label and printable document templates for attendees.")}</p>
        </div>
      </div>
    </div>
  );
}
