import type { ISODateTimeString } from "./common";
import type { Court } from "./court";
import type { Image } from "./image";
import type { Province } from "./province";
import type { Review } from "./review";
import type { TimeSlot } from "./timeSlot";
import type { User } from "./user";
import type { VenueTypeWithPivot } from "./venueType";

/**
 * Interface `EnrichedTimeSlot` được tạo ra để giải quyết lỗi của bạn.
 * Nó kế thừa (extends) từ `TimeSlot` gốc và bổ sung thêm hai thuộc tính quan trọng là
 * `status` và `price` mà API trả về từ bảng 'availabilities'.
 */
export interface EnrichedTimeSlot extends TimeSlot {
  status: 'open' | 'booked' | 'closed' | 'maintenance' | null;
  price: number | null;
}

export interface Voucher {
  id: number;
  code: string;
  description: string;

  type: 'percentage' | 'fixed'; // có thể mở rộng thêm
  value: string; // % hoặc số tiền
  max_discount_amount: number;
  min_order_value: number;

  usage_limit: number;
  used_count: number;

  target_user_type: 'new_user' | 'all' | 'old_user';

  process_status: 'active' | 'inactive' | 'expired';

  start_at: string; // ISO datetime
  end_at: string;   // ISO datetime

  creator_user_id: number;
  venue_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}


/**
 * Định nghĩa cấu trúc đầy đủ của một đối tượng Venue 
 * được trả về từ API, bao gồm các quan hệ lồng nhau.
 */
export interface Venue {
  id: number;
  owner_id: number;
  name: string;
  start_time?: string;
  end_time?: string;
  description?: string;
  address_detail: string;
  district_id: number;
  province_id: number;
  lat: number | string; // API trả về string
  lng: number | string; // API trả về string
  phone?: string | null;
  email?: string | null;
  type: string;           // THÊM DÒNG NÀY
  is_active: boolean | number; // API trả về number (1/0)
  created_at: ISODateTimeString;
  updated_at: ISODateTimeString;
  deleted_at: ISODateTimeString | null;
  reviews_avg_rating?: number | string; // API trả về string

  // === QUAN HỆ (NESTED RELATIONSHIPS) ===

  images?: Image[];

  courts?: (Court & { time_slots?: EnrichedTimeSlot[] })[];

  reviews?: Review[];
  venue_types?: VenueTypeWithPivot[];
  province?: Province;
  owner?: User;
  promotions?: Voucher[];
}