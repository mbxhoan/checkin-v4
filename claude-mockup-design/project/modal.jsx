// modal.jsx — generic modal/dialog used across the app

const { useEffect: mEU } = React;

const Modal = ({ open, onClose, title, sub, icon, size = "md", footer, children }) => {
  mEU(() => {
    if (!open) return;
    const onKey = (e) => { if (e.key === "Escape") onClose(); };
    document.addEventListener("keydown", onKey);
    document.body.style.overflow = "hidden";
    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = "";
    };
  }, [open, onClose]);

  if (!open) return null;
  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className={"modal modal--" + size} role="dialog" aria-modal="true" onClick={(e) => e.stopPropagation()}>
        <header className="modal__head">
          {icon && <div className="modal__head-icon"><Icon name={icon} /></div>}
          <div style={{ flex: 1, minWidth: 0 }}>
            <h3 className="modal__title">{title}</h3>
            {sub && <p className="modal__sub">{sub}</p>}
          </div>
          <button className="modal__close" onClick={onClose} aria-label="Đóng">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
          </button>
        </header>
        <div className="modal__body">{children}</div>
        {footer && <footer className="modal__foot">{footer}</footer>}
      </div>
    </div>
  );
};

Object.assign(window, { Modal });
