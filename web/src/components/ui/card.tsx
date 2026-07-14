"use client";
import { type ReactNode } from "react";
import { Icon } from "./icon";

interface CardProps {
  title: string;
  sub?: string;
  icon?: string;
  children: ReactNode;
  actions?: ReactNode;
}

export function Card({ title, sub, icon, children, actions }: CardProps) {
  return (
    <div className="card">
      <div className="card__head">
        {icon && <Icon name={icon as Parameters<typeof Icon>[0]["name"]} size={18} style={{ color: "var(--primary)" }} />}
        <div>
          <h3>{title}</h3>
          {sub && <div className="card__sub">{sub}</div>}
        </div>
        {actions && <div className="qa" style={{ marginLeft: "auto" }}>{actions}</div>}
      </div>
      <div className="card__body">{children}</div>
    </div>
  );
}
