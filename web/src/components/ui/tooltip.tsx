"use client";
interface TooltipProps { text: string }

export function Tooltip({ text }: TooltipProps) {
  return (
    <span className="tip">
      <span className="tip__trigger">?</span>
      <span className="tip__bubble">{text}</span>
    </span>
  );
}
