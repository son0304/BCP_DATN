import type { Court } from "./court";
import type { Image } from "./image";
import type { Province } from "./province";
import type { Review } from "./review";
import type { User } from "./user";
import type { VenueTypeWithPivot } from "./venueType";
import type { ISODateTimeString } from "./common";


export interface Venue {
    id: number;
    owner_id: number;
    name: string;
    start_time?: string;          
    end_time?: string;
    address_detail: string;
    district_id: number;
    province_id: number;
    lat: number; // số
    lng: number; // số
    phone?: string | null;
    email?: string | null;
    is_active: boolean;
    created_at: ISODateTimeString;
    updated_at: ISODateTimeString;
    deleted_at: ISODateTimeString | null;
    reviews_avg_rating?: number;
    description?:string

    // Quan hệ (nested)
    images?: Image[];
    courts?: (Court & { time_slots?: any[] })[];
    reviews?: Review[];
    types?: VenueTypeWithPivot[];
    province?: Province;
    owner?: User;
}
