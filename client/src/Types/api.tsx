// Dùng chung cho mọi API response
export interface ApiResponse<T> {
    message: string;
    success: boolean;
    data: T;
  }
  