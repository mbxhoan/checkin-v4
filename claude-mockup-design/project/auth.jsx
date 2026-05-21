// auth.jsx — login & register UI with OAuth (Google, Apple, LinkedIn)

const { useState: aUS } = React;

// ---------- Brand-marked logos for OAuth ----------
const GoogleLogo = () => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill="#EA4335" d="M12 5.04c1.74 0 3.3.6 4.53 1.78l3.37-3.37C17.95 1.52 15.24.5 12 .5 7.42.5 3.46 3.13 1.54 6.96l3.93 3.05C6.4 7.13 8.98 5.04 12 5.04z" />
    <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.55-.2-2.27H12v4.51h6.46c-.28 1.5-1.12 2.77-2.39 3.62l3.86 3c2.26-2.09 3.56-5.17 3.56-8.86z" />
    <path fill="#FBBC05" d="M5.47 14.49a7.04 7.04 0 0 1-.37-2.21c0-.77.13-1.52.36-2.21L1.54 7.02A11.51 11.51 0 0 0 .5 12.28c0 1.86.45 3.62 1.04 5.26l3.93-3.05z" />
    <path fill="#34A853" d="M12 23.5c3.24 0 5.95-1.07 7.93-2.9l-3.86-3c-1.07.72-2.45 1.15-4.07 1.15-3.02 0-5.6-2.04-6.53-4.79l-3.93 3.05C3.46 20.87 7.42 23.5 12 23.5z" />
  </svg>
);

const AppleLogo = () => (
  <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M17.05 12.5c0-2.6 2.13-3.85 2.22-3.9-1.21-1.78-3.1-2.02-3.78-2.05-1.61-.17-3.14.95-3.96.95-.82 0-2.08-.93-3.42-.9C6.39 6.62 4.84 7.6 4 9.2c-1.84 3.2-.47 7.94 1.32 10.55.88 1.28 1.92 2.7 3.3 2.65 1.32-.05 1.82-.85 3.42-.85 1.6 0 2.04.85 3.42.83 1.42-.03 2.32-1.28 3.18-2.57.74-1.1 1.05-2.18 1.07-2.24-.02-.01-2.04-.78-2.06-3.07zm-2.6-5.66c.7-.86 1.18-2.05 1.05-3.24-1.02.04-2.25.68-2.97 1.54-.65.76-1.22 1.97-1.07 3.14 1.14.09 2.3-.58 2.99-1.44z" />
  </svg>
);

const LinkedInLogo = () => (
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path fill="#0A66C2" d="M20.45 20.45h-3.55v-5.56c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.13 1.44-2.13 2.94v5.66H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.55C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.72C24 .77 23.2 0 22.22 0z" />
  </svg>
);

const EyeOpen = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" />
  </svg>
);
const EyeOff = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
    <path d="M3 3l18 18M10.6 6.1A10.5 10.5 0 0 1 12 6c6.5 0 10 6 10 6a17.6 17.6 0 0 1-3.1 4M6.6 6.6C3.8 8.3 2 12 2 12s3.5 6 10 6c1.7 0 3.2-.4 4.5-1M9.9 9.9a3 3 0 0 0 4.2 4.2" />
  </svg>
);

// ---------- Brand mark ----------
const BrandMark = () => (
  <div className="auth__brand">
    <div className="auth__brand-mark">
      <img src="assets/in-logo.png" alt="IN" />
    </div>
    <div>
      <div style={{ fontSize: 14, fontWeight: 700, letterSpacing: "0.04em" }}>IN · DELFI</div>
      <div style={{ fontSize: 10.5, color: "rgba(255,255,255,0.55)", letterSpacing: "0.08em", textTransform: "uppercase", marginTop: 1 }}>Event Check-in Platform</div>
    </div>
  </div>
);

// ---------- Hero side ----------
const AuthHero = () => {
  const t = useT();
  return (
  <aside className="auth__hero">
    <div className="auth__hero-decor" />
    <BrandMark />

    <div className="auth__hero-content">
      <h1>
        {t("Tổ chức sự kiện", "Run events &")}<br />
        {t("check-in trong", "check-in within")} <span className="accent">{t("vài giây", "seconds")}</span>.
      </h1>
      <p>
        {t(
          "Cấu hình trang đăng ký, sinh mã QR, kiểm soát ra vào và báo cáo — tất cả từ một bảng điều khiển cho mọi sự kiện trong công ty.",
          "Configure registration pages, generate QR codes, control entry, and report — all from one dashboard for every event in your company.",
        )}
      </p>

      <div className="auth__feats">
        <div className="auth__feat">
          <div className="auth__feat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
              <rect x="3.5" y="3.5" width="6" height="6" /><rect x="14.5" y="3.5" width="6" height="6" />
              <rect x="3.5" y="14.5" width="6" height="6" /><path d="M14.5 14.5h2v2M18.5 14.5v2M16.5 18.5h4M14.5 20.5h2" />
            </svg>
          </div>
          <div className="auth__feat-text">
            <b>{t("Sinh mã QR theo nhãn hiệu", "Branded QR codes")}</b>
            <span>{t("Tùy biến màu, logo, định dạng — xuất hàng loạt chỉ một cú nhấp", "Customize color, logo, format — bulk export in one click")}</span>
          </div>
        </div>
        <div className="auth__feat">
          <div className="auth__feat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
              <rect x="7" y="3" width="10" height="18" rx="2" /><path d="M11 18h2" />
            </svg>
          </div>
          <div className="auth__feat-text">
            <b>{t("App IN — check-in tại quầy", "IN app — check-in at the door")}</b>
            <span>{t("Quét mã trên điện thoại / tablet, đồng bộ thời gian thực với hệ thống", "Scan on phone/tablet, real-time sync with the system")}</span>
          </div>
        </div>
        <div className="auth__feat">
          <div className="auth__feat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
              <path d="M3 21h18" /><path d="M7 17v-6M12 17V7M17 17v-9" />
            </svg>
          </div>
          <div className="auth__feat-text">
            <b>{t("Báo cáo theo thời gian thực", "Real-time reporting")}</b>
            <span>{t("Theo dõi tỷ lệ check-in, nguồn đăng ký, lưu lượng theo giờ", "Track check-in rate, registration source, hourly traffic")}</span>
          </div>
        </div>
      </div>
    </div>

    <div className="auth__footnote">© 2026 Delfi Technologies · Vietnam</div>
  </aside>
  );
};

// ---------- Form side ----------
function AuthPanel() {
  const t = useT();
  const [mode, setMode] = aUS("login"); // 'login' | 'register'
  const [showPw, setShowPw] = aUS(false);
  const [showPw2, setShowPw2] = aUS(false);
  const [email, setEmail] = aUS("");
  const [pw, setPw] = aUS("");
  const [pw2, setPw2] = aUS("");
  const [name, setName] = aUS("");
  const [company, setCompany] = aUS("");
  const [remember, setRemember] = aUS(true);
  const [agree, setAgree] = aUS(false);

  const onOAuth = (provider) => {
    // Mock — go straight to the dashboard
    window.location.href = "index.html";
  };
  const onSubmit = (e) => {
    e.preventDefault();
    if (mode === "register" && !agree) {
      alert(t("Bạn cần đồng ý với Điều khoản sử dụng để tiếp tục.", "You need to agree to the Terms of Service to continue."));
      return;
    }
    window.location.href = "index.html";
  };

  return (
    <section className="auth__form-side">
      <div className="auth__panel-top">
        <LangSwitcher />
      </div>
      <div className="auth__panel">
        <div className="auth__tabs" role="tablist">
          <button
            role="tab"
            className={"auth__tab" + (mode === "login" ? " auth__tab--active" : "")}
            onClick={() => setMode("login")}
          >{t("Đăng nhập", "Sign in")}</button>
          <button
            role="tab"
            className={"auth__tab" + (mode === "register" ? " auth__tab--active" : "")}
            onClick={() => setMode("register")}
          >{t("Đăng ký", "Sign up")}</button>
        </div>

        <div className="auth__panel-head">
          {mode === "login" ? (
            <>
              <h2>{t("Chào mừng trở lại", "Welcome back")}</h2>
              <p>{t("Đăng nhập để quản lý sự kiện của công ty bạn.", "Sign in to manage your company's events.")}</p>
            </>
          ) : (
            <>
              <h2>{t("Tạo tài khoản miễn phí", "Create a free account")}</h2>
              <p>{t("Dùng thử 14 ngày — không cần thẻ tín dụng.", "14-day free trial — no credit card required.")}</p>
            </>
          )}
        </div>

        <div className="auth__oauth">
          <button type="button" className="auth__oauth-btn" onClick={() => onOAuth("google")}>
            <GoogleLogo />
            <span>{mode === "login" ? t("Tiếp tục với Google", "Continue with Google") : t("Đăng ký với Google", "Sign up with Google")}</span>
          </button>
          <button type="button" className="auth__oauth-btn auth__oauth-btn--apple" onClick={() => onOAuth("apple")}>
            <AppleLogo />
            <span>{mode === "login" ? t("Tiếp tục với Apple", "Continue with Apple") : t("Đăng ký với Apple", "Sign up with Apple")}</span>
          </button>
          <button type="button" className="auth__oauth-btn" onClick={() => onOAuth("linkedin")}>
            <LinkedInLogo />
            <span>{mode === "login" ? t("Tiếp tục với LinkedIn", "Continue with LinkedIn") : t("Đăng ký với LinkedIn", "Sign up with LinkedIn")}</span>
          </button>
        </div>

        <div className="auth__or">{t("Hoặc dùng email", "Or use email")}</div>

        <form onSubmit={onSubmit}>
          {mode === "register" && (
            <>
              <div className="auth__field">
                <label className="auth__label">{t("Họ và tên", "Full name")}</label>
                <input
                  className="auth__input"
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder={t("Nguyễn Văn A", "Jane Doe")}
                  autoComplete="name"
                />
              </div>
              <div className="auth__field">
                <label className="auth__label">{t("Tên công ty", "Company name")}</label>
                <input
                  className="auth__input"
                  type="text"
                  required
                  value={company}
                  onChange={(e) => setCompany(e.target.value)}
                  placeholder={t("VD: FOREST Medical Group", "e.g. FOREST Medical Group")}
                  autoComplete="organization"
                />
              </div>
            </>
          )}

          <div className="auth__field">
            <label className="auth__label">{t("Email công việc", "Work email")}</label>
            <input
              className="auth__input"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder={t("ban@congty.vn", "you@company.com")}
              autoComplete="email"
            />
          </div>

          <div className="auth__field">
            <label className="auth__label">
              {t("Mật khẩu", "Password")}
              {mode === "login" && (
                <a href="#forgot" style={{ float: "right", fontWeight: 500, fontSize: 12.5 }}>{t("Quên mật khẩu?", "Forgot password?")}</a>
              )}
            </label>
            <div className="auth__input-wrap">
              <input
                className="auth__input"
                type={showPw ? "text" : "password"}
                required
                value={pw}
                onChange={(e) => setPw(e.target.value)}
                placeholder={mode === "register" ? t("Ít nhất 8 ký tự", "At least 8 characters") : t("Mật khẩu của bạn", "Your password")}
                autoComplete={mode === "register" ? "new-password" : "current-password"}
                minLength={mode === "register" ? 8 : undefined}
              />
              <button type="button" className="auth__input-toggle" onClick={() => setShowPw(!showPw)} aria-label={t("Hiện/ẩn mật khẩu", "Show/hide password")}>
                {showPw ? <EyeOff /> : <EyeOpen />}
              </button>
            </div>
          </div>

          {mode === "register" && (
            <div className="auth__field">
              <label className="auth__label">{t("Xác nhận mật khẩu", "Confirm password")}</label>
              <div className="auth__input-wrap">
                <input
                  className="auth__input"
                  type={showPw2 ? "text" : "password"}
                  required
                  value={pw2}
                  onChange={(e) => setPw2(e.target.value)}
                  placeholder={t("Nhập lại mật khẩu", "Re-enter password")}
                  autoComplete="new-password"
                />
                <button type="button" className="auth__input-toggle" onClick={() => setShowPw2(!showPw2)} aria-label={t("Hiện/ẩn mật khẩu", "Show/hide password")}>
                  {showPw2 ? <EyeOff /> : <EyeOpen />}
                </button>
              </div>
            </div>
          )}

          <div className="auth__row">
            {mode === "login" ? (
              <label className="auth__check">
                <input type="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} />
                {t("Ghi nhớ đăng nhập", "Remember me")}
              </label>
            ) : (
              <label className="auth__check">
                <input type="checkbox" checked={agree} onChange={(e) => setAgree(e.target.checked)} />
                {t("Tôi đồng ý với", "I agree to the")} <a href="#terms">{t("Điều khoản", "Terms")}</a> {t("và", "and")} <a href="#privacy">{t("Bảo mật", "Privacy")}</a>
              </label>
            )}
          </div>

          <button type="submit" className="auth__submit">
            {mode === "login" ? t("Đăng nhập", "Sign in") : t("Tạo tài khoản", "Create account")}
          </button>
        </form>

        <p className="auth__alt">
          {mode === "login" ? (
            <>{t("Chưa có tài khoản?", "Don't have an account?")} <a href="#register" onClick={(e) => { e.preventDefault(); setMode("register"); }}>{t("Đăng ký miễn phí", "Sign up free")}</a></>
          ) : (
            <>{t("Đã có tài khoản?", "Already have an account?")} <a href="#login" onClick={(e) => { e.preventDefault(); setMode("login"); }}>{t("Đăng nhập", "Sign in")}</a></>
          )}
        </p>

        <div className="auth__legal">
          {t(
            "Khi tiếp tục, bạn xác nhận đã đọc và đồng ý với",
            "By continuing, you acknowledge you have read and agreed to the",
          )} <a href="#terms">{t("Điều khoản dịch vụ", "Terms of Service")}</a> {t("và", "and")} <a href="#privacy">{t("Chính sách bảo mật", "Privacy Policy")}</a> {t("của Delfi.", "of Delfi.")}
        </div>
      </div>
    </section>
  );
}

function Auth() {
  return (
    <div className="auth">
      <AuthHero />
      <AuthPanel />
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(
  <LangProvider>
    <Auth />
  </LangProvider>
);
