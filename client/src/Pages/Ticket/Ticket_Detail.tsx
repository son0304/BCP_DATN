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
  };

  const formatCurrency = (value: string | number) => {
    if (!value) return "0₫";
    return Number(value).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "₫";
  };

  // --- HÀM TÍNH TOÁN DỰ KIẾN HOÀN TIỀN ---
  const getRefundInfo = (item: any) => {
    if (!item.booking) return { rate: 1, label: "Hoàn 100% (Dịch vụ)" };

    const now = new Date();
    const bookingTime = new Date(`${item.booking.date} ${item.booking.time_slot.start_time}`);
    const diffInMs = bookingTime.getTime() - now.getTime();
    const diffInHours = diffInMs / (1000 * 60 * 60);

    if (diffInHours < 2) return { rate: 0, label: "Phạt 100% (Hủy sát giờ < 2h)" };
    if (diffInHours >= 24) return { rate: 1, label: "Hoàn 100% (Hủy sớm > 24h)" };
    return { rate: 0.5, label: "Hoàn 50% (Hủy từ 2h - 24h)" };
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case "pending":
        return { label: "Chờ thanh toán", class: "bg-yellow-50 text-yellow-700 border-yellow-200", icon: "fa-hourglass-half" };
      case "confirmed":
        return { label: "Đã thanh toán", class: "bg-emerald-50 text-emerald-700 border-emerald-200", icon: "fa-circle-check" };
      case "checkin":
        return { label: "Đang sử dụng", class: "bg-purple-50 text-purple-700 border-purple-200", icon: "fa-street-view" };
      case "cancelled":
      case "canceled":
        return { label: "Đã hủy", class: "bg-red-50 text-red-700 border-red-200", icon: "fa-circle-xmark" };
      default:
        return { label: "Hoàn thành", class: "bg-blue-50 text-blue-700 border-blue-200", icon: "fa-flag-checkered" };
    }
  };

  // --- HANDLER: HỦY ITEM LẺ ---
  const handleCancelItem = (item: any) => {
    const info = getRefundInfo(item);
    const itemTotal = Number(item.unit_price) * (item.quantity || 1);
    const refundAmount = itemTotal * info.rate;

    const confirmMsg = `XÁC NHẬN HỦY MỤC NÀY:\n` +
      `---------------------------------\n` +
      `• Chính sách: ${info.label}\n` +
      `• Số tiền hoàn lại ví: ${formatCurrency(refundAmount)}\n` +
      `---------------------------------\n` +
      `Lưu ý: Nếu đơn hàng có Voucher, hệ thống sẽ tính toán lại mức giảm dựa trên các mục còn lại. Bạn có chắc chắn muốn hủy?`;

    if (window.confirm(confirmMsg)) {
      destroyItem.mutate(item.id, {
        onSuccess: () => {
          showNotification("Hủy thành công. Tiền đã được hoàn về ví.", "success");
          refetch();
        },
        onError: (error: any) => {
          handleApiError(error, "Không thể hủy lúc này.");
        }
      });
    }
  };

  // --- HANDLER: HỦY TOÀN BỘ VÉ ---
  const handleCancelTicket = () => {
    if (!data?.data) return;
    const ticket = data.data;
    let totalEstimatedRefund = 0;

    ticket.items?.forEach((item: any) => {
      if (item.status !== 'refund') {
        const info = getRefundInfo(item);
        totalEstimatedRefund += (Number(item.unit_price) * (item.quantity || 1)) * info.rate;
      }
    });

    const finalRefund = Math.max(0, totalEstimatedRefund - Number(ticket.discount_amount || 0));

    const confirmMsg = `CẢNH BÁO: HỦY TOÀN BỘ ĐƠN HÀNG\n` +
      `---------------------------------\n` +
      `• Tổng tiền dự kiến hoàn: ${formatCurrency(finalRefund)}\n` +
      `---------------------------------\n` +
      `Hành động này không thể hoàn tác. Bạn có chắc chắn muốn tiếp tục?`;

    if (window.confirm(confirmMsg)) {
      destroyTicket.mutate(ticket.id, {
        onSuccess: () => {
          showNotification("Đã hủy toàn bộ đơn hàng thành công.", "success");
          refetch();
        },
        onError: (error: any) => {
          handleApiError(error, "Lỗi khi hủy đơn hàng.");
        }
      });
    }
  };

  const handlePaymentSuccess = () => {
    refetch();
    setPaymentMethod("");
    showNotification("Thanh toán thành công!", "success");
  };

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

  const ticket = data.data;
  const items = ticket.items ?? [];
  const status = getStatusConfig(ticket.status);

  const isTicketCancelled = ticket.status === 'cancelled';
  const isTicketCompleted = ticket.status === 'completed';
  const isTicketPending = ticket.status === 'pending';
  const isTicketCheckin = ticket.status === 'checkin';

  let venueInfo = null;
  const firstBooking = items.find(i => i.booking);
  if (firstBooking?.booking?.court?.venue) {
    venueInfo = firstBooking.booking.court.venue;
  }

  let headerGradient = 'from-[#10B981] via-teal-500 to-[#059669]';
  if (isTicketCancelled) headerGradient = 'from-red-400 to-red-600';
  else if (isTicketPending) headerGradient = 'from-yellow-400 to-orange-500';
  else if (isTicketCompleted) headerGradient = 'from-blue-400 to-blue-600';
  else if (isTicketCheckin) headerGradient = 'from-purple-500 to-indigo-600';

  return (
    <div className="bg-[#F3F4F6] min-h-screen py-8 px-4 font-sans flex justify-center">
      <div className="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 relative">

        <div className={`h-1.5 w-full bg-gradient-to-r ${headerGradient}`}></div>

        <div className="p-6 md:p-8 text-center border-b border-gray-100 border-dashed">
          <div className={`w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm transform rotate-3
            ${isTicketCancelled ? 'bg-red-50 text-red-500' :
              isTicketCheckin ? 'bg-purple-50 text-purple-600' :
                'bg-green-50 text-[#10B981]'}`}>
            <i className="fa-solid fa-file-invoice text-2xl"></i>
          </div>

          <h1 className="text-xl md:text-2xl font-bold text-gray-900 uppercase tracking-tight">Hóa đơn</h1>

          {venueInfo && (
            <h2 className="text-base font-bold text-[#10B981] mt-1 flex items-center justify-center gap-1">
              <i className="fa-solid fa-location-dot text-xs"></i> {venueInfo.name}
            </h2>
          )}

          <div className="flex items-center justify-center gap-2 mt-2 text-sm text-gray-500">
            <span>Mã vé - Checkin:</span>
            <span className="font-mono font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded">#{ticket.booking_code}</span>
          </div>

          <div className="mt-5 flex justify-center">
            <span className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold border ${status.class}`}>
              <i className={`fa-solid ${status.icon}`}></i>
              {status.label}
            </span>
          </div>
        </div>

        <div className="p-6 md:p-8 space-y-8">

          {/* === BOX CHÍNH SÁCH HOÀN TIỀN (BỔ SUNG) === */}
          {!isTicketCancelled && !isTicketCompleted && !isTicketCheckin && (
            <div className="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl">
              <div className="flex items-center gap-2 mb-1">
                <i className="fa-solid fa-circle-info text-blue-500 text-xs"></i>
                <span className="text-[11px] uppercase font-bold text-blue-800 tracking-wider">Lưu ý hoàn tiền về ví:</span>
              </div>
              <ul className="text-[11px] text-blue-700 space-y-1 ml-4 list-disc font-medium">
                <li>Sân bóng: Trước 24h (Hoàn 100%), 2h - 24h (Hoàn 50%), dưới 2h (Phạt 100%).</li>
                <li>Dịch vụ: Hoàn 100% nếu mục đó chưa được sử dụng/check-in.</li>
                <li>Voucher: Hệ thống sẽ tính lại mức giảm giá dựa trên đơn hàng mới khi hủy lẻ.</li>
              </ul>
            </div>
          )}

          {/* 1. Customer Info */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-1">
              <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Khách hàng</p>
              <p className="font-bold text-gray-800 text-sm md:text-base">{ticket.user.name}</p>
              <p className="text-xs text-gray-500 flex items-center gap-2"><i className="fa-solid fa-phone w-3 text-center"></i> {ticket.user.phone}</p>
              <p className="text-xs text-gray-500 flex items-center gap-2"><i className="fa-solid fa-envelope w-3 text-center"></i> {ticket.user.email}</p>
            </div>

            <div className="space-y-4 md:text-right">
              <div className="space-y-1">
                <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Thời gian tạo</p>
                <p className="font-bold text-gray-800 text-sm md:text-base">
                  {new Date(ticket.created_at).toLocaleTimeString("vi-VN", { hour: '2-digit', minute: '2-digit' })}
                </p>
                <p className="text-xs text-gray-500">
                  {new Date(ticket.created_at).toLocaleDateString("vi-VN", { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                </p>
              </div>
            </div>
          </div>

          {/* 2. Items List */}
          <div>
            <div className="flex justify-between items-end mb-3 border-b border-gray-100 pb-2">
              <p className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Chi tiết hóa đơn</p>
              <span className="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">{items.length} mục</span>
            </div>

            <div className="space-y-3">
              {items.map((item, idx) => {
                const isRefunded = item.status === 'refund';
                const isBooking = !!item.booking;
                const isService = !!item.venue_service;

                let itemName = "Sản phẩm không xác định";
                let itemSubInfo = null;
                let itemImage = null;

                if (isBooking) {
                  itemName = item.booking?.court?.name || "Sân bóng";
                  itemSubInfo = (
                    <>
                      <span className="flex items-center gap-1"><i className="fa-regular fa-calendar"></i> {item.booking?.date}</span>
                      <span className="flex items-center gap-1"><i className="fa-regular fa-clock"></i> {item.booking?.time_slot?.start_time?.slice(0, 5)} - {item.booking?.time_slot?.end_time?.slice(0, 5)}</span>
                    </>
                  );
                } else if (isService) {
                  const serviceInfo = item.venue_service?.service;
                  itemName = serviceInfo?.name || "Dịch vụ";
                  if (serviceInfo?.images && serviceInfo.images.length > 0) {
                    itemImage = serviceInfo.images[0].url;
                  }
                  itemSubInfo = (
                    <span className="flex items-center gap-1 text-blue-500 font-medium">
                      <i className="fa-solid fa-cubes"></i> Số lượng: {item.quantity}  {serviceInfo?.unit}
                    </span>
                  );
                }

                return (
                  <div key={item.id} className={`group relative flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl border transition-all ${isRefunded ? 'bg-gray-50 border-gray-100 opacity-60' : 'bg-white border-gray-100 hover:border-green-200 hover:shadow-sm'}`}>

                    <div className="flex items-start gap-3 mb-2 sm:mb-0">
                      {itemImage ? (
                        <img src={itemImage} alt={itemName} className="w-10 h-10 rounded-lg object-cover border border-gray-200" />
                      ) : (
                        <div className={`w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0 
                              ${isRefunded ? 'bg-gray-200 text-gray-400' : (isBooking ? 'bg-green-50 text-[#10B981]' : 'bg-blue-50 text-blue-500')}`}>
                          {isBooking ? <i className="fa-regular fa-futbol"></i> : <i className="fa-solid fa-bottle-water"></i>}
                        </div>
                      )}

                      <div>
                        <p className={`text-sm font-bold ${isRefunded ? 'text-gray-500 line-through' : 'text-gray-800'}`}>
                          {itemName}
                          {isRefunded && <span className="ml-2 text-[9px] font-bold bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded">ĐÃ HỦY</span>}
                        </p>
                        <div className={`text-xs mt-0.5 flex flex-wrap gap-x-3 gap-y-1 ${isRefunded ? 'text-gray-400' : 'text-gray-500'}`}>
                          {itemSubInfo}
                        </div>
                      </div>
                    </div>

                    <div className="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center pl-14 sm:pl-0">
                      <div className={`text-right ${isRefunded ? 'opacity-60' : ''}`}>
                        {isRefunded ? (
                          <p className="font-bold text-sm text-gray-400 italic">{formatCurrency(0)}</p>
                        ) : (
                          <div className="flex flex-col items-end leading-tight">
                            {!isBooking && item.quantity > 1 && (
                              <span className="text-[10px] text-gray-400 mb-0.5">{formatCurrency(item.unit_price)} x {item.quantity}</span>
                            )}
                            {Number(item.discount_amount) > 0 ? (
                              <>
                                <span className="text-xs text-gray-400 line-through decoration-1">
                                  {formatCurrency(Number(item.unit_price) * (isBooking ? 1 : item.quantity))}
                                </span>
                                <span className="font-bold text-sm text-red-500">
                                  {formatCurrency((Number(item.unit_price) * (isBooking ? 1 : item.quantity)) - Number(item.discount_amount))}
                                </span>
                              </>
                            ) : (
                              <p className="font-bold text-sm text-gray-800">
                                {formatCurrency(Number(item.unit_price) * (isBooking ? 1 : item.quantity))}
                              </p>
                            )}
                          </div>
                        )}
                      </div>

                      {!isTicketCancelled && !isTicketCompleted && !isTicketCheckin && !isRefunded && (
                        <button
                          type="button"
                          onClick={() => handleCancelItem(item)}
                          className="mt-0 sm:mt-1 text-[10px] font-bold text-red-500 hover:text-white hover:bg-red-500 px-2 py-0.5 rounded transition-all border border-transparent hover:border-red-500 flex items-center gap-1"
                        >
                          <i className="fa-regular fa-trash-can"></i> Hủy & Hoàn tiền
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
                <span>Tổng tạm tính</span>
                <span className="font-medium text-gray-700">{formatCurrency(ticket.subtotal ?? 0)}</span>
              </div>
              {Number(ticket.discount_amount) > 0 && (
                <div className="flex justify-between text-xs md:text-sm text-[#10B981]">
                  <span><i className="fa-solid fa-tag mr-1"></i> Giảm giá</span>
                  <span className="font-bold">- {formatCurrency(ticket.discount_amount)}</span>
                </div>
              )}
            </div>
            <div className="border-t border-gray-200 border-dashed my-3"></div>
            <div className="flex justify-between items-center">
              <span className="text-sm font-bold text-gray-800">Tổng thanh toán</span>
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

          {!isTicketCancelled && !isTicketCompleted && !isTicketCheckin && (
            <div className="flex justify-end">
              <button
                type="button"
                onClick={handleCancelTicket}
                className="text-xs font-bold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-2 rounded-lg transition-all flex items-center gap-2 border border-transparent hover:border-red-100"
              >
                <i className="fa-solid fa-ban"></i> Hủy đơn hàng
              </button>
            </div>
          )}
        </div>

        {/* === PAYMENT === */}
        {!isTicketCancelled && ticket.status == "pending" && (
          <div className="bg-gray-50 p-6 md:p-8 border-t border-gray-200">
            <h3 className="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide flex items-center gap-2">
              <i className="fa-regular fa-credit-card text-[#10B981]"></i> Phương thức thanh toán
            </h3>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
              <div onClick={() => setPaymentMethod('momo')} className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'momo' ? "border-[#D82D8B] bg-[#FFF0F6] shadow-md" : "border-gray-200 bg-white hover:border-gray-300"}`}>
                <img src="/momo.png" alt="MoMo" className="h-8 w-8 object-contain rounded" />
                <span className={`text-xs font-bold ${paymentMethod === 'momo' ? 'text-[#D82D8B]' : 'text-gray-600'}`}>Ví MoMo</span>
              </div>

              <div onClick={() => setPaymentMethod('vnpay')} className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'vnpay' ? "border-[#005BAA] bg-[#F0F9FF] shadow-md" : "border-gray-200 bg-white hover:border-gray-300"}`}>
                <img src="/vnpay.png" alt="VNPay" className="h-12 w-12 object-contain rounded" />
                <span className={`text-xs font-bold ${paymentMethod === 'vnpay' ? 'text-[#005BAA]' : 'text-gray-600'}`}>VNPay</span>
              </div>

              <div onClick={() => setPaymentMethod('wallet')} className={`relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center sm:flex-col sm:justify-center gap-3 ${paymentMethod === 'wallet' ? "border-[#10B981] bg-[#ECFDF5] shadow-md" : "border-gray-200 bg-white hover:border-gray-300"}`}>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm ${paymentMethod === 'wallet' ? 'bg-[#10B981] text-white' : 'bg-gray-100 text-gray-400'}`}>
                  <i className="fa-solid fa-wallet"></i>
                </div>
                <div className="text-left sm:text-center">
                  <span className={`block text-xs font-bold ${paymentMethod === 'wallet' ? 'text-[#10B981]' : 'text-gray-600'}`}>Ví của tôi</span>
                  <span className="block text-[10px] text-gray-400">Số dư: {formatCurrency(ticket?.user?.wallet?.balance ?? 0)}</span>
                </div>
              </div>
            </div>

            <div className="animate-fade-in">
              {paymentMethod === "momo" && <PaymentMomo ticket={ticket} onSuccess={handlePaymentSuccess} />}
              {paymentMethod === "vnpay" && <PaymentVNPay />}
              {paymentMethod === "wallet" && <PaymentWallet ticket={ticket} onSuccess={handlePaymentSuccess} />}
            </div>
          </div>
        )}

        {/* --- FOOTER STATUS --- */}
        {isTicketCancelled && (
          <div className="bg-red-50 p-6 text-center border-t border-red-100">
            <i className="fa-solid fa-circle-xmark text-3xl text-red-400 mb-2"></i>
            <h3 className="text-red-700 font-bold">Đơn hàng đã hủy</h3>
          </div>
        )}
        {ticket.status === "confirmed" && (
          <div className="bg-green-50 p-6 text-center border-t border-green-100">
            <i className="fa-solid fa-circle-check text-3xl text-green-500 mb-2"></i>
            <h3 className="text-green-800 font-bold">Thanh toán hoàn tất</h3>
          </div>
        )}
        {isTicketCheckin && (
          <div className="bg-purple-50 p-6 text-center border-t border-purple-100">
            <i className="fa-solid fa-person-running text-3xl text-purple-600 mb-2"></i>
            <h3 className="text-purple-800 font-bold text-lg">Đang sử dụng dịch vụ</h3>
          </div>
        )}
        {isTicketCompleted && (
          <div className="bg-blue-50 p-6 text-center border-t border-blue-100">
            <i className="fa-solid fa-medal text-3xl text-blue-500 mb-2"></i>
            <h3 className="text-blue-800 font-bold text-lg">Đơn hàng đã hoàn thành</h3>
          </div>
        )}
      </div>
    </div>
  );
};

export default Ticket_Detail;