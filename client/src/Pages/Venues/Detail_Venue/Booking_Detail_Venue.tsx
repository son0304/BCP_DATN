import React, { useEffect, useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import type { QueryObserverResult, RefetchOptions } from '@tanstack/react-query';
import { useNotification } from '../../../Components/Notification';
import { usePostData } from '../../../Hooks/useApi';
import type { Venue } from '../../../Types/venue';
import type { User } from '../../../Types/user';
import type { ApiResponse } from '../../../Types/api';
import Voucher_Detail_Venue from './Voucher_Detail_Venue';

// --- TYPES ---
type VenueSlot = {
  id: number;
  time_slot_id?: number;
  start_time: string;
  end_time: string;
  price: number | string;
  status?: 'open' | 'booked' | 'closed' | 'maintenance';
  sale_price: number | string | null; // Cập nhật type allow null
  flash_status: "active" | "sold_out" | "inactive";
  quantity: number;
  sold_count: number;
};

type SelectedItem = {
  court_id: number;
  court_name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
  sale_price: number;
};

export type Voucher = {
  id: number;
  code: string;
  value: number;
  type: '%' | 'VND';
  start_at: string;
  expires_at: string | null;
  max_discount_amount: number | null;
};

type BookingDetailVenueProps = {
  venue: Venue;
  user: User;
  refetch: (options?: RefetchOptions) => Promise<QueryObserverResult<ApiResponse<Venue>, Error>>;
  selectedDate: string;
  setSelectedDate: React.Dispatch<React.SetStateAction<string>>;
};

const Booking_Detail_Venue: React.FC<BookingDetailVenueProps> = ({
  venue,
  user,
  refetch,
  selectedDate,
  setSelectedDate,
}) => {
  const [activeCourtId, setActiveCourtId] = useState<number | null>(null);
  const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);

  const [selectedVoucher, setSelectedVoucher] = useState<Voucher | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const { showNotification } = useNotification();
  const { mutate } = usePostData<ApiResponse<number>, any>('tickets');
  const navigate = useNavigate();

  const courts = venue.courts ?? [];
  console.log(courts);


  useEffect(() => {
    if (courts.length > 0 && activeCourtId === null) {
      setActiveCourtId(courts[0].id);
    }
  }, [courts, activeCourtId]);

  useEffect(() => {
    refetch();
    setSelectedItems([]);
    setSelectedVoucher(null);
  }, [selectedDate, refetch]);

  const activeCourtSlots = useMemo(() => {
    if (!activeCourtId) return [];
    const court = courts.find((c) => c.id === activeCourtId);
    // @ts-ignore
    const rawSlots: VenueSlot[] = court?.time_slots || court?.availabilities || [];
    return rawSlots.sort((a, b) => a.start_time.localeCompare(b.start_time));
  }, [courts, activeCourtId]);

  // --- CẬP NHẬT LOGIC TÍNH TIỀN ---
  const { rawTotalPrice, finalPrice, discountAmount } = useMemo(() => {
    // Tính tổng tiền dựa trên giá thực tế (Nếu có sale thì lấy sale, không thì lấy price)
    const total = selectedItems.reduce((sum, item) => {
      const effectivePrice = (item.sale_price && item.sale_price > 0) ? item.sale_price : item.price;
      return sum + effectivePrice;
    }, 0);

    let discount = 0;
    if (selectedVoucher) {
      const now = new Date();
      if (!selectedVoucher.expires_at || new Date(selectedVoucher.expires_at) >= now) {
        if (selectedVoucher.type === '%') {
          discount = (total * selectedVoucher.value) / 100;
          if (selectedVoucher.max_discount_amount && discount > selectedVoucher.max_discount_amount) {
            discount = selectedVoucher.max_discount_amount;
          }
        } else {
          discount = selectedVoucher.value;
        }
      }
    }
    const validDiscount = Math.min(discount, total);
    return {
      rawTotalPrice: total,
      discountAmount: validDiscount,
      finalPrice: Math.max(0, total - validDiscount)
    };
  }, [selectedItems, selectedVoucher]);

  const formatPrice = (price: number | string) =>
    Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });

  const isSlotPast = (dateStr: string, timeStr: string) => {
    const slotDate = new Date(`${dateStr}T${timeStr}`);
    const now = new Date();
    return slotDate < now;
  };

  const handleToggleSlot = (court: any, slot: VenueSlot) => {
    const slotUniqueId = slot.time_slot_id || slot.id;

    if (slot.status && slot.status !== 'open') {
      const msgMap: Record<string, string> = {
        booked: 'Sân đã có người đặt.',
        maintenance: 'Sân đang bảo trì.',
        closed: 'Sân đóng cửa.',
      };
      return showNotification(msgMap[slot.status] || 'Khung giờ này không khả dụng.', 'error');
    }

    if (isSlotPast(selectedDate, slot.start_time)) {
      return showNotification('Khung giờ này đã trôi qua.', 'error');
    }

    const isSelected = selectedItems.some(
      (item) => item.court_id === court.id && item.time_slot_id === slotUniqueId
    );

    if (isSelected) {
      setSelectedItems((prev) =>
        prev.filter((i) => !(i.court_id === court.id && i.time_slot_id === slotUniqueId))
      );
    } else {
      setSelectedItems((prev) => [
        ...prev,
        {
          court_id: court.id,
          court_name: court.name,
          time_slot_id: slotUniqueId,
          start_time: slot.start_time,
          end_time: slot.end_time,
          date: selectedDate,
          price: Number(slot.price),
          sale_price: Number(slot.sale_price || 0), // Đảm bảo không null
        },
      ]);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) return showNotification('Vui lòng đăng nhập để đặt sân.', 'error');
    if (selectedItems.length === 0) return showNotification('Vui lòng chọn ít nhất 1 khung giờ.', 'error');

    const hasPastItem = selectedItems.some(item => isSlotPast(item.date, item.start_time));
    if (hasPastItem) return showNotification('Có khung giờ đã quá hạn, vui lòng tải lại trang.', 'error');

    setIsSubmitting(true);

    const payload = {
      user_id: user.id,
      venue_id: venue.id,
      promotion_id: selectedVoucher?.id || null,
      discount_amount: discountAmount,
      total_amount: finalPrice,
      bookings: selectedItems.map((item) => ({
        court_id: item.court_id,
        time_slot_id: item.time_slot_id,
        date: item.date,
        unit_price: item.price,
        sale_price: item.sale_price,
      })),
    };

    mutate(payload, {
      onSuccess: (res) => {
        if (res.success) {
          showNotification('Đặt sân thành công!', 'success');
          navigate(`/booking/${res.data}`);
          refetch();
        } else {
          showNotification(res.message || 'Đặt sân thất bại.', 'error');
          refetch();
        }
      },
      onError: () => {
        showNotification('Lỗi kết nối server.', 'error');
      },
      onSettled: () => setIsSubmitting(false),
    });
  };

  return (
    <div className="lg:col-span-3 order-1 lg:order-2 h-full">
      <div className="bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden lg:sticky lg:top-24 flex flex-col h-full">

        {/* COMPACT HEADER */}
        <div className="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 px-4 py-3 text-white flex justify-between items-center">
          <h4 className="text-sm font-bold flex items-center gap-2 uppercase tracking-wide">
            <i className="fa-solid fa-calendar-check text-[#10B981]"></i> Đặt Lịch Online
          </h4>
          <span className="text-[10px] bg-white/10 px-2 py-0.5 rounded text-gray-300">
            {selectedItems.length} slot đang chọn
          </span>
        </div>

        <div className="p-4 space-y-5 flex-1 overflow-y-auto custom-scrollbar">

          {/* 1. DATE PICKER & LEGEND */}
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div className="relative w-full sm:w-auto">
              <input
                type="date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                min={new Date().toISOString().slice(0, 10)}
                className="w-full sm:w-48 pl-3 pr-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-[#10B981] outline-none font-medium text-gray-700 cursor-pointer"
              />
            </div>
            <div className="flex gap-3 text-[10px] text-gray-500">
              <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-white border border-gray-300"></span> Trống</div>
              <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#10B981]"></span> Chọn</div>
              <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-gray-200"></span> Kín</div>
            </div>
          </div>

          {/* 2. COURT TABS (SCROLLABLE) */}
          <div className="border-b border-gray-100">
            <div className="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
              {courts.map((court) => (
                <button
                  key={court.id}
                  onClick={() => setActiveCourtId(court.id)}
                  className={`px-3 py-1.5 rounded-md text-xs font-bold whitespace-nowrap transition-all ${activeCourtId === court.id
                    ? 'bg-[#10B981] text-white shadow-sm'
                    : 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                    }`}
                >
                  {court.name}
                </button>
              ))}
            </div>
          </div>

          {/* 3. SLOTS GRID (Compact Buttons Updated) */}
          <div className="min-h-[200px]">
            <div className="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-6 gap-2">
              {activeCourtSlots.length === 0 ? (
                <div className="col-span-full text-center py-10 text-gray-400 text-xs flex flex-col items-center">
                  <i className="fa-regular fa-calendar-xmark text-2xl mb-2 opacity-30"></i>
                  <span>Không có lịch trống cho sân này</span>
                </div>
              ) : (
                activeCourtSlots.map((slot) => {
                  const slotUniqueId = slot.time_slot_id || slot.id;
                  const isSelected = selectedItems.some(
                    (item) => item.court_id === activeCourtId && item.time_slot_id === slotUniqueId
                  );
                  const isPast = isSlotPast(selectedDate, slot.start_time);
                  const isOpen = !slot.status || slot.status === 'open';
                  const isDisabled = !isOpen || isPast;
                  const hasSale = slot.sale_price !== null && Number(slot.sale_price) > 0;

                  // --- BASE STYLE ---
                  // min-h-[65px] min-w-[75px]: Đảm bảo nút to và vuông
                  let btnClass = "relative flex flex-col items-center justify-center gap-1 py-3 px-2 min-h-[65px] min-w-[75px] rounded border transition-all duration-200 ";

                  if (isDisabled) {
                    btnClass += "bg-gray-100 border-gray-100 text-gray-300 cursor-not-allowed";
                  } else if (isSelected) {
                    btnClass += "bg-[#10B981] border-[#10B981] text-white shadow-md ring-2 ring-green-100 cursor-pointer transform -translate-y-0.5";
                  } else {
                    btnClass += "bg-white border-gray-200 text-gray-600 hover:border-[#10B981] hover:text-[#10B981] hover:shadow-sm cursor-pointer";
                  }

                  return (
                    <button
                      key={`${activeCourtId}-${slotUniqueId}`}
                      onClick={() => !isDisabled && handleToggleSlot(courts.find(c => c.id === activeCourtId), slot)}
                      disabled={isDisabled}
                      className={btnClass}
                    >
                      {/* 1. GIỜ: To hơn (text-sm), đậm hơn */}
                      <span className="text-sm font-bold leading-none">
                        {slot.start_time.slice(0, 5)}
                      </span>

                      {/* 2. GIÁ: Bọc trong div cố định chiều cao (h-8) để TRÁNH NHẢY LAYOUT */}
                      <div className="flex flex-col items-center justify-end h-8">
                        {hasSale ? (
                          <>
                            {/* Giá gốc: Nhỏ, gạch ngang, màu nhạt */}
                            <span className={`text-[10px] line-through leading-tight ${isSelected ? 'text-green-100 opacity-80' : 'text-gray-400'}`}>
                              {Number(slot.price) / 1000}k
                            </span>
                            {/* Giá Sale: Đậm, màu đỏ (hoặc trắng nếu đang chọn) */}
                            <span className={`text-xs font-bold leading-tight ${isSelected ? 'text-white' : 'text-red-600'}`}>
                              {Number(slot.sale_price) / 1000}k
                            </span>
                          </>
                        ) : (
                          /* Giá thường: Căn giữa */
                          <span className={`text-xs font-medium ${isSelected ? 'text-white' : 'text-gray-500'}`}>
                            {Number(slot.price) / 1000}k
                          </span>
                        )}
                      </div>
                    </button>
                  );
                })
              )}
            </div>
          </div>

          {/* 4. CHECKOUT SECTION */}
          <div className="bg-gray-50 rounded-xl p-3 border border-gray-200">
            {/* Selected List */}
            {selectedItems.length > 0 ? (
              <div className="max-h-24 overflow-y-auto custom-scrollbar mb-3 pr-1 space-y-1">
                {selectedItems.map((item, idx) => {
                  const effectivePrice = (item.sale_price && item.sale_price > 0) ? item.sale_price : item.price;
                  return (
                    <div key={idx} className="flex justify-between items-center bg-white p-1.5 rounded border border-gray-100 text-xs">
                      <div className="flex items-center gap-1.5">
                        <span className="w-1 h-3 bg-[#10B981] rounded-full"></span>
                        <span className="font-semibold text-gray-700">{item.court_name}</span>
                        <span className="text-gray-500">({item.start_time.slice(0, 5)} - {item.end_time.slice(0, 5)})</span>
                      </div>
                      <div className="flex items-center gap-2">
                        {item.sale_price > 0 && (
                          <span className="text-[10px] text-gray-400 line-through decoration-1">{formatPrice(item.price)}</span>
                        )}
                        <span className={`font-bold ${item.sale_price > 0 ? 'text-red-500' : 'text-gray-800'}`}>
                          {formatPrice(effectivePrice)}
                        </span>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <p className="text-center text-xs text-gray-400 py-2 italic">Chưa chọn khung giờ nào</p>
            )}

            <div className="border-t border-gray-200 my-2"></div>

            {/* Voucher */}
            <Voucher_Detail_Venue onVoucherApply={setSelectedVoucher} totalPrice={rawTotalPrice} />

            <div className="border-t border-gray-200 my-3"></div>

            {/* Total */}
            <div className="flex justify-between items-center mb-3">
              <div className="text-xs text-gray-500">
                <p>Tạm tính: {formatPrice(rawTotalPrice)}</p>
                {selectedVoucher && <p className="text-[#10B981]">Giảm giá: -{formatPrice(discountAmount)}</p>}
              </div>
              <div className="text-right">
                <span className="block text-[10px] text-gray-400">Tổng thanh toán</span>
                <span className="text-lg font-extrabold text-[#F59E0B]">{formatPrice(finalPrice)}</span>
              </div>
            </div>

            {/* Submit Button */}
            <button
              type="submit"
              onClick={handleSubmit}
              disabled={selectedItems.length === 0 || isSubmitting}
              className={`w-full py-2.5 rounded-lg text-sm font-bold text-white shadow-md transition-all flex items-center justify-center gap-2 ${selectedItems.length === 0 || isSubmitting
                ? 'bg-gray-300 cursor-not-allowed shadow-none'
                : 'bg-[#10B981] hover:bg-[#059669] hover:-translate-y-0.5'
                }`}
            >
              {isSubmitting ? (
                <> <i className="fa-solid fa-circle-notch fa-spin"></i> Xử lý... </>
              ) : (
                <> Xác nhận đặt sân <i className="fa-solid fa-arrow-right text-xs"></i> </>
              )}
            </button>
          </div>

        </div>
      </div>
    </div >
  );
};

export default Booking_Detail_Venue;