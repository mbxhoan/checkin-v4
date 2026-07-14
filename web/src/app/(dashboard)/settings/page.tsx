"use client";
import { useT } from "@/lib/context";
import { useApp } from "@/lib/context";
import { Icon } from "@/components/ui/icon";
import { Card } from "@/components/ui/card";

export default function SettingsPage() {
  const t = useT();
  const { company } = useApp();

  return (
    <div className="page">
      <div className="page__head">
        <div>
          <h1 className="page__title">{t("Cài đặt công ty", "Company settings")}</h1>
          <p className="page__sub">{t("Tài khoản, nhân viên, thanh toán", "Account, members, billing")}</p>
        </div>
      </div>

      <Card title={t("Thông tin công ty", "Company information")} sub={t("Thông tin cơ bản về tổ chức của bạn", "Basic information about your organization")} icon="settings">
        <div className="grid grid--2">
          <div className="field">
            <label className="field__label">{t("Tên công ty", "Company name")}</label>
            <input className="input" defaultValue={company.name} />
          </div>
          <div className="field">
            <label className="field__label">{t("Mã công ty", "Company code")}</label>
            <input className="input" value={company.code} readOnly />
          </div>
          <div className="field">
            <label className="field__label">Email</label>
            <input className="input" type="email" defaultValue="contact@forestmedical.vn" />
          </div>
          <div className="field">
            <label className="field__label">{t("Số điện thoại", "Phone")}</label>
            <input className="input" type="tel" defaultValue="+84 28 1234 5678" />
          </div>
        </div>
        <div style={{ marginTop: 16 }}>
          <button className="qa__btn qa__btn--primary">{t("Lưu thay đổi", "Save changes")}</button>
        </div>
      </Card>

      <Card title={t("Gói dịch vụ", "Subscription plan")} sub={t("Thông tin gói và giới hạn sử dụng", "Plan information and usage limits")} icon="chart">
        <div style={{ display: "flex", gap: 20, alignItems: "center", padding: "8px 0" }}>
          <div style={{ width: 48, height: 48, borderRadius: 12, background: "var(--primary-soft)", display: "grid", placeItems: "center" }}>
            <Icon name="ticket" size={24} style={{ color: "var(--primary)" }} />
          </div>
          <div>
            <div style={{ fontWeight: 600, fontSize: 16 }}>{company.plan}</div>
            <div className="small">{t("Hết hạn:", "Expires:")} 31/12/2026</div>
          </div>
          <button className="qa__btn" style={{ marginLeft: "auto" }}>{t("Nâng cấp gói", "Upgrade plan")}</button>
        </div>
        <div className="divider" />
        <div className="grid grid--3">
          {[
            { label: t("Số sự kiện", "Events"), used: 8, max: 50 },
            { label: t("Số người dùng", "Users"), used: 12, max: 100 },
            { label: t("Lưu trữ", "Storage"), used: 2.4, max: 20, unit: "GB" },
          ].map((item) => (
            <div key={item.label}>
              <div style={{ fontSize: 12.5, color: "var(--text-muted)", marginBottom: 4 }}>{item.label}</div>
              <div style={{ fontSize: 16, fontWeight: 600 }}>{item.used}{item.unit} <span className="small">/ {item.max}{item.unit}</span></div>
              <div style={{ height: 4, background: "var(--surface-2)", borderRadius: 999, marginTop: 6, overflow: "hidden" }}>
                <div style={{ height: "100%", width: `${(item.used / item.max) * 100}%`, background: "var(--primary)", borderRadius: 999 }} />
              </div>
            </div>
          ))}
        </div>
      </Card>
    </div>
  );
}
