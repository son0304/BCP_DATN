export interface TimeSlot {
  id: number;
  label: string;
  start_time: string;
  end_time: string;
  status?: "open" | "booked" | "closed" | "maintenance" | null;
}
