"use client";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

export default function TemplatesPage() {
  const t = useT();
  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{t("Mẫu thư & QR", "Templates & QR")}</h1>
          <p className="page__sub">{t("Thư viện mẫu dùng cho nhiều sự kiện", "Template library reusable across events")}</p>
        </div>
      </div>
      <div className="card" style={{ padding: 60, textAlign: "center" }}>
        <Icon name="mail" size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px", display: "block" }} />
        <h3 style={{ margin: "0 0 6px" }}>{t("Module này đang được thiết kế", "This module is being designed")}</h3>
        <p className="small" style={{ margin: 0 }}>{t("Thư viện mẫu email, thiệp mời và QR dùng chung cho toàn công ty.", "Library of email templates, invitation cards and QR templates shared across the company.")}</p>
      </div>
    </div>
  );
}
