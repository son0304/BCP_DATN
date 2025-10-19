import type { ID, ISODateString } from "./common";

export type BookingStatus = "pending" | "confirmed" | "cancelled" | "completed";

export interface Booking {
    id: ID;
    user_id: ID;
    court_id: ID;
    time_slot_id: ID; // matches backend naming in bookings
    date: ISODateString;
    status: BookingStatus;
}


