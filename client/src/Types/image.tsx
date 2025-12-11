export interface Image {
  id: number;
  imageable_id: number;
  imageable_type: string;
  url: string;
  is_primary: number; 
  description?: string | null;
}
