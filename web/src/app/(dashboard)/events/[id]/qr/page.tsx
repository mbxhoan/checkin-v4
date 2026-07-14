"use client";
import { use, useState } from "react";
import { useT } from "@/lib/context";
import { Icon } from "@/components/ui/icon";

function PseudoQR({ fg, bg, size = 120 }: { fg: string; bg: string; size?: number }) {
  const cells = 21;
  const cellSize = size / cells;
  const pattern = Array.from({ length: cells }, (_, r) =>
    Array.from({ length: cells }, (_, c) => {
      // Finder patterns (corners)
      if ((r < 7 && c < 7) || (r < 7 && c >= cells - 7) || (r >= cells - 7 && c < 7)) {
        const ir = r % 7; const ic = c % 7;
        const rr = r >= cells - 7 ? r - (cells - 7) : r;
        const cc = c >= cells - 7 ? c - (cells - 7) : c;
        if (rr === 0 || rr === 6 || cc === 0 || cc === 6 || (rr >= 2 && rr <= 4 && cc >= 2 && cc <= 4)) return true;
        return false;
      }
      return Math.random() > 0.5;
    })
  );

  return (
    <div style={{ display: "grid", gap: 1, gridTemplateColumns: `repeat(${cells}, ${cellSize}px)`, background: bg, padding: 8, borderRadius: 8 }}>
      {pattern.flatMap((row, r) => row.map((on, c) => (
        <div key={`${r}-${c}`} style={{ width: cellSize, height: cellSize, background: on ? fg : bg, borderRadius: cellSize * 0.15 }} />
      )))}
    </div>
  );
}

export default function QRPage({ params }: { params: Promise<{ id: string }> }) {
  const t = useT();
  use(params);
  const [fgColor, setFgColor] = useState("#000000");
  const [bgColor, setBgColor] = useState("#ffffff");
  const [errorCorrection, setErrorCorrection] = useState("M");
  const [logoEmbed, setLogoEmbed] = useState(false);
  const [format, setFormat] = useState(".png");
  const [sample, setSample] = useState("qr");

  return (
    <div className="detail-fade">
      <div className="section-title">
        <Icon name="qr" size={20} />
        <h2>{t("Mã QR", "QR codes")}</h2>
        <span className="small">{t("Cài đặt kiểu dáng và xuất mã QR cho khách", "Configure QR appearance and export for guests")}</span>
      </div>

      <div className="content">
        <div style={{ display: "grid", gridTemplateColumns: "1fr auto", gap: 24, alignItems: "start" }}>
          {/* Left: Controls */}
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            {/* Colors */}
            <div className="card">
              <div className="card__head"><h3>{t("Màu sắc", "Colors")}</h3></div>
              <div className="card__body">
                <div className="grid grid--2">
                  <div className="field">
                    <label className="field__label">{t("Màu điểm QR", "QR dot color")}</label>
                    <div className="color-pick" onClick={() => document.getElementById("fg-pick")?.click()}>
                      <div className="color-pick__sw" style={{ background: fgColor }} />
                      <span className="color-pick__hex">{fgColor}</span>
                    </div>
                    <input id="fg-pick" type="color" value={fgColor} onChange={(e) => setFgColor(e.target.value)} style={{ position: "absolute", opacity: 0, pointerEvents: "none" }} />
                  </div>
                  <div className="field">
                    <label className="field__label">{t("Màu nền", "Background color")}</label>
                    <div className="color-pick" onClick={() => document.getElementById("bg-pick")?.click()}>
                      <div className="color-pick__sw" style={{ background: bgColor }} />
                      <span className="color-pick__hex">{bgColor}</span>
                    </div>
                    <input id="bg-pick" type="color" value={bgColor} onChange={(e) => setBgColor(e.target.value)} style={{ position: "absolute", opacity: 0, pointerEvents: "none" }} />
                  </div>
                </div>
              </div>
            </div>

            {/* Settings */}
            <div className="card">
              <div className="card__head"><h3>{t("Cài đặt khác", "Other settings")}</h3></div>
              <div className="card__body">
                <div className="grid grid--2">
                  <div className="field">
                    <label className="field__label">{t("Mức sửa lỗi", "Error correction")}</label>
                    <select className="select" value={errorCorrection} onChange={(e) => setErrorCorrection(e.target.value)}>
                      <option value="L">L — Thấp (7%)</option>
                      <option value="M">M — Trung bình (15%)</option>
                      <option value="Q">Q — Cao (25%)</option>
                      <option value="H">H — Rất cao (30%)</option>
                    </select>
                  </div>
                  <div className="field">
                    <label className="field__label">{t("Định dạng xuất", "Export format")}</label>
                    <select className="select" value={format} onChange={(e) => setFormat(e.target.value)}>
                      <option value=".png">PNG</option>
                      <option value=".jpg">JPEG</option>
                      <option value=".svg">SVG</option>
                    </select>
                  </div>
                </div>
                <div className="opt-row" style={{ marginTop: 12 }}>
                  <div className="opt-row__main">
                    <div className="opt-row__title">{t("Nhúng logo vào QR", "Embed logo in QR")}</div>
                    <div className="opt-row__desc">{t("Đặt logo công ty vào giữa mã QR", "Place company logo in the center of QR code")}</div>
                  </div>
                  <div className="toggle" onClick={() => setLogoEmbed(!logoEmbed)}>
                    {logoEmbed && <style>{`.toggle:after { transform: translateX(16px); }`}</style>}
                  </div>
                </div>
              </div>
            </div>

            {/* Sample type */}
            <div className="card">
              <div className="card__head"><h3>{t("Dữ liệu mẫu", "Sample data")}</h3></div>
              <div className="card__body">
                <div style={{ display: "flex", gap: 8 }}>
                  {[{ id: "qr", label: "Mã QR khách" }, { id: "url", label: "URL đăng ký" }, { id: "invoice", label: "Mã hóa đơn" }].map((s) => (
                    <button
                      key={s.id}
                      className={`filter-pill${sample === s.id ? " filter-pill--active" : ""}`}
                      onClick={() => setSample(s.id)}
                    >
                      {s.label}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Export */}
            <div style={{ display: "flex", gap: 8 }}>
              <button className="qa__btn qa__btn--primary" style={{ flex: 1, justifyContent: "center" }}>
                <Icon name="download" size={14} />{t("Xuất tất cả QR", "Export all QR codes")}
              </button>
              <button className="qa__btn">
                <Icon name="copy" size={14} />{t("Copy mẫu", "Copy template")}
              </button>
            </div>
          </div>

          {/* Right: Preview */}
          <div style={{ width: 280, display: "flex", flexDirection: "column", alignItems: "center", gap: 16 }}>
            <div className="card" style={{ width: "100%", padding: 24, textAlign: "center" }}>
              <div style={{ fontSize: 12.5, color: "var(--text-muted)", marginBottom: 16 }}>{t("Xem trước", "Preview")}</div>
              <div style={{ display: "flex", justifyContent: "center" }}>
                <PseudoQR fg={fgColor} bg={bgColor} size={160} />
              </div>
              <div style={{ marginTop: 14, fontSize: 13, fontFamily: "monospace", color: "var(--text-muted)" }}>
                QR-SAMPLE-001
              </div>
            </div>
            <div className="small" style={{ textAlign: "center" }}>
              {t("Mã QR thực tế sẽ chứa dữ liệu khách", "Actual QR codes will contain guest data")}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
