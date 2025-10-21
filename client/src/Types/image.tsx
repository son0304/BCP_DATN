export interface Image {
  id: number;
  venue_id: number;
  url: string;
  is_primary: number; // true nếu ảnh chính
  description?: string | null;
}
