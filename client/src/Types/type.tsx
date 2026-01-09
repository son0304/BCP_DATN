export type VenueSlot = {
  id: number; // time_slot_id hoặc availability_id
  time_slot_id?: number;
  start_time: string;
  end_time: string;
  price: number | string;
  status?: 'open' | 'booked' | 'closed' | 'maintenance' | 'unavailable';
  sale_price: number | string | null;
  flash_status?: "active" | "sold_out" | "inactive";
  quantity?: number;
  sold_count?: number;
};

export type Court = {
  id: number;
  name: string;
  time_slots?: VenueSlot[];
  availabilities?: VenueSlot[]; // API có thể trả về key này
};

export type SelectedItem = {
  court_id: number;
  court_name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
  sale_price: number;
}