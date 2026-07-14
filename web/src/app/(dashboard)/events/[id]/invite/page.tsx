"use client";
import { use } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

export default function InvitePage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);
  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="mail" size={20} />
        <h2>{t("Thiệp & email", "Invites & email")}</h2>
      </div>
      <div className="content">
        <div className="card" style={{ padding: 60, textAlign: "center" }}>
          <Icon name="mail" size={48} style={{ color: "var(--text-faint)", margin: "0 auto 12px", display: "block" }} />
          <h3 style={{ margin: "0 0 6px" }}>{t("Module này đang được thiết kế", "This module is being designed")}</h3>
          <p className="small" style={{ margin: 0 }}>{t("Tạo và gửi thiệp mời, email xác nhận và nhắc nhở tham dự.", "Create and send invitation cards, confirmation emails and attendance reminders.")}</p>
        </div>
      </div>
    </div>
  );
}
