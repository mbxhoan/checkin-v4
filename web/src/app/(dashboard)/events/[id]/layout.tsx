"use client";
import { use, useEffect } from "react";
import { notFound } from "next/navigation";
import { useApp } from "@/lib/context";
import { EVENTS } from "@/data/events";

export default function EventLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ id: string }>;
}) {
  const { id } = use(params);
  const { setCurrentEventId } = useApp();
  const event = EVENTS.find((e) => e.id === id);

  useEffect(() => {
    setCurrentEventId(id);
    return () => setCurrentEventId(null);
  }, [id, setCurrentEventId]);

  if (!event) notFound();

  return <>{children}</>;
}
