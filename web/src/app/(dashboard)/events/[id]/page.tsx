import { redirect } from "next/navigation";
export default function EventRoot({ params }: { params: Promise<{ id: string }> }) {
  return params.then(({ id }) => redirect(`/events/${id}/overview`));
}
