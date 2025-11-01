export interface Court {
    id: number;
    venue_id: number;
    name: string;
    surface: string;
    price_per_hour: number; // decimal(10,2) -> number on client
    is_indoor: boolean; // true = trong nhà, false = ngoài trời
    
}
