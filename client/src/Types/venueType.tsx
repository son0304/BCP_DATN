export interface Pivot {
    venue_id: number;
    venue_type_id: number;
    created_at: string;
    updated_at: string;
}

export interface VenueTypeWithPivot {
    id: number;
    name: string;
    pivot: Pivot;
}
