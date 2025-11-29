import type { ISODateTimeString } from "./common";
import type { User } from "./user";

// Chi tiết từng booking trong 1 ticket
export interface TicketItem {
  id: number;
  ticket_id: number;
  booking_id: number;
  unit_price: string | number;
  discount_amount: string | number;
  status: "active" | "refund" ;
  is_booking?: boolean | null;
  booking?: {
    id: number;
    court_id: number;
    date: ISODateTimeString;
    status: "pending" | "confirmed" | "cancelled";
    court?: {
      id: number;
      name: string;
    };
    time_slot?: {
      id: number;
      start_time: string;
      end_time: string;
    };
  };
}

// Thông tin tổng quát của 1 ticket
export interface Ticket {
  id: number;
  user_id: number;
  promotion_id?: number | null;
  user: User;

  subtotal: string | number;
  discount_amount: string | number;
  total_amount: string | number;

  status: "pending" | "confirmed" | "cancelled";
  payment_status: "unpaid" | "paid" | "refunded";

  notes?: string | null;

  created_at: ISODateTimeString;
  updated_at: ISODateTimeString;
  deleted_at?: ISODateTimeString | null;

  items?: TicketItem[];
}
