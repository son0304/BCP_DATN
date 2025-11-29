// src/types/User.ts

export interface Wallet {
  id: number;
  user_id: number;
  balance: string | number; // DB trả string (decimal) nhưng có thể dùng number
}


export interface User {
  id: number;                   // ID của user
  name: string;                 // Họ tên
  email: string;                // Email
  role_id: number;              // Role ID, mặc định 2 nếu không có
  phone?: string | null;        // Số điện thoại (có thể null)
  avt?: string | null;          // Avatar URL (có thể null)
  district?: string | null;  // District ID (có thể null)
  province?: string | null;  // Province ID (có thể null)
  is_active: boolean;           // Trạng thái active
  wallet?: Wallet;            // Thông tin ví tiền (có thể null)

}
