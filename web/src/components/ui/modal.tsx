"use client";
import { useEffect, type ReactNode } from "react";
import { Icon } from "./icon";

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title: string;
  sub?: string;
  icon?: Parameters<typeof Icon>[0]["name"];
  size?: "sm" | "md" | "lg";
  children: ReactNode;
  footer?: ReactNode;
}

export function Modal({ open, onClose, title, sub, icon, size = "md", children, footer }: ModalProps) {
  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => { if (e.key === "Escape") onClose(); };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div
        className={`modal${size === "lg" ? " modal--lg" : size === "sm" ? " modal--sm" : ""}`}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="modal__head">
          {icon && (
            <div className="modal__head-icon">
              <Icon name={icon} size={18} />
            </div>
          )}
          <div>
            <h3>{title}</h3>
            {sub && <p>{sub}</p>}
          </div>
          <button className="modal__close" onClick={onClose} aria-label="Close">
            <Icon name="x" size={16} />
          </button>
        </div>
        <div className="modal__body">{children}</div>
        {footer && <div className="modal__footer">{footer}</div>}
      </div>
    </div>
  );
}
