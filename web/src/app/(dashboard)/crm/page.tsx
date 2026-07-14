"use client";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

export default function CRMPage() {
  const t = useT();
  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{t("Khách hàng", "Customers")}</h1>
          <p className="page__sub">{t("Cơ sở dữ liệu khách hàng dùng chung trong công ty", "Shared customer database across the company")}</p>
        </div>
      </div>
      <div className="card" style={{ padding: 60, textAlign: "center" }}>
        <Icon name="users" size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px", display: "block" }} />
        <h3 style={{ margin: "0 0 6px" }}>{t("Module CRM đang được thiết kế", "CRM module is being designed")}</h3>
        <p className="small" style={{ margin: 0 }}>{t("Đây là khu vực toàn công ty — dữ liệu khách hàng dùng chung cho mọi sự kiện.", "This is a company-wide area — customer data shared across all events.")}</p>
      </div>
    </div>
  );
}
