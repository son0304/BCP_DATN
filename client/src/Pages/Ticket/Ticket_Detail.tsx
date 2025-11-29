import { useState } from "react";
import { useParams, Link } from "react-router-dom";
import { useDeleteData, useFetchDataById } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";
import { useNotification } from "../../Components/Notification";

// Import các component thanh toán
import PaymentMomo from "../Payment/PaymentMomo";
import PaymentVNPay from "../Payment/PaymentVNPay";
import PaymentWallet from "../Payment/PaymentWallet";

const Ticket_Detail = () => {
  const [paymentMethod, setPaymentMethod] = useState<string>("");
  const { id } = useParams();
  const { showNotification } = useNotification();


  const { data, isLoading, isError, refetch } = useFetchDataById<Ticket>("ticket", id || "");

  const destroyItem = useDeleteData<Ticket>("item");

  const destroyTicket = useDeleteData<Ticket>("ticket");

  const handleApiError = (error: any, defaultMsg: string) => {
    const serverMessage = error?.response?.data?.message;

    if (serverMessage) {
      showNotification(serverMessage, "error");
    } else {
      showNotification(defaultMsg, "error");
    }
    console.error("Chi tiết lỗi API:", error);
  };

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
      case "canceled":
        return { label: "Đã hủy", class: "bg-red-50 text-red-700 border-red-200", icon: "fa-circle-xmark" };
      default:
        return { label: "Hoàn thành", class: "bg-blue-50 text-blue-700 border-blue-200", icon: "fa-flag-checkered" };
    }
  };

  // --- HANDLER 1: HỦY ITEM LẺ ---
  const handleCancelItem = (itemId: number) => {
    if (window.confirm("Bạn có chắc chắn muốn hủy sân này không?\nTiền sẽ hoàn về ví theo chính sách hoàn hủy.")) {
      destroyItem.mutate(itemId, {
        onSuccess: () => {
          showNotification("Hủy sân thành công.", "success");
          refetch();
        },
        onError: (error: any) => {
          handleApiError(error, "Không thể hủy sân lúc này. Vui lòng thử lại.");
        }
      });
    }
  };

  // --- HANDLER 2: HỦY TOÀN BỘ VÉ ---
  const handleCancelTicket = () => {
    if (!data?.data?.id) return;

    if (window.confirm("CẢNH BÁO: Bạn đang yêu cầu hủy TOÀN BỘ đơn hàng.\n\nSố tiền hoàn lại sẽ phụ thuộc vào thời gian hủy so với giờ chơi.\nBạn có chắc chắn muốn tiếp tục?")) {
      destroyTicket.mutate(data.data.id, {
        onSuccess: () => {
          showNotification("Đã hủy toàn bộ đơn hàng thành công.", "success");
          refetch(); // Load lại để cập nhật trạng thái
        },
        onError: (error: any) => {
          handleApiError(error, "Lỗi khi hủy đơn hàng. Vui lòng thử lại sau.");
        }
      });
    }
  };

  const handlePaymentSuccess = () => {
    refetch();
    setPaymentMethod("");
    showNotification("Thanh toán thành công!", "success");
  };

  // --- RENDER LOADING / ERROR ---
  if (isLoading)
    return (
      <div className="min-h-[80vh] flex flex-col items-center justify-center bg-gray-50">
        <div className="animate-spin rounded-full h-12 w-12 border-4 border-[#10B981] border-t-transparent"></div>
        <p className="mt-4 text-sm text-gray-500 font-medium">Đang tải hóa đơn...</p>
      </div>
    );

  if (isError || !data?.data)
    return (
      <div className="min-h-[80vh] flex flex-col items-center justify-center bg-gray-50 p-6">
        <div className="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center text-red-500 mb-4">
          <i className="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>
        <h2 className="text-lg font-bold text-gray-800">Không tìm thấy đơn hàng</h2>
        <Link to="/" className="mt-4 text-sm font-semibold text-[#10B981] hover:underline flex items-center gap-2">
          <i className="fa-solid fa-arrow-left"></i> Quay về trang chủ
        </Link>
      </div>
    );

  // --- DATA PREPARATION ---
  const ticket = data.data;
  const items = ticket.items ?? [];
  const status = getStatusConfig(ticket.status);
  const isTicketCancelled = ticket.status === 'cancelled';

  return (
    <div className="bg-[#F3F4F6] min-h-screen py-8 px-4 font-sans flex justify-center">
      <div className="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 relative">

        {/* --- Top Decoration --- */}
        <div className={`h-1.5 w-full bg-gradient-to-r ${isTicketCancelled ? 'from-red-400 to-red-600' : 'from-[#10B981] via-teal-500 to-[#059669]'}`}></div>

        {/* === HEADER === */}
        <div className="p-6 md:p-8 text-center border-b border-gray-100 border-dashed">
          <div className={`w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm transform rotate-3
            ${isTicketCancelled ? 'bg-red-50 text-red-500' : 'bg-green-50 text-[#10B981]'}`}>
            <i className="fa-solid fa-file-invoice text-2xl"></i>
          </div>

          <h1 className="text-xl md:text-2xl font-bold text-gray-900 uppercase tracking-tight">Hóa đơn đặt sân</h1>
          <div className="flex items-center justify-center gap-2 mt-2 text-sm text-gray-500">
            <span>Mã vé:</span>
            <span className="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded">#{ticket.id}</span>
          </div>

          <div className="mt-5 flex justify-center">
            <span className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold border ${status.class}`}>
              <i className={`fa-solid ${status.icon}`}></i>
              {status.label}
            </span>
          </div>
        </div>

        {/* === CONTENT BODY === */}
        <div className="p-6 md:p-8 space-y-8">

          {/* 1. Customer Info */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-1">
              <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Khách hàng</p>
              <p className="font-bold text-gray-800 text-sm md:text-base">{ticket.user.name}</p>
              <p className="text-xs text-gray-500 flex items-center gap-2"><i className="fa-solid fa-phone w-3 text-center"></i> {ticket.user.phone}</p>
              <p className="text-xs text-gray-500 flex items-center gap-2"><i className="fa-solid fa-envelope w-3 text-center"></i> {ticket.user.email}</p>
            </div>
            <div className="space-y-1 md:text-right">
              <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Thời gian tạo</p>
              <p className="font-bold text-gray-800 text-sm md:text-base">
                {new Date(ticket.created_at).toLocaleTimeString("vi-VN", { hour: '2-digit', minute: '2-digit' })}
              </p>
              <p className="text-xs text-gray-500">
                {new Date(ticket.created_at).toLocaleDateString("vi-VN", { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
              </p>
            </div>
          </div>

          {/* 2. Items List */}
          <div>
            <div className="flex justify-between items-end mb-3 border-b border-gray-100 pb-2">
              <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Chi tiết dịch vụ</p>
              <span className="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">{items.length} items</span>
            </div>

            <div className="space-y-3">
              {items.map((item, idx) => {
                const isRefunded = item.status === 'refund';
                return (
                  <div key={item.id} className={`group relative flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl border transition-all ${isRefunded ? 'bg-gray-50 border-gray-100 opacity-60' : 'bg-white border-gray-100 hover:border-green-200 hover:shadow-sm'}`}>

                    {/* Left: Info */}
                    <div className="flex items-start gap-3 mb-2 sm:mb-0">
                      <div className={`w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0 ${isRefunded ? 'bg-gray-200 text-gray-400' : 'bg-green-50 text-[#10B981]'}`}>
                        {idx + 1}
                      </div>
                      <div>
                        <p className={`text-sm font-bold ${isRefunded ? 'text-gray-500 line-through' : 'text-gray-800'}`}>
                          {item.booking?.court?.name}
                          {isRefunded && <span className="ml-2 text-[9px] font-bold bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded">ĐÃ HỦY</span>}
                        </p>
                        <div className={`text-xs mt-0.5 flex flex-wrap gap-x-3 gap-y-1 ${isRefunded ? 'text-gray-400' : 'text-gray-500'}`}>
                          <span className="flex items-center gap-1"><i className="fa-regular fa-calendar"></i> {item.booking?.date}</span>
                          <span className="flex items-center gap-1"><i className="fa-regular fa-clock"></i> {item.booking?.time_slot?.start_time?.slice(0, 5)} - {item.booking?.time_slot?.end_time?.slice(0, 5)}</span>
                        </div>
                      </div>
                    </div>

                    {/* Right: Price & Action */}
                    <div className="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center pl-11 sm:pl-0">
                      <p className={`font-bold text-sm ${isRefunded ? 'text-gray-400 italic' : 'text-gray-800'}`}>
                        {isRefunded ? formatCurrency(0) : formatCurrency(item.unit_price)}
                      </p>

                      {!isTicketCancelled && !isRefunded && (
                        <button
                          type="button"
                          onClick={() => handleCancelItem(item.id)}
                          className="mt-0 sm:mt-1 text-[10px] font-bold text-red-500 hover:text-white hover:bg-red-500 px-2 py-0.5 rounded transition-all border border-transparent hover:border-red-500 flex items-center gap-1"
                        >
                          <i className="fa-regular fa-trash-can"></i> Hủy
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* 3. Summary (Billing) */}
          <div className="bg-gray-50/50 rounded-xl p-4 border border-gray-100 border-dashed">
            <div className="space-y-2">
              <div className="flex justify-between text-xs md:text-sm text-gray-500">
                <span>Tổng tiền sân</span>
                <span className="font-medium text-gray-700">{formatCurrency(ticket.subtotal ?? 0)}</span>
              </div>
              {Number(ticket.discount_amount) > 0 && (
                <div className="flex justify-between text-xs md:text-sm text-[#10B981]">
                  <span><i className="fa-solid fa-tag mr-1"></i> Voucher giảm giá</span>
                  <span className="font-bold">- {formatCurrency(ticket.discount_amount)}</span>
                </div>
              )}
            </div>

            <div className="border-t border-gray-200 border-dashed my-3"></div>

            <div className="flex justify-between items-center">
              <span className="text-sm font-bold text-gray-800">Thành tiền</span>
              <span className="text-xl md:text-2xl font-extrabold text-[#F59E0B]">{formatCurrency(ticket.total_amount ?? 0)}</span>
            </div>
          </div>

          {/* 4. Notes */}
          {ticket.notes && (
            <div className="bg-amber-50 p-3 rounded-lg border border-amber-100 text-xs text-amber-800 flex gap-2 items-start">
              <i className="fa-regular fa-note-sticky mt-0.5 text-amber-500"></i>
              <div><span className="font-bold">Ghi chú:</span> {ticket.notes}</div>
            </div>
          )}

          {/* 5. Cancel All Button */}
          {!isTicketCancelled && (
            <div className="flex justify-end">
              <button
                type="button"
                onClick={handleCancelTicket}
                className="text-xs font-bold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition-all flex items-center gap-2 border border-transparent hover:border-red-100"
              >
                <i className="fa-solid fa-ban"></i> Hủy toàn bộ đơn hàng
              </button>
            </div>
          )}
        </div>

        {/* === FOOTER PAYMENT SECTION === */}
        {!isTicketCancelled && ticket.status !== "confirmed" && (
          <div className="bg-gray-50 p-6 md:p-8 border-t border-gray-200">
            <h3 className="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide flex items-center gap-2">
              <i className="fa-regular fa-credit-card text-[#10B981]"></i> Phương thức thanh toán
            </h3>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
              {/* MoMo */}
              <div
                onClick={() => setPaymentMethod('momo')}
                className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'momo'
                  ? "border-[#D82D8B] bg-[#FFF0F6] shadow-md"
                  : "border-gray-200 bg-white hover:border-gray-300"
                  }`}
              >
                <img src="/momo.png" alt="MoMo" className="h-8 w-8 object-contain rounded" />
                <span className={`text-xs font-bold ${paymentMethod === 'momo' ? 'text-[#D82D8B]' : 'text-gray-600'}`}>Ví MoMo</span>
                {paymentMethod === 'momo' && <div className="absolute top-2 right-2 text-[#D82D8B] text-xs"><i className="fa-solid fa-check-circle"></i></div>}
              </div>

              {/* VNPay */}
              <div
                onClick={() => setPaymentMethod('vnpay')}
                className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'vnpay'
                  ? "border-[#005BAA] bg-[#F0F9FF] shadow-md"
                  : "border-gray-200 bg-white hover:border-gray-300"
                  }`}
              >
                <img src="/vnpay.png" alt="VNPay" className="h-12 w-12 object-contain rounded" />
                <span className={`text-xs font-bold ${paymentMethod === 'vnpay' ? 'text-[#005BAA]' : 'text-gray-600'}`}>VNPay</span>
                {paymentMethod === 'vnpay' && <div className="absolute top-2 right-2 text-[#005BAA] text-xs"><i className="fa-solid fa-check-circle"></i></div>}
              </div>

              {/* Wallet */}
              <div
                onClick={() => setPaymentMethod('wallet')}
                className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'wallet'
                  ? "border-[#10B981] bg-[#ECFDF5] shadow-md"
                  : "border-gray-200 bg-white hover:border-gray-300"
                  }`}
              >
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${paymentMethod === 'wallet' ? 'bg-[#10B981] text-white' : 'bg-gray-100 text-gray-400'}`}>
                  <i className="fa-solid fa-wallet"></i>
                </div>
                <div className="text-left sm:text-center">
                  <span className={`block text-xs font-bold ${paymentMethod === 'wallet' ? 'text-[#10B981]' : 'text-gray-600'}`}>Ví của tôi</span>
                  <span className="block text-[10px] text-gray-400">Số dư: {formatCurrency(ticket?.user?.wallet?.balance ?? 0)}</span>
                </div>
                {paymentMethod === 'wallet' && <div className="absolute top-2 right-2 text-[#10B981] text-xs"><i className="fa-solid fa-check-circle"></i></div>}
              </div>
            </div>

            <div className="animate-fade-in">
              {paymentMethod === "momo" && <PaymentMomo ticket={ticket} onSuccess={handlePaymentSuccess} />}
              {paymentMethod === "vnpay" && <PaymentVNPay />}
              {paymentMethod === "wallet" && <PaymentWallet ticket={ticket} onSuccess={handlePaymentSuccess} />}

              {!paymentMethod && (
                <div className="p-3 bg-gray-100 rounded-lg text-center text-xs text-gray-500 italic">
                  Vui lòng chọn 1 phương thức thanh toán để tiếp tục
                </div>
              )}
            </div>
          </div>
        )}

        {/* Footer Info (Cancelled / Confirmed) */}
        {isTicketCancelled && (
          <div className="bg-red-50 p-6 text-center border-t border-red-100">
            <i className="fa-solid fa-circle-xmark text-3xl text-red-400 mb-2"></i>
            <h3 className="text-red-700 font-bold">Đơn hàng đã hủy</h3>
            <p className="text-red-500 text-xs mt-1">Số tiền đã thanh toán (nếu có) sẽ được hoàn về ví theo chính sách.</p>
            <Link to="/" className="inline-block mt-4 text-xs font-bold text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition">
              Đặt sân khác
            </Link>
          </div>
        )}

        {ticket.status === "confirmed" && (
          <div className="bg-green-50 p-6 text-center border-t border-green-100">
            <i className="fa-solid fa-circle-check text-3xl text-green-500 mb-2"></i>
            <h3 className="text-green-800 font-bold">Thanh toán hoàn tất</h3>
            <p className="text-green-600 text-xs mt-1">Cảm ơn bạn đã đặt sân. Chúc bạn có những phút giây thể thao tuyệt vời!</p>
            <Link to="/" className="inline-block mt-4 text-xs font-bold text-white bg-[#10B981] hover:bg-[#059669] px-4 py-2 rounded-lg transition">
              Về trang chủ
            </Link>
          </div>
        )}
      </div>
    </div>
  );
};

export default Ticket_Detail;