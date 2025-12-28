import type { ISODateTimeString } from "./common";
import type { User } from "./user";

// --- 1. CÁC INTERFACE CON (HELPER) ---

// Thông tin chi tiết về Service (Nước, Vợt...)
export interface ServiceImage {
  id: number;
  url: string;
}

export interface ServiceDetail {
  id: number;
  name: string;
  unit: string;
  type: 'consumable' | 'service' | 'amenities'; // Loại dịch vụ
  images?: ServiceImage[];
}

export interface VenueServiceDetail {
  id: number;
  venue_id: number;
  service?: ServiceDetail;
}

// Thông tin chi tiết về Booking (Sân, Giờ)
export interface BookingDetail {
  id: number;
  court_id: number;
  date: ISODateTimeString;
  status: "pending" | "confirmed" | "cancelled" | "completed";
  court?: {
    id: number;
    name: string;
    venue?: {
      id: number;
      name: string;
    };
  };
  time_slot?: {
    id: number;
    start_time: string; // HH:mm:ss
    end_time: string;   // HH:mm:ss
  };
}

// --- 2. CHI TIẾT TỪNG ITEM TRONG HÓA ĐƠN ---
export interface TicketItem {
  id: number;
  ticket_id: number;

  // Khóa ngoại: Item có thể là Booking HOẶC VenueService
  booking_id?: number | null;
  venue_service_id?: number | null;

  // Tài chính & Số lượng
  unit_price: string | number;      // Giá đơn vị
  quantity: number;                 // Số lượng (Quan trọng)
  discount_amount: string | number; // Số tiền giảm
  total_price?: string | number;    // Tổng tiền dòng này (nếu BE trả về)

  status: "active" | "refund";      // Trạng thái item

  // Quan hệ dữ liệu (Relations)
  booking?: BookingDetail;          // Nếu là đặt sân
  venue_service?: VenueServiceDetail; // Nếu là dịch vụ
}

// --- 3. THÔNG TIN TỔNG QUÁT TICKET ---
export interface Ticket {
  id: number;
  user_id: number;
  promotion_id?: number | null;

  // Tài chính tổng
  subtotal: string | number;
  discount_amount: string | number;
  total_amount: string | number;

  // Trạng thái
  status: "pending" | "confirmed" | "cancelled" | "completed";
  payment_status: "unpaid" | "paid" | "refunded";

  notes?: string | null;
  booking_code?: string | null;

  // Thời gian
  created_at: ISODateTimeString;
  updated_at: ISODateTimeString;
  deleted_at?: ISODateTimeString | null;

  // Quan hệ
  user: User;
  items?: TicketItem[];
}