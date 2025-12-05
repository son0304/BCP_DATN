import type { User } from "./user";

export interface Image {
  id: number;
  url: string;
}
export interface Review {
  id: number;
  venue_id: number;
  rating: number; // 0..5
  comment?: string | null;
  images: Image[];
  user_id?: number;
  user: User;
  created_at: string
}
