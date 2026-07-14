"use client";
import { Toggle } from "./toggle";
import { Tooltip } from "./tooltip";

interface OptRowProps {
  title: string;
  desc?: string;
  on: boolean;
  onChange: (v: boolean) => void;
  tip?: string;
  disabled?: boolean;
}

export function OptRow({ title, desc, on, onChange, tip, disabled }: OptRowProps) {
  return (
    <div className="opt-row">
      <div className="opt-row__main">
        <div className="opt-row__title">
          {title}
          {tip && <Tooltip text={tip} />}
        </div>
        {desc && <div className="opt-row__desc">{desc}</div>}
      </div>
      <Toggle on={on} onChange={onChange} disabled={disabled} />
    </div>
  );
}
