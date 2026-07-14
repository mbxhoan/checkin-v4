"use client";
import { use, useState } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";
import { OptRow } from "@/components/ui/opt-row";

interface CheckinSettings {
  showCount: boolean; printLabel: boolean; camera: boolean; manual: boolean;
  dupQR: boolean; dupDay: boolean; dupUser: boolean; sound: boolean;
}

function DeviceSection({ title, icon, settings, onChange }: {
  title: string;
  icon: "camera" | "phone";
  settings: CheckinSettings;
  onChange: (s: Partial<CheckinSettings>) => void;
}) {
  const t = useT();
  return (
    <div className="card">
      <div className="card__head">
        <Icon name={icon} size={18} style={{ color: "var(--primary)" }} />
        <h3>{title}</h3>
      </div>
      <div className="card__body">
        <div className="setgrp">
          <div className="setgrp__head">
            <Icon name="eye" size={16} />
            {t("Hiển thị", "Display")}
          </div>
          <OptRow title={t("Hiện số lượt check-in", "Show check-in count")} desc={t("Hiển thị tổng số khách đã vào hội trường", "Show total guests in venue")} on={settings.showCount} onChange={(v) => onChange({ showCount: v })} />
          <OptRow title={t("In nhãn ngay sau check-in", "Print label after check-in")} desc={t("Tự động in badge/nhãn tên sau khi xác nhận", "Auto-print badge/name label after confirmation")} on={settings.printLabel} onChange={(v) => onChange({ printLabel: v })} />
        </div>

        <div className="setgrp" style={{ marginTop: 16 }}>
          <div className="setgrp__head">
            <Icon name="camera" size={16} />
            {t("Phương thức quét", "Scan method")}
          </div>
          <OptRow title={t("Quét camera", "Camera scan")} desc={t("Sử dụng camera thiết bị để quét mã QR", "Use device camera to scan QR codes")} on={settings.camera} onChange={(v) => onChange({ camera: v })} />
          <OptRow title={t("Nhập thủ công", "Manual entry")} desc={t("Cho phép nhập mã QR bằng bàn phím", "Allow typing QR code manually")} on={settings.manual} onChange={(v) => onChange({ manual: v })} />
        </div>

        <div className="setgrp" style={{ marginTop: 16 }}>
          <div className="setgrp__head">
            <Icon name="alert" size={16} />
            {t("Kiểm tra trùng lặp", "Duplicate detection")}
          </div>
          <OptRow title={t("Cảnh báo QR trùng trong phiên", "Warn duplicate QR in session")} desc={t("Báo động khi cùng mã QR được quét lần 2", "Alert when same QR is scanned again")} on={settings.dupQR} onChange={(v) => onChange({ dupQR: v })} />
          <OptRow title={t("Cảnh báo QR đã check-in trong ngày", "Warn if already checked in today")} desc={t("Báo động nếu khách đã check-in hôm nay", "Alert if guest already checked in today")} on={settings.dupDay} onChange={(v) => onChange({ dupDay: v })} />
          <OptRow title={t("Kiểm tra chéo giữa các thiết bị", "Cross-device check")} desc={t("Đồng bộ trạng thái check-in giữa các quầy", "Sync check-in status across all stations")} on={settings.dupUser} onChange={(v) => onChange({ dupUser: v })} />
        </div>

        <div className="setgrp" style={{ marginTop: 16 }}>
          <div className="setgrp__head">
            <Icon name="bell" size={16} />
            {t("Âm thanh", "Sound")}
          </div>
          <OptRow title={t("Bật âm thanh xác nhận", "Enable confirmation sound")} desc={t("Phát âm thanh khi check-in thành công hoặc lỗi", "Play sound on successful or failed check-in")} on={settings.sound} onChange={(v) => onChange({ sound: v })} />
        </div>
      </div>
    </div>
  );
}

export default function CheckinPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);

  const [desktop, setDesktop] = useState<CheckinSettings>({
    showCount: true, printLabel: false, camera: true, manual: true,
    dupQR: true, dupDay: false, dupUser: false, sound: true,
  });
  const [mobile, setMobile] = useState<CheckinSettings>({
    showCount: true, printLabel: false, camera: true, manual: true,
    dupQR: true, dupDay: false, dupUser: false, sound: false,
  });

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="ticket" size={20} />
        <h2>{t("Check-in", "Check-in")}</h2>
        <span className="small">{t("Cài đặt thiết bị check-in cho từng quầy", "Configure check-in device settings per station")}</span>
      </div>
      <div className="content">
        <div className="grid grid--2">
          <DeviceSection
            title={t("Màn hình desktop / Máy tính bảng", "Desktop / Tablet")}
            icon="camera"
            settings={desktop}
            onChange={(patch) => setDesktop((s) => ({ ...s, ...patch }))}
          />
          <DeviceSection
            title={t("Điện thoại di động", "Mobile phone")}
            icon="phone"
            settings={mobile}
            onChange={(patch) => setMobile((s) => ({ ...s, ...patch }))}
          />
        </div>
      </div>
    </div>
  );
}
