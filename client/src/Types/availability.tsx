import type { ID, ISODateString } from "./common";

export type AvailabilityStatus = "open" | "closed" | "booked";

export interface Availability {
    id: ID;
    court_id: ID;
    slot_id: ID; // reference to TimeSlot.id
    date: ISODateString; // "YYYY-MM-DD"
    status: AvailabilityStatus;
    note?: string | null;
}


