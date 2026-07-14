"use client";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { Icon } from "@/components/ui/icon";

function GoogleLogo() {
  return (
    <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
      <path fill="#EA4335" d="M12 5.04c1.74 0 3.3.6 4.53 1.78l3.37-3.37C17.95 1.52 15.24.5 12 .5 7.42.5 3.46 3.13 1.54 6.96l3.93 3.05C6.4 7.13 8.98 5.04 12 5.04z"/>
      <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.55-.2-2.27H12v4.51h6.46c-.28 1.5-1.12 2.77-2.39 3.62l3.86 3c2.26-2.09 3.56-5.17 3.56-8.86z"/>
      <path fill="#FBBC05" d="M5.47 14.49a7.04 7.04 0 0 1-.37-2.21c0-.77.13-1.52.36-2.21L1.54 7.02A11.51 11.51 0 0 0 .5 12.28c0 1.86.45 3.62 1.04 5.26l3.93-3.05z"/>
      <path fill="#34A853" d="M12 23.5c3.24 0 5.95-1.07 7.93-2.9l-3.86-3c-1.07.72-2.45 1.15-4.07 1.15-3.02 0-5.6-2.04-6.53-4.79l-3.93 3.05C3.46 20.87 7.42 23.5 12 23.5z"/>
    </svg>
  );
}

function LinkedInLogo() {
  return (
    <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
      <path fill="#0A66C2" d="M20.45 20.45h-3.55v-5.56c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.44-2.13 2.94v5.66H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.55C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.72C24 .77 23.2 0 22.22 0z"/>
    </svg>
  );
}

export default function LoginPage() {
  const router = useRouter();
  const [tab, setTab] = useState<"login" | "register">("login");
  const [showPw, setShowPw] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.push("/events");
  };

  return (
    <div className="auth">
      {/* Hero side */}
      <aside className="auth__hero">
        <div className="auth__hero-decor" />
        <div className="auth__brand">
          <div className="auth__brand-mark">IN</div>
          <div>
            <div>IN · DELFI</div>
            <div style={{ fontSize: 10.5, color: "rgba(255,255,255,0.55)", letterSpacing: "0.08em", textTransform: "uppercase", marginTop: 1, fontWeight: 400 }}>Event Check-in Platform</div>
          </div>
        </div>

        <div className="auth__hero-content">
          <h1>
            Tổ chức sự kiện<br />
            check-in trong <span className="accent">vài giây</span>.
          </h1>
          <p>Cấu hình trang đăng ký, sinh mã QR, kiểm soát ra vào và báo cáo — tất cả từ một bảng điều khiển cho mọi sự kiện trong công ty.</p>

          <div className="auth__feats">
            {[
              { icon: "qr" as const, title: "Sinh mã QR theo nhãn hiệu", desc: "Logo, màu sắc, định dạng tùy chỉnh theo từng sự kiện" },
              { icon: "ticket" as const, title: "Check-in tức thì", desc: "Quét QR bằng camera điện thoại hoặc máy quét chuyên dụng" },
              { icon: "chart" as const, title: "Báo cáo thời gian thực", desc: "Theo dõi lượt vào, tốc độ check-in và thống kê ngay lập tức" },
            ].map((f) => (
              <div key={f.title} className="auth__feat">
                <div className="auth__feat-icon">
                  <Icon name={f.icon} size={16} />
                </div>
                <div className="auth__feat-text">
                  <b>{f.title}</b>
                  <span>{f.desc}</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="auth__footnote">© 2026 DELFI Technologies. All rights reserved.</div>
      </aside>

      {/* Form side */}
      <div className="auth__form-side">
        <div className="auth__panel-top">
          <button className="lang-switch__btn" style={{ background: "var(--auth-surface)", border: "1px solid var(--auth-border)", borderRadius: 8, padding: "6px 10px", fontSize: 13, fontFamily: "inherit", display: "inline-flex", alignItems: "center", gap: 6 }}>
            <span className="lang-switch__flag">🇻🇳</span> Tiếng Việt
          </button>
        </div>

        <div className="auth__panel">
          <div className="auth__panel-head">
            <div className="auth__tabs">
              <button
                className={`auth__tab${tab === "login" ? " auth__tab--active" : ""}`}
                onClick={() => setTab("login")}
              >Đăng nhập</button>
              <button
                className={`auth__tab${tab === "register" ? " auth__tab--active" : ""}`}
                onClick={() => setTab("register")}
              >Tạo tài khoản</button>
            </div>
            <h2>{tab === "login" ? "Chào mừng trở lại" : "Bắt đầu miễn phí"}</h2>
            <p>{tab === "login" ? "Đăng nhập vào tài khoản của bạn để tiếp tục." : "Tạo tài khoản để quản lý sự kiện của công ty bạn."}</p>
          </div>

          <div className="auth__oauth">
            <button className="auth__oauth-btn">
              <GoogleLogo />
              {tab === "login" ? "Tiếp tục với Google" : "Đăng ký với Google"}
            </button>
            <button className="auth__oauth-btn">
              <LinkedInLogo />
              {tab === "login" ? "Tiếp tục với LinkedIn" : "Đăng ký với LinkedIn"}
            </button>
          </div>

          <div className="auth__or">hoặc</div>

          <form onSubmit={handleSubmit}>
            <div className="auth__field">
              <label className="auth__label">Email công ty</label>
              <input
                className="auth__input"
                type="email"
                placeholder="you@company.vn"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>

            <div className="auth__field">
              <label className="auth__label">Mật khẩu</label>
              <div className="auth__input-wrap">
                <input
                  className="auth__input"
                  type={showPw ? "text" : "password"}
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
                <button
                  type="button"
                  className="auth__input-toggle"
                  onClick={() => setShowPw(!showPw)}
                >
                  <Icon name="eye" size={17} />
                </button>
              </div>
            </div>

            <div className="auth__row">
              <label className="auth__check">
                <input type="checkbox" defaultChecked />
                Ghi nhớ đăng nhập
              </label>
              <a href="#" style={{ color: "var(--auth-accent)", fontSize: 13 }}>Quên mật khẩu?</a>
            </div>

            <button type="submit" className="auth__submit">
              {tab === "login" ? "Đăng nhập →" : "Tạo tài khoản →"}
            </button>
          </form>

          <p className="auth__alt">
            {tab === "login" ? (
              <>Chưa có tài khoản? <a href="#" onClick={() => setTab("register")}>Tạo miễn phí</a></>
            ) : (
              <>Đã có tài khoản? <a href="#" onClick={() => setTab("login")}>Đăng nhập</a></>
            )}
          </p>

          <div className="auth__legal">
            Bằng cách đăng nhập, bạn đồng ý với <a href="#" style={{ color: "var(--auth-accent)" }}>Điều khoản dịch vụ</a> và <a href="#" style={{ color: "var(--auth-accent)" }}>Chính sách bảo mật</a> của chúng tôi.
          </div>
        </div>
      </div>
    </div>
  );
}
