export interface TimeSlot {
    id: number;
    start_time: string;
    end_time: string;
    is_booking?: 'confirmed' | 'pending' | 'canceled';
    label?: string | null; 
  }
  