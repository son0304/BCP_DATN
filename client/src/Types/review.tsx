export interface Review {
  id: number;
  venue_id: number;
  rating: number; // 0..5
  comment?: string | null;
  user_id?: number;
}
