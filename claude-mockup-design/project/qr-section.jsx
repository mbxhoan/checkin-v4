// qr-section.jsx — QR config with 70/30 split layout and live preview

const { useState: qrUS, useMemo: qrUM, useEffect: qrUE, useRef: qrUR } = React;

// QR code rendering helper — uses Google Chart API as fallback for speed.
// We render an SVG QR-code-like grid via a tiny generator. Keeps it offline.
// For production, swap to a real library. Here we generate a pseudo-QR matrix
// from the input string that VISUALLY reads as a QR (finder squares + data).
function pseudoQRMatrix(text, size = 33) {
  // hash-based pseudo-random matrix; not a real QR but visually convincing.
  const m = Array.from({ length: size }, () => Array(size).fill(0));
  let h = 0;
  for (let i = 0; i < text.length; i++) h = (h * 31 + text.charCodeAt(i)) | 0;
  const rnd = () => {
    h = (h * 1103515245 + 12345) & 0x7fffffff;
    return h / 0x7fffffff;
  };
  // fill
  for (let y = 0; y < size; y++)
    for (let x = 0; x < size; x++)
      m[y][x] = rnd() > 0.5 ? 1 : 0;

  // finder pattern function
  const drawFinder = (ox, oy) => {
    for (let y = 0; y < 7; y++)
      for (let x = 0; x < 7; x++) {
        const ring = (x === 0 || x === 6 || y === 0 || y === 6);
        const core = (x >= 2 && x <= 4 && y >= 2 && y <= 4);
        m[oy + y][ox + x] = (ring || core) ? 1 : 0;
      }
    // quiet zone around (set to 0)
    for (let y = -1; y < 8; y++)
      for (let x = -1; x < 8; x++) {
        if (x === -1 || x === 7 || y === -1 || y === 7) {
          const yy = oy + y, xx = ox + x;
          if (yy >= 0 && yy < size && xx >= 0 && xx < size) m[yy][xx] = 0;
        }
      }
  };
  drawFinder(0, 0);
  drawFinder(size - 7, 0);
  drawFinder(0, size - 7);

  // alignment-ish square center-bottom-right
  const ax = size - 9, ay = size - 9;
  for (let y = 0; y < 5; y++)
    for (let x = 0; x < 5; x++) {
      const ring = (x === 0 || x === 4 || y === 0 || y === 4);
      const core = (x === 2 && y === 2);
      m[ay + y][ax + x] = (ring || core) ? 1 : 0;
    }

  // timing patterns
  for (let i = 8; i < size - 8; i++) {
    m[6][i] = i % 2 === 0 ? 1 : 0;
    m[i][6] = i % 2 === 0 ? 1 : 0;
  }
  return m;
}

const QRPreview = ({ s, sample = "GUEST-0001-VIDEC-2026", showLogo, logoSrc, dotShape = "square" }) => {
  const size = 33;
  const matrix = qrUM(() => pseudoQRMatrix(sample, size), [sample]);
  const cell = 8;
  const px = size * cell;
  // optional center hole when logo is on (24% of QR side)
  const logoBox = Math.round(px * (parseInt((s.logoSize || "30%"), 10) / 100));
  const logoX = (px - logoBox) / 2;
  const logoY = (px - logoBox) / 2;

  return (
    <svg viewBox={`0 0 ${px} ${px}`} xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" style={{ display: "block" }}>
      <rect width={px} height={px} fill={s.bgColor} />
      {matrix.map((row, y) =>
        row.map((v, x) => {
          if (!v) return null;
          // If logo is on, suppress modules where logo will sit (with a small margin)
          if (showLogo) {
            const cx = x * cell + cell / 2;
            const cy = y * cell + cell / 2;
            if (cx > logoX - 4 && cx < logoX + logoBox + 4 && cy > logoY - 4 && cy < logoY + logoBox + 4) return null;
          }
          if (dotShape === "round") {
            return <circle key={`${x}-${y}`} cx={x * cell + cell / 2} cy={y * cell + cell / 2} r={cell / 2 - 0.2} fill={s.fgColor} />;
          }
          if (dotShape === "soft") {
            return <rect key={`${x}-${y}`} x={x * cell + 0.5} y={y * cell + 0.5} width={cell - 1} height={cell - 1} rx={cell * 0.25} fill={s.fgColor} />;
          }
          return <rect key={`${x}-${y}`} x={x * cell} y={y * cell} width={cell} height={cell} fill={s.fgColor} />;
        }),
      )}
      {showLogo && (
        <g>
          <rect x={logoX - 4} y={logoY - 4} width={logoBox + 8} height={logoBox + 8} rx={10} fill={s.bgColor} />
          {logoSrc ? (
            <image href={logoSrc} x={logoX} y={logoY} width={logoBox} height={logoBox} preserveAspectRatio="xMidYMid meet" />
          ) : (
            <g>
              <rect x={logoX} y={logoY} width={logoBox} height={logoBox} rx={8} fill="url(#in-grad)" />
              <text x={logoX + logoBox / 2} y={logoY + logoBox / 2 + 7} textAnchor="middle" fontFamily="Be Vietnam Pro, sans-serif" fontWeight="800" fontSize={logoBox * 0.45} fill="#fff">IN</text>
            </g>
          )}
          <defs>
            <linearGradient id="in-grad" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stopColor="#ff7e3c" />
              <stop offset="55%" stopColor="#ff3b6b" />
              <stop offset="100%" stopColor="#a955f7" />
            </linearGradient>
          </defs>
        </g>
      )}
    </svg>
  );
};

// =============== Main section ===============
const SectionQR = ({ s, set }) => {
  const upd = (k, v) => set({ ...s, [k]: v });
  const [showLogo, setShowLogo] = qrUS(s.logoEmbed);
  qrUE(() => setShowLogo(s.logoEmbed), [s.logoEmbed]);

  // Sample data shown in preview — let user toggle which type
  const [sampleKind, setSampleKind] = qrUS("guest");
  const SAMPLE = {
    guest: "GUEST-0001-VIDEC-2026",
    invoice: "INV-2026-08-A-0247",
    custom: s.customSample || "https://checkin.delfi.vn/r/videc-2026",
  };

  const dotShape = s.dotShape || "square";
  const ecLabel = {
    L: "Thấp (7%)", M: "Trung bình (15%)", Q: "Cao (25%)", H: "Tối đa (30%)",
  }[s.errorCorrection || "M"];

  return (
    <div className="qr-builder">
      {/* LEFT — configuration (70%) */}
      <div className="qr-builder__config">
        <Card title="Hình thức mã QR" sub="Tùy biến màu sắc, hình dạng & độ nét" icon="image">
          <div className="grid grid--2">
            <Field label="Màu mã QR" tip="Tối ưu là màu tối trên nền sáng. Tránh dùng màu nhạt vì máy quét khó đọc.">
              <ColorPick value={s.fgColor} onChange={(v) => upd("fgColor", v)} />
            </Field>
            <Field label="Màu nền">
              <ColorPick value={s.bgColor} onChange={(v) => upd("bgColor", v)} />
            </Field>
            <Field label="Hình dạng module" tip="Khối vuông cho khả năng quét tốt nhất. Bo tròn cho cảm giác mềm mại hơn.">
              <select className="select" value={dotShape} onChange={(e) => upd("dotShape", e.target.value)}>
                <option value="square">Vuông (khuyến nghị)</option>
                <option value="soft">Bo nhẹ</option>
                <option value="round">Tròn</option>
              </select>
            </Field>
            <Field label="Định dạng tệp xuất" tip="PNG cho chất lượng tốt, JPG nhẹ hơn, SVG dùng cho in ấn vector.">
              <select className="select" value={s.format} onChange={(e) => upd("format", e.target.value)}>
                <option value=".png">PNG (khuyến nghị)</option>
                <option value=".jpg">JPG (dung lượng nhỏ)</option>
                <option value=".svg">SVG (cho in ấn)</option>
              </select>
            </Field>
            <Field label="Độ bền (Error correction)" tip="Càng cao, mã QR càng quét được khi bị che/xước. Nếu nhúng logo, đặt từ Cao trở lên.">
              <select className="select" value={s.errorCorrection} onChange={(e) => upd("errorCorrection", e.target.value)}>
                <option value="L">L — Thấp, mã gọn (7%)</option>
                <option value="M">M — Trung bình (15%)</option>
                <option value="Q">Q — Cao, có thể che 1 phần (25%)</option>
                <option value="H">H — Tối đa, cho thẻ trầy (30%)</option>
              </select>
            </Field>
            <Field label="Lề trắng (quiet zone)" tip="Khoảng trắng quanh mã giúp máy quét nhận diện rìa. Đừng cắt sát quá.">
              <select className="select" value={s.quietZone || "4"} onChange={(e) => upd("quietZone", e.target.value)}>
                <option value="0">Không lề</option>
                <option value="2">Mỏng — 2 module</option>
                <option value="4">Khuyến nghị — 4 module</option>
                <option value="6">Dày — 6 module</option>
              </select>
            </Field>
          </div>
        </Card>

        <Card title="Logo trung tâm" sub="Đặt logo / huy hiệu vào giữa mã QR" icon="sparkles">
          <OptRow
            title="Nhúng logo vào giữa mã QR"
            desc="Logo sẽ che một phần mã — hệ thống tự bù trừ bằng độ bền."
            tip="Khi bật, hãy đặt 'Độ bền mã QR' từ Cao trở lên để đảm bảo máy quét vẫn đọc được."
            on={s.logoEmbed}
            onChange={(v) => { upd("logoEmbed", v); setShowLogo(v); }}
          />
          {s.logoEmbed && (
            <div className="grid grid--2" style={{ marginTop: 12 }}>
              <Field label="Kích thước logo (so với mã QR)" tip="20–30% là cân bằng tốt nhất. Quá lớn sẽ làm mã khó quét.">
                <select className="select" value={s.logoSize} onChange={(e) => upd("logoSize", e.target.value)}>
                  <option>20%</option><option>25%</option><option>30%</option><option>35%</option>
                </select>
              </Field>
              <Field label="Nguồn logo" tip="'Logo công ty' lấy ảnh đã tải lên ở tab Hình ảnh. 'Logo IN' dùng nhãn hiệu app check-in.">
                <select className="select" value={s.logoSource || "company"} onChange={(e) => upd("logoSource", e.target.value)}>
                  <option value="company">Logo công ty (từ tab Hình ảnh)</option>
                  <option value="in">Logo IN (app check-in)</option>
                  <option value="custom">Tải lên logo riêng…</option>
                </select>
              </Field>
            </div>
          )}
        </Card>

        <Card title="Nội dung mã QR" sub="Quy tắc sinh mã cho từng khách" icon="qr">
          <div className="grid grid--2">
            <Field label="Loại nội dung" tip="Mã định danh để check-in nội bộ, hoặc URL trỏ về trang đăng ký / xác thực.">
              <select className="select" value={s.contentType || "id"} onChange={(e) => upd("contentType", e.target.value)}>
                <option value="id">Mã định danh (GUEST-xxxx)</option>
                <option value="url">URL có chữ ký (link xác thực)</option>
                <option value="vcard">vCard (danh thiếp)</option>
              </select>
            </Field>
            <Field label="Tiền tố mã" hint="VD: VIDEC-2026 → mã sẽ là VIDEC-2026-0001">
              <input
                className="input"
                value={s.prefix || "VIDEC-2026"}
                onChange={(e) => upd("prefix", e.target.value)}
                style={{ fontFamily: "'SF Mono', Menlo, monospace", fontSize: 13 }}
              />
            </Field>
          </div>
          <OptRow
            title="Tạo mã QR riêng cho từng loại khách"
            desc="Sinh nhiều bộ mã khác nhau theo trường thông tin (VD: theo loại vé)"
            tip="Nâng cao. Khi bật, hệ thống sinh thư mục mã QR khác nhau theo một trường lựa chọn."
            on={s.qrPerType}
            onChange={(v) => upd("qrPerType", v)}
          />
        </Card>
      </div>

      {/* RIGHT — live preview (30%) */}
      <aside className="qr-builder__preview">
        <div className="qr-preview-card">
          <div className="qr-preview-card__head">
            <div>
              <div className="qr-preview-card__title">Xem trước mã QR</div>
              <div className="qr-preview-card__sub">Cập nhật ngay theo cấu hình bên trái</div>
            </div>
            <span className="qr-preview-card__chip">Live</span>
          </div>

          <div className="qr-preview-card__sample">
            <button
              className={"qr-sample-tab" + (sampleKind === "guest" ? " is-active" : "")}
              onClick={() => setSampleKind("guest")}
            >Mã khách</button>
            <button
              className={"qr-sample-tab" + (sampleKind === "invoice" ? " is-active" : "")}
              onClick={() => setSampleKind("invoice")}
            >Mã vé</button>
            <button
              className={"qr-sample-tab" + (sampleKind === "custom" ? " is-active" : "")}
              onClick={() => setSampleKind("custom")}
            >URL</button>
          </div>

          <div
            className="qr-preview-card__canvas"
            style={{ background: s.bgColor }}
          >
            <QRPreview
              s={s}
              sample={SAMPLE[sampleKind]}
              showLogo={showLogo}
              logoSrc={
                s.logoSource === "in"
                  ? "assets/in-logo.png"
                  : s.logoSource === "company"
                  ? null
                  : null
              }
              dotShape={dotShape}
            />
          </div>

          <div className="qr-preview-card__meta">
            <div className="qr-preview-card__meta-row">
              <span>Nội dung</span>
              <code>{SAMPLE[sampleKind]}</code>
            </div>
            <div className="qr-preview-card__meta-row">
              <span>Định dạng</span>
              <b>{(s.format || ".png").toUpperCase().replace(".", "")}</b>
            </div>
            <div className="qr-preview-card__meta-row">
              <span>Độ bền</span>
              <b>{ecLabel}</b>
            </div>
            <div className="qr-preview-card__meta-row">
              <span>Logo trung tâm</span>
              <b>{s.logoEmbed ? `Có · ${s.logoSize || "30%"}` : "Tắt"}</b>
            </div>
          </div>

          <div className="qr-preview-card__actions">
            <button className="qa__btn"><Icon name="copy" />Sao chép mã</button>
            <button className="qa__btn qa__btn--primary"><Icon name="upload" style={{ transform: "rotate(180deg)" }} />Tải xuống</button>
          </div>

          <div className="qr-preview-card__tip">
            <Icon name="info" size={14} />
            <span>Đây là mã mẫu. Mã thực tế cho từng khách sẽ được sinh khi bạn lưu cấu hình.</span>
          </div>
        </div>
      </aside>
    </div>
  );
};

Object.assign(window, { SectionQR, QRPreview });
