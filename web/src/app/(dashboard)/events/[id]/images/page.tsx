"use client";
import { use } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

function UploadZone({ title, desc, hint }: { title: string; desc: string; hint?: string }) {
  return (
    <div className="upload">
      <div className="upload__icon"><Icon name="upload" size={32} /></div>
      <div className="upload__title">{title}</div>
      <div className="upload__sub">{desc}</div>
      {hint && <div className="upload__sub" style={{ marginTop: 4 }}>{hint}</div>}
    </div>
  );
}

export default function ImagesPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="image" size={20} />
        <h2>{t("Hình ảnh", "Images")}</h2>
        <span className="small">{t("Logo, banner và hình nền cho sự kiện", "Logo, banner and background images for the event")}</span>
      </div>
      <div className="content">
        <div className="card">
          <div className="card__head"><h3>{t("Hình ảnh thương hiệu", "Brand images")}</h3></div>
          <div className="card__body">
            <div className="grid grid--2">
              <div className="field">
                <label className="field__label">{t("Logo sự kiện", "Event logo")}</label>
                <UploadZone title={t("Tải lên logo", "Upload logo")} desc={t("Kéo thả hoặc click để chọn file", "Drag & drop or click to select")} hint="PNG, SVG • Tối đa 2MB" />
              </div>
              <div className="field">
                <label className="field__label">{t("Favicon / Icon nhỏ", "Favicon / Small icon")}</label>
                <UploadZone title={t("Tải lên favicon", "Upload favicon")} desc={t("Dùng cho tab trình duyệt", "Used for browser tab")} hint="ICO, PNG 32×32 • Tối đa 200KB" />
              </div>
            </div>
          </div>
        </div>

        <div className="card" style={{ marginTop: 16 }}>
          <div className="card__head"><h3>{t("Banner & Cover", "Banner & Cover")}</h3></div>
          <div className="card__body">
            <div className="field" style={{ marginBottom: 16 }}>
              <label className="field__label">{t("Banner email mời", "Email invitation banner")}</label>
              <UploadZone title={t("Tải lên banner email", "Upload email banner")} desc={t("Hiển thị đầu email mời và xác nhận", "Shown at top of invitation and confirmation emails")} hint="JPG, PNG • 600×200px • Tối đa 1MB" />
            </div>
            <div className="field">
              <label className="field__label">{t("Ảnh bìa trang đăng ký", "Registration page cover")} </label>
              <UploadZone title={t("Tải lên ảnh bìa", "Upload cover image")} desc={t("Hiển thị trên trang đăng ký công khai", "Displayed on the public registration page")} hint="JPG, PNG • 1920×640px • Tối đa 3MB" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
