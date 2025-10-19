import React from "react";
import { useParams } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { ISODateTimeString } from "../../Types/common";

// Interface (Giữ nguyên)
interface TicketData {
  id: number;
  user_id: number;
  promotion_id: number | null;
  subtotal: number | string;
  discount_amount: number | string;
  total_amount: number | string;
  status: "pending" | "confirmed" | "canceled";
  payment_status: "unpaid" | "paid" | "refunded";
  notes: string | null;
  created_at: ISODateTimeString;
  updated_at: ISODateTimeString;
}

// --- THÔNG TIN THANH TOÁN (VIETQR) ---
// !! Tốt nhất nên lưu ở file .env
const BANK_BIN = "970418"; // BIN của Techcombank (ví dụ)
const ACCOUNT_NO = "1903xxxxxxxx"; // Số tài khoản của bạn
const ACCOUNT_NAME = "NGUYEN VAN A"; // Tên chủ tài khoản
const BANK_NAME = "Techcombank"; // Tên ngân hàng
// ---

// (Các component StatusBadge và PaymentStatusBadge giữ nguyên như cũ)
// ... (Giữ nguyên StatusBadge, PaymentStatusBadge)

// Component chính
const Ticket: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { data, isLoading, error } = useFetchDataById<TicketData>("ticket", id!);

  // (Loading, Error, Not Found states giữ nguyên)
  // ...

  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-[400px]">
        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-[#348738]"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-md mx-auto bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mt-10" role="alert">
        <strong className="font-bold">Đã xảy ra lỗi!</strong>
        <span className="block sm:inline"> Không thể tải chi tiết ticket.</span>
      </div>
    );
  }

  if (!data || !data.data) {
    return (
      <div className="max-w-md mx-auto bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mt-10" role="alert">
        <strong className="font-bold">Không tìm thấy!</strong>
        <span className="block sm:inline"> Ticket với ID #{id} không tồn tại.</span>
      </div>
    );
  }

  const ticket = data.data;

  // --- TẠO THÔNG TIN QR (MỚI) ---
  const qrMemo = `PAY ${ticket.id}`; // Mã nội dung thanh toán DUY NHẤT
  const qrImageUrl = `https://api.vietqr.io/image/${BANK_BIN}-${ACCOUNT_NO}-compact.png?amount=${ticket.total_amount}&addInfo=${encodeURIComponent(
    qrMemo
  )}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;
  // ---

  // --- Giao diện chính ---
  return (
    <div className="min-h-screen bg-gray-50 p-4 sm:p-8">
      <div className="max-w-3xl mx-auto bg-white shadow-2xl rounded-xl overflow-hidden">
        
        {/* Header của Ticket (Giữ nguyên) */}
        <div className="bg-gradient-to-r from-[#348738] to-[#2d6a2d] p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-3xl font-bold">Chi tiết Ticket</h2>
              <p className="text-lg opacity-90">Mã #{ticket.id}</p>
            </div>
            <i className="fa-solid fa-ticket text-5xl opacity-30"></i>
          </div>
        </div>

        {/* Thân Ticket */}
        <div className="p-6 sm:p-8 space-y-8">
          
          {/* Phần Trạng thái (Giữ nguyên) */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <StatusBadge status={ticket.status} />
            <PaymentStatusBadge status={ticket.payment_status} />
          </div>

          {/* === PHẦN THANH TOÁN QR (MỚI) === */}
          {/* Chỉ hiển thị khi CHƯA THANH TOÁN */}
          {ticket.payment_status === 'unpaid' && (
            <div className="border border-green-300 bg-green-50 rounded-lg p-5">
              <h3 className="text-lg font-semibold text-green-900 mb-4">
                <i className="fa-solid fa-qrcode mr-2"></i>
                Quét mã để thanh toán
              </h3>
              <div className="flex flex-col sm:flex-row gap-5 items-center">
                
                {/* Mã QR */}
                <img 
                  src={qrImageUrl} 
                  alt="VietQR Code" 
                  className="w-40 h-40 md:w-48 md:h-48 rounded-lg border-4 border-white shadow-lg" 
                />

                {/* Thông tin chuyển khoản */}
                <div className="space-y-2 text-gray-800 flex-1">
                  <p>
                    <span className="font-medium text-gray-600">Ngân hàng:</span> 
                    <span className="font-bold ml-2">{BANK_NAME}</span>
                  </p>
                  <p>
                    <span className="font-medium text-gray-600">Số tài khoản:</span> 
                    <span className="font-bold ml-2">{ACCOUNT_NO}</span>
                  </p>
                  <p>
                    <span className="font-medium text-gray-600">Chủ tài khoản:</span> 
                    <span className="font-bold ml-2">{ACCOUNT_NAME}</span>
                  </p>
                  <p>
                    <span className="font-medium text-gray-600">Số tiền:</span> 
                    <span className="font-bold ml-2 text-xl text-red-600">{Number(ticket.total_amount).toLocaleString()} đ</span>
                  </p>
                  
                  <div className="pt-2">
                    <span className="font-medium text-gray-600">Nội dung:</span> 
                    <p className="font-bold text-lg text-red-700 bg-red-100 border border-red-300 px-2 py-1 rounded-md inline-block mt-1">
                      {qrMemo}
                    </p>
                    <p className="text-xs italic text-gray-500 mt-2">
                      Vui lòng nhập <span className="font-bold">ĐÚNG</span> nội dung chuyển khoản để được xác nhận tự động.
                    </p>
                  </div>

                </div>
              </div>
            </div>
          )}
          {/* === KẾT THÚC PHẦN QR === */}


          {/* Phần Tài chính (Giữ nguyên) */}
          <div className="border border-gray-200 rounded-lg">
            <h3 className="text-lg font-semibold text-gray-800 px-5 py-4 border-b">
              Chi tiết thanh toán
            </h3>
            <div className="p-5 space-y-3">
              <div className="flex justify-between text-gray-600">
                <span>Tạm tính (Subtotal):</span>
                <span>{Number(ticket.subtotal).toLocaleString()} đ</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>Giảm giá (Discount):</span>
                <span className="text-red-600">- {Number(ticket.discount_amount).toLocaleString()} đ</span>
              </div>
              
              <div className="border-t border-dashed pt-4 mt-4">
                <div className="flex justify-between text-xl font-bold text-gray-900">
                  <span>Tổng cộng (Total):</span>
                  <span className="text-[#2d6a2d]">{Number(ticket.total_amount).toLocaleString()} đ</span>
                </div>
              </div>
            </div>
          </div>

          {/* Phần Chi tiết (Giữ nguyên) */}
          <div>
            <h3 className="text-lg font-semibold text-gray-800 mb-4">Thông tin chi tiết</h3>
            <div className="space-y-3">
              <div className="flex justify-between border-b pb-3">
                <span className="font-medium text-gray-500">User ID:</span>
                <span className="font-semibold text-gray-900">{ticket.user_id}</span>
              </div>
              <div className="flex justify-between border-b pb-3">
                <span className="font-medium text-gray-500">Mã khuyến mãi:</span>
                <span className="font-semibold text-gray-900">{ticket.promotion_id ?? "Không áp dụng"}</span>
              </div>
              {ticket.notes && (
                <div>
                  <span className="font-medium text-gray-500">Ghi chú:</span>
                  <p className="mt-1 p-3 bg-gray-50 rounded-md text-gray-700 italic border">
                    {ticket.notes}
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Footer của Ticket (Metadata - Giữ nguyên) */}
        <div className="bg-gray-50 border-t p-5 text-sm text-gray-500 space-y-1">
          <div className="flex justify-between">
            <span>Ngày tạo:</span>
            <span>{new Date(ticket.created_at).toLocaleString("vi-VN")}</span>
          </div>
          <div className="flex justify-between">
            <span>Cập nhật lần cuối:</span>
            <span>{new Date(ticket.updated_at).toLocaleString("vi-VN")}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

// (Giữ nguyên StatusBadge và PaymentStatusBadge)
const StatusBadge: React.FC<{ status: TicketData["status"] }> = ({ status }) => {
  const statusConfig = {
    pending: {
      text: "Đang chờ",
      icon: "fa-solid fa-clock",
      classes: "bg-yellow-100 text-yellow-800 border-yellow-300",
    },
    confirmed: {
      text: "Đã xác nhận",
      icon: "fa-solid fa-check-circle",
      classes: "bg-green-100 text-green-800 border-green-300",
    },
    canceled: {
      text: "Đã hủy",
      icon: "fa-solid fa-times-circle",
      classes: "bg-red-100 text-red-800 border-red-300",
    },
  };
  const config = statusConfig[status];

  return (
    <div className={`p-4 rounded-lg border ${config.classes}`}>
      <span className="text-xs font-semibold uppercase opacity-70">Trạng thái</span>
      <div className="flex items-center gap-2 mt-1">
        <i className={`${config.icon} text-lg`}></i>
        <p className="text-lg font-bold">{config.text}</p>
      </div>
    </div>
  );
};

const PaymentStatusBadge: React.FC<{ status: TicketData["payment_status"] }> = ({ status }) => {
  const statusConfig = {
    unpaid: {
      text: "Chưa thanh toán",
      icon: "fa-solid fa-file-invoice-dollar",
      classes: "bg-red-100 text-red-800 border-red-300",
    },
    paid: {
      text: "Đã thanh toán",
      icon: "fa-solid fa-check-double",
      classes: "bg-green-100 text-green-800 border-green-300",
    },
    refunded: {
      text: "Đã hoàn tiền",
      icon: "fa-solid fa-rotate-left",
      classes: "bg-blue-100 text-blue-800 border-blue-300",
    },
  };
  const config = statusConfig[status];

  return (
    <div className={`p-4 rounded-lg border ${config.classes}`}>
      <span className="text-xs font-semibold uppercase opacity-70">Thanh toán</span>
      <div className="flex items-center gap-2 mt-1">
        <i className={`${config.icon} text-lg`}></i>
        <p className="text-lg font-bold">{config.text}</p>
      </div>
    </div>
  );
};


export default Ticket;