import React from "react";
import { useParams } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";

// Định nghĩa lại các màu theo quy tắc đã cung cấp
const PRIMARY_COLOR = "#10B981"; // Emerald Green
const SECONDARY_COLOR = "#F59E0B"; // Amber
const TEXT_PRIMARY = "text-[#11182C]"; // Đen Than
const TEXT_BODY = "text-[#4B5563]"; // Xám Đậm
const TEXT_SECONDARY = "text-[#6B7280]"; // Xám Vừa
const UTILITY_ERROR = "#EF4444"; // Đỏ

// Interface (Giữ nguyên)


// Thông tin thanh toán (VIETQR) - Giữ nguyên
const BANK_BIN = "970418"; 
const ACCOUNT_NO = "1903xxxxxxxx";
const ACCOUNT_NAME = "NGUYEN VAN A";
const BANK_NAME = "Techcombank";

// Component chính
const Ticket_Detail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { data, isLoading, error } = useFetchDataById<Ticket>("ticket", id!);

  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-[500px]">
        {/* Màu spinner dùng màu Primary */}
        <div className="animate-spin rounded-full h-20 w-20 border-t-4 border-b-4" style={{ borderColor: PRIMARY_COLOR }}></div>
      </div>
    );
  }

  if (error) {
    // Thông báo lỗi dùng màu Đỏ Utility
    return (
      <div className="max-w-xl mx-auto bg-red-50 border border-red-500 text-red-800 px-6 py-4 rounded-xl mt-10 shadow-lg text-base sm:text-lg" role="alert">
        <strong className={`font-bold text-xl ${TEXT_PRIMARY}`}>Đã xảy ra lỗi!</strong>
        <span className="block mt-1"> Không thể tải chi tiết ticket.</span>
      </div>
    );
  }

  if (!data || !data.data) {
    // Thông báo dùng màu Vàng Cam Secondary cho nổi bật
    return (
      <div className="max-w-xl mx-auto bg-amber-50 border border-amber-500 text-amber-800 px-6 py-4 rounded-xl mt-10 shadow-lg text-base sm:text-lg" role="alert">
        <strong className={`font-bold text-xl ${TEXT_PRIMARY}`}>Không tìm thấy!</strong>
        <span className="block mt-1"> Ticket với ID #{id} không tồn tại.</span>
      </div>
    );
  }

  const ticket = data.data;

  // --- LOGIC CHO CÁC BADGE (Điều chỉnh màu sắc) ---
  const statusConfig = {
    // pending: Dùng màu Vàng Cam Secondary
    pending: { text: "Đang chờ", icon: "fa-solid fa-clock", classes: "bg-amber-50 text-amber-800 border-amber-400" },
    // confirmed: Dùng màu Primary
    confirmed: { text: "Đã xác nhận", icon: "fa-solid fa-check-circle", classes: "bg-emerald-50 text-emerald-800 border-emerald-400" },
    // completed
    completed: { text: "Hoàn thành", icon: "fa-solid fa-check-circle", classes: "bg-emerald-50 text-emerald-800 border-emerald-400" },
    // cancelled: Dùng màu Đỏ Utility
    cancelled: { text: "Đã hủy", icon: "fa-solid fa-times-circle", classes: "bg-red-50 text-red-800 border-red-400" }, 
  };
  const currentStatus = statusConfig[ticket.status] || { text: ticket.status, icon: "fa-solid fa-question-circle", classes: "bg-gray-100 text-gray-800 border-gray-400" };

  const paymentStatusConfig = {
    // unpaid: Dùng màu Đỏ Utility
    unpaid: { text: "Chưa thanh toán", icon: "fa-solid fa-file-invoice-dollar", classes: "bg-red-50 text-red-800 border-red-400" }, 
    // paid: Dùng màu Primary
    paid: { text: "Đã thanh toán", icon: "fa-solid fa-check-double", classes: "bg-emerald-50 text-emerald-800 border-emerald-400" },
    // refunded: Dùng màu Xanh Dương (Màu phụ mới cho hoàn tiền)
    refunded: { text: "Đã hoàn tiền", icon: "fa-solid fa-rotate-left", classes: "bg-blue-50 text-blue-800 border-blue-400" },
  };
  const currentPaymentStatus = paymentStatusConfig[ticket.payment_status] || { text: ticket.payment_status, icon: "fa-solid fa-question-circle", classes: "bg-gray-100 text-gray-800 border-gray-400" };

  // Thông tin QR (Giữ nguyên logic)
  const qrMemo = `PAY ${ticket.id}`;
  const qrImageUrl = `https://api.vietqr.io/image/${BANK_BIN}-${ACCOUNT_NO}-compact.png?amount=${ticket.total_amount}&addInfo=${encodeURIComponent(qrMemo)}&accountName=${encodeURIComponent(ACCOUNT_NAME)}`;

  return (
    // Màu nền tổng thể: #F9FAFB
    <div className="min-h-screen p-4 sm:p-8" style={{ backgroundColor: "#F9FAFB" }}> 
      {/* Màu nền Card: #FFFFFF */}
      <div className="max-w-4xl mx-auto bg-white shadow-2xl rounded-xl overflow-hidden">
        
        {/* Header */}
        {/* Dùng màu Primary cho Header Background */}
        <div className="p-6 text-white" style={{ backgroundColor: PRIMARY_COLOR }}> 
          <div className="flex items-center justify-between">
            <div>
              {/* Tiêu đề chính (H1): text-4xl (Desktop), text-3xl (Mobile) */}
              <h2 className="text-3xl sm:text-4xl font-extrabold">Chi tiết Ticket</h2> 
              {/* Text phụ: text-sm (Mobile), text-base (Desktop) - Dùng text-lg cho nổi bật hơn*/}
              <p className="text-lg opacity-90 mt-1">Mã #{ticket.id}</p> 
            </div>
            <i className="fa-solid fa-ticket text-6xl opacity-30"></i>
          </div>
        </div>

        {/* Thân Ticket */}
        <div className="relative">
          {/* Lớp phủ "Đã hủy" */}
          {ticket.status === 'cancelled' && (
            <div className="absolute inset-0 z-10 flex items-center justify-center bg-gray-500/20"> 
                <div className="bg-white p-6 rounded-lg shadow-2xl border-4 rotate-[-5deg] transform scale-105" style={{ borderColor: UTILITY_ERROR }}> 
                    <h2 className="text-2xl sm:text-3xl font-bold uppercase tracking-widest" style={{ color: UTILITY_ERROR }}>
                        Ticket đã bị HỦY
                    </h2>
                </div>
            </div>
          )}
          
          <div className={`p-6 sm:p-8 space-y-8 ${ticket.status === 'cancelled' ? 'blur-sm pointer-events-none' : ''}`}>
          
            {/* Phần Trạng thái */}
            {/* Tiêu đề mục (Section Title) - Không có, nên dùng font-size tiêu đề thẻ (Card Title) cho badge text */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6"> 
              {/* Badge Trạng thái Ticket */}
              <div className={`p-5 rounded-xl border ${currentStatus.classes} shadow-md`}> 
                {/* Text phụ: text-sm (14px) */}
                <span className={`text-sm font-semibold uppercase tracking-wider opacity-80 ${TEXT_SECONDARY}`}>Trạng thái</span> 
                <div className="flex items-center gap-3 mt-2"> 
                  <i className="text-2xl" style={{ color: currentStatus.classes.includes('red') ? UTILITY_ERROR : currentStatus.classes.includes('emerald') ? PRIMARY_COLOR : SECONDARY_COLOR }}></i>
                  {/* Tiêu đề thẻ (Card Title): text-lg (Mobile), text-xl (Desktop) */}
                  <p className={`text-lg sm:text-xl font-bold ${TEXT_PRIMARY}`}>{currentStatus.text}</p> 
                </div>
              </div>
              {/* Badge Trạng thái Thanh toán */}
              <div className={`p-5 rounded-xl border ${currentPaymentStatus.classes} shadow-md`}> 
                {/* Text phụ: text-sm (14px) */}
                <span className={`text-sm font-semibold uppercase tracking-wider opacity-80 ${TEXT_SECONDARY}`}>Thanh toán</span> 
                <div className="flex items-center gap-3 mt-2"> 
                  <i className="text-2xl" style={{ color: currentPaymentStatus.classes.includes('red') ? UTILITY_ERROR : currentPaymentStatus.classes.includes('emerald') ? PRIMARY_COLOR : 'inherit' }}></i>
                  {/* Tiêu đề thẻ (Card Title): text-lg (Mobile), text-xl (Desktop) */}
                  <p className={`text-lg sm:text-xl font-bold ${TEXT_PRIMARY}`}>{currentPaymentStatus.text}</p>
                </div>
              </div>
            </div>

            {/* Phần Thanh toán QR */}
            {ticket.payment_status === 'unpaid' && (
              <div className="border-2 rounded-xl p-6 shadow-lg" style={{ borderColor: PRIMARY_COLOR, backgroundColor: '#ECFDF5' }}> {/* Dùng nền Emerald Light */}
                {/* Tiêu đề mục (Section Title): text-xl (Mobile), text-2xl (Desktop) */}
                <h3 className={`text-xl sm:text-2xl font-bold mb-5 border-b pb-3 ${TEXT_PRIMARY}`} style={{ borderBottomColor: PRIMARY_COLOR }}> 
                  <i className="fa-solid fa-qrcode mr-3 text-2xl" style={{ color: PRIMARY_COLOR }}></i>
                  Quét mã để thanh toán
                </h3>
                <div className="flex flex-col md:flex-row gap-6 items-center"> 
                  <img src={qrImageUrl} alt="VietQR Code" className="w-48 h-48 rounded-lg border-4 border-white shadow-xl" />
                  <div className={`space-y-3 flex-1 text-base ${TEXT_BODY}`}> 
                    <p><span className={`font-semibold ${TEXT_SECONDARY}`}>Ngân hàng:</span> <span className={`font-extrabold ml-2 text-lg ${TEXT_PRIMARY}`}>{BANK_NAME}</span></p> 
                    <p><span className={`font-semibold ${TEXT_SECONDARY}`}>Số tài khoản:</span> <span className={`font-extrabold ml-2 text-lg ${TEXT_PRIMARY}`}>{ACCOUNT_NO}</span></p> 
                    <p><span className={`font-semibold ${TEXT_SECONDARY}`}>Chủ tài khoản:</span> <span className={`font-extrabold ml-2 text-lg ${TEXT_PRIMARY}`}>{ACCOUNT_NAME}</span></p> 
                    <p><span className={`font-semibold ${TEXT_SECONDARY}`}>Số tiền:</span> <span className="font-extrabold ml-2 text-2xl" style={{ color: UTILITY_ERROR }}>{Number(ticket.total_amount).toLocaleString()} đ</span></p>
                    <div className="pt-3">
                      <span className={`font-semibold ${TEXT_SECONDARY}`}>Nội dung chuyển khoản:</span>
                      {/* Màu nền dùng Amber/Secondary, màu chữ dùng Đen Than Primary */}
                      <p className={`font-extrabold text-xl ${TEXT_PRIMARY} border px-3 py-2 rounded-lg inline-block mt-2 select-all`} style={{ backgroundColor: '#FFFBEB', borderColor: SECONDARY_COLOR }}>{qrMemo}</p> 
                      {/* Text phụ: text-sm (14px) */}
                      <p className={`text-sm italic mt-2 ${TEXT_SECONDARY}`}>Vui lòng nhập <span className="font-bold" style={{ color: UTILITY_ERROR }}>CHÍNH XÁC</span> nội dung chuyển khoản để được xác nhận tự động.</p>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Phần Tài chính */}
            <div className="border rounded-xl shadow-sm" style={{ borderColor: "#E5E7EB" }}> 
              {/* Tiêu đề mục (Section Title): text-xl (Mobile), text-2xl (Desktop) */}
              <h3 className={`text-xl sm:text-2xl font-bold px-6 py-4 border-b ${TEXT_PRIMARY}`} style={{ borderColor: "#E5E7EB" }}>Chi tiết thanh toán</h3> 
              <div className="p-6 space-y-4"> 
                {/* Chữ nội dung: text-base (16px) */}
                <div className={`flex justify-between text-base ${TEXT_BODY}`}> 
                  <span>Tạm tính (Subtotal):</span>
                  <span className="font-medium">{Number(ticket.subtotal).toLocaleString()} đ</span>
                </div>
                {/* Chữ nội dung: text-base (16px) */}
                <div className={`flex justify-between text-base ${TEXT_BODY}`}> 
                  <span>Giảm giá (Discount):</span>
                  <span className="font-medium" style={{ color: UTILITY_ERROR }}>- {Number(ticket.discount_amount).toLocaleString()} đ</span>
                </div>
                <div className="border-t pt-4 mt-4" style={{ borderColor: "#E5E7EB" }}> 
                  <div className={`flex justify-between text-xl sm:text-2xl font-extrabold ${TEXT_PRIMARY}`}> 
                    <span>Tổng cộng (Total):</span>
                    <span style={{ color: PRIMARY_COLOR }}>{Number(ticket.total_amount).toLocaleString()} đ</span> 
                  </div>
                </div>
              </div>
            </div>

            {/* Phần Chi tiết */}
            <div>
              {/* Tiêu đề mục (Section Title): text-xl (Mobile), text-2xl (Desktop) */}
              <h3 className={`text-xl sm:text-2xl font-bold mb-5 border-b pb-2 ${TEXT_PRIMARY}`} style={{ borderColor: "#E5E7EB" }}>Thông tin chi tiết</h3> 
              <div className="space-y-4"> 
                {/* Chữ nội dung: text-base (16px) */}
                <div className="flex justify-between border-b pb-3" style={{ borderColor: "#E5E7EB" }}> 
                  <span className={`font-medium ${TEXT_SECONDARY}`}>User ID:</span>
                  <span className={`font-bold text-base ${TEXT_PRIMARY}`}>{ticket.user_id}</span> 
                </div>
                {/* Chữ nội dung: text-base (16px) */}
                <div className="flex justify-between border-b pb-3" style={{ borderColor: "#E5E7EB" }}> 
                  <span className={`font-medium ${TEXT_SECONDARY}`}>Mã khuyến mãi:</span>
                  <span className={`font-bold text-base ${TEXT_PRIMARY}`}>{ticket.promotion_id ?? "Không áp dụng"}</span> 
                </div>
                {ticket.notes && (
                  <div>
                    {/* Text phụ: text-sm (14px) */}
                    <span className={`font-medium text-sm block mb-1 ${TEXT_SECONDARY}`}>Ghi chú:</span>
                    {/* Chữ nội dung: text-base (16px) */}
                    <p className={`mt-1 p-4 bg-gray-50 rounded-lg italic border text-base ${TEXT_BODY}`} style={{ borderColor: "#E5E7EB" }}>{ticket.notes}</p> 
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="border-t p-5 text-sm space-y-2" style={{ backgroundColor: "#F9FAFB", borderColor: "#E5E7EB" }}> 
          <div className="flex justify-between">
            {/* Text phụ: text-sm (14px) */}
            <span className={`font-medium text-sm ${TEXT_SECONDARY}`}>Ngày tạo:</span>
            <span className={`font-semibold text-sm ${TEXT_BODY}`}>{new Date(ticket.created_at).toLocaleString("vi-VN")}</span>
          </div>
          <div className="flex justify-between">
            {/* Text phụ: text-sm (14px) */}
            <span className={`font-medium text-sm ${TEXT_SECONDARY}`}>Cập nhật lần cuối:</span>
            <span className={`font-semibold text-sm ${TEXT_BODY}`}>{new Date(ticket.updated_at).toLocaleString("vi-VN")}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Ticket_Detail;