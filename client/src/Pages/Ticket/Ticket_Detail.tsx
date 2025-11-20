import { useState } from "react";
import { useParams, Link } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";

// Import các component thanh toán
import PaymentMomo from "../Payment/PaymentMomo";
import PaymentVNPay from "../Payment/PaymentVNPay";

const Ticket_Detail = () => {
  const [paymentMethod, setPaymentMethod] = useState<string>("");
  const { id } = useParams();

  // Lấy hàm refetch từ hook để reload lại dữ liệu sau khi thanh toán xong
  const { data, isLoading, isError, refetch } = useFetchDataById<Ticket>("ticket", id || "");

  // --- CONFIG & HELPERS ---

  // Format tiền tệ: Bỏ số thập phân, thêm dấu chấm phân cách hàng nghìn
  const formatCurrency = (value: string | number) => {
    if (!value) return "0₫";
    return Number(value).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "₫";
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case "pending":
        return { label: "Chờ thanh toán", class: "bg-yellow-50 text-yellow-700 border-yellow-200", icon: "fa-hourglass-half" };
      case "confirmed":
        return { label: "Đã thanh toán", class: "bg-emerald-50 text-emerald-700 border-emerald-200", icon: "fa-circle-check" };
      case "cancelled":
        return { label: "Đã hủy", class: "bg-red-50 text-red-700 border-red-200", icon: "fa-circle-xmark" };
      default:
        return { label: "Hoàn thành", class: "bg-blue-50 text-blue-700 border-blue-200", icon: "fa-flag-checkered" };
    }
  };

  // --- HANDLER ---

  // Hàm này sẽ được Component con (PaymentMomo) gọi khi polling thấy thành công
  const handlePaymentSuccess = () => {
    // 1. Tải lại dữ liệu vé mới nhất từ server (để trạng thái chuyển sang Confirmed)
    refetch();

    // 2. Reset phương thức thanh toán để ẩn khung QR Code đi
    setPaymentMethod("");
  };

  // --- LOADING & ERROR STATES ---
  if (isLoading)
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center space-y-4">
        <div className="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        <p className="text-gray-500 font-medium">Đang tải thông tin vé...</p>
      </div>
    );

  if (isError || !data?.data)
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center text-center p-6">
        <i className="fa-solid fa-triangle-exclamation text-4xl text-red-500 mb-4"></i>
        <h2 className="text-xl font-bold text-gray-800">Không tìm thấy đơn hàng</h2>
        <Link to="/" className="mt-4 text-emerald-600 hover:underline">Quay về trang chủ</Link>
      </div>
    );

  // --- DATA PREPARATION ---
  const ticket = data.data;
  const items = ticket.items ?? [];
  const status = getStatusConfig(ticket.status);

  return (
    <div className="bg-gray-50 min-h-screen py-10 px-4 font-sans">
      <div className="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 relative">

        {/* Họa tiết trang trí đầu hóa đơn */}
        <div className="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500"></div>

        {/* === HEADER === */}
        <div className="p-6 pb-4 text-center border-b border-gray-100 border-dashed">
          <div className="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-600">
            <i className="fa-solid fa-receipt text-2xl"></i>
          </div>
          <h1 className="text-2xl font-bold text-gray-800 uppercase tracking-wide">Hóa đơn đặt sân</h1>
          <p className="text-sm text-gray-500 mt-1">Mã vé: <span className="font-mono font-bold text-gray-800">#{ticket.id}</span></p>

          <div className="mt-4 flex justify-center">
            <span className={`flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-bold border ${status.class}`}>
              <i className={`fa-solid ${status.icon}`}></i>
              {status.label}
            </span>
          </div>
        </div>

        {/* === BODY === */}
        <div className="p-6 space-y-8">

          {/* 1. Thông tin khách hàng */}
          <div className="flex flex-col md:flex-row justify-between gap-6 text-sm">
            <div className="space-y-1">
              <p className="text-gray-500 text-xs uppercase font-semibold tracking-wider">Người đặt</p>
              <p className="font-bold text-gray-800 text-base">{ticket.user.name}</p>
              <p className="text-gray-600"><i className="fa-solid fa-phone text-xs mr-2 w-4"></i>{ticket.user.phone}</p>
              <p className="text-gray-600"><i className="fa-solid fa-envelope text-xs mr-2 w-4"></i>{ticket.user.email}</p>
            </div>
            <div className="space-y-1 md:text-right">
              <p className="text-gray-500 text-xs uppercase font-semibold tracking-wider">Thời gian tạo</p>
              <p className="font-medium text-gray-800">
                {new Date(ticket.created_at).toLocaleTimeString("vi-VN", { hour: '2-digit', minute: '2-digit' })}
              </p>
              <p className="text-gray-600">
                {new Date(ticket.created_at).toLocaleDateString("vi-VN", { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
              </p>
            </div>
          </div>

          {/* 2. Chi tiết booking */}
          <div>
            <p className="text-gray-500 text-xs uppercase font-semibold tracking-wider mb-3">Chi tiết dịch vụ</p>
            <div className="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
              {items.map((item, idx) => (
                <div
                  key={item.id}
                  className={`p-4 flex justify-between items-center ${idx !== items.length - 1 ? 'border-b border-gray-200 border-dashed' : ''
                    }`}
                >
                  <div className="flex items-start gap-3">
                    <div className="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-emerald-600 shadow-sm font-bold text-xs">
                      {idx + 1}
                    </div>

                    <div>
                      <p className="font-bold text-gray-800">
                        {item.booking?.court?.name}
                      </p>
                      <div className="text-xs text-gray-500 mt-1 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                        <span>
                          <i className="fa-regular fa-calendar mr-1"></i>
                          {item.booking?.date}
                        </span>
                        <span className="hidden sm:inline text-gray-300">|</span>
                        <span>
                          <i className="fa-regular fa-clock mr-1"></i>
                          {item.booking?.time_slot?.start_time?.slice(0, 5)} -
                          {item.booking?.time_slot?.end_time?.slice(0, 5)}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div className="text-right">
                    <p className="font-bold text-gray-800">{formatCurrency(item.unit_price)}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* 3. Tổng tiền */}
          <div className="border-t-2 border-gray-200 border-dashed pt-4">
            <div className="flex justify-between text-sm text-gray-600 mb-2">
              <span>Tạm tính</span>
              <span className="font-medium">{formatCurrency(ticket.subtotal ?? 0)}</span>
            </div>

            {Number(ticket.discount_amount) > 0 && (
              <div className="flex justify-between text-sm text-emerald-600 mb-2">
                <span>Giảm giá (Voucher)</span>
                <span className="font-medium">- {formatCurrency(ticket.discount_amount)}</span>
              </div>
            )}

            <div className="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
              <span className="font-bold text-gray-800 text-lg">Tổng thanh toán</span>
              <span className="font-extrabold text-2xl text-emerald-600">{formatCurrency(ticket.total_amount ?? 0)}</span>
            </div>
          </div>

          {/* 4. Ghi chú */}
          {ticket.notes && (
            <div className="bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-sm text-yellow-800 flex gap-2 items-start">
              <i className="fa-regular fa-note-sticky mt-1"></i>
              <div>
                <span className="font-semibold">Ghi chú:</span> {ticket.notes}
              </div>
            </div>
          )}

        </div>

        {/* === FOOTER PAYMENT === */}
        {ticket.status !== "cancelled" && ticket.status !== "confirmed" && (
          <div className="bg-gray-50 p-6 border-t border-gray-200">
            <h3 className="font-bold text-gray-800 mb-4 flex items-center gap-2">
              <i className="fa-solid fa-credit-card text-emerald-600"></i>
              Chọn phương thức thanh toán
            </h3>

            {/* Danh sách chọn phương thức */}
            <div className="grid grid-cols-2 gap-4 mb-6">
              {/* MoMo Card */}
              <div
                onClick={() => setPaymentMethod('momo')}
                className={`relative group p-4 rounded-xl border-2 transition-all duration-200 flex flex-col items-center gap-3 bg-white cursor-pointer ${paymentMethod === 'momo'
                  ? "border-[#a50064] bg-[#fff0f6] shadow-md"
                  : "border-gray-200 hover:border-[#a50064]/50 hover:shadow-sm"
                  }`}
              >
                <img src="/momo.png" alt="MoMo" className="h-10 object-contain" />
                <span className={`text-sm font-semibold ${paymentMethod === 'momo' ? 'text-[#a50064]' : 'text-gray-500'}`}>Ví MoMo</span>
                {paymentMethod === 'momo' && (
                  <div className="absolute top-2 right-2 text-[#a50064]"><i className="fa-solid fa-circle-check"></i></div>
                )}
              </div>

              {/* VNPay Card */}
              <div
                onClick={() => setPaymentMethod('vnpay')}
                className={`relative group p-4 rounded-xl border-2 transition-all duration-200 flex flex-col items-center gap-3 bg-white cursor-pointer ${paymentMethod === 'vnpay'
                  ? "border-[#005baaff] bg-[#f0f9ff] shadow-md"
                  : "border-gray-200 hover:border-[#005baaff]/50 hover:shadow-sm"
                  }`}
              >
                <img src="/vnpay.png" alt="VNPay" className="h-10 object-contain" />
                <span className={`text-sm font-semibold ${paymentMethod === 'vnpay' ? 'text-[#005baaff]' : 'text-gray-500'}`}>VNPay</span>
                {paymentMethod === 'vnpay' && (
                  <div className="absolute top-2 right-2 text-[#005baaff]"><i className="fa-solid fa-circle-check"></i></div>
                )}
              </div>
            </div>

            {/* Render Nút/Component Thanh toán tương ứng */}
            <div className="animate-fadeIn">
              {paymentMethod === "momo" && (
                // QUAN TRỌNG: Truyền hàm handlePaymentSuccess vào component con
                <PaymentMomo
                  ticket={ticket}
                  onSuccess={handlePaymentSuccess}
                />
              )}

              {paymentMethod === "vnpay" && (
                <PaymentVNPay />
              )}

              {!paymentMethod && (
                <div className="text-center text-sm text-gray-400 italic py-2">
                  Vui lòng chọn 1 phương thức để tiếp tục
                </div>
              )}
            </div>
          </div>
        )}

        {/* Footer Cancelled */}
        {ticket.status === "cancelled" && (
          <div className="bg-red-50 p-4 text-center text-red-600 font-medium border-t border-red-100">
            Đơn hàng này đã bị hủy. Vui lòng đặt lại sân mới.
          </div>
        )}

        {/* Footer Confirmed */}
        {ticket.status === "confirmed" && (
          <div className="bg-emerald-50 p-4 text-center text-emerald-600 font-medium border-t border-emerald-100">
            <i className="fa-solid fa-check-circle mr-2"></i>
            Đơn hàng đã thanh toán thành công. Chúc bạn có trải nghiệm tuyệt vời!
          </div>
        )}

      </div>
    </div>
  );
};

export default Ticket_Detail;