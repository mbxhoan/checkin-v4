"use client";
interface ToggleProps {
  on: boolean;
  onChange: (v: boolean) => void;
  disabled?: boolean;
}

export function Toggle({ on, onChange, disabled }: ToggleProps) {
  return (
    <div
      className={`toggle${on ? " toggle--on" : ""}${disabled ? " toggle--disabled" : ""}`}
      onClick={() => !disabled && onChange(!on)}
      role="switch"
      aria-checked={on}
    />
  );
}
