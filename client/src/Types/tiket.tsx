import type { ISODateTimeString } from "./common";

export interface Ticket {
    id: number;
    user_id: number;
    promotion_id: number | null;
    subtotal: number | string;
    discount_amount: number | string;
    total_amount: number | string;
    status: "pending" | "confirmed" | "cancelled";
    payment_status: "unpaid" | "paid" | "refunded";
    notes: string | null;
    created_at: ISODateTimeString;
    updated_at: ISODateTimeString;
  }