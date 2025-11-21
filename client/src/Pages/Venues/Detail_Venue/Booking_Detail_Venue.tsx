import React, { useEffect, useState } from 'react';
import { useNotification } from '../../../Components/Notification';
import type { Venue, EnrichedTimeSlot } from '../../../Types/venue';
import type { User } from '../../../Types/user';
import { usePostData } from '../../../Hooks/useApi';
import type { ApiResponse } from '../../../Types/api';
import { useNavigate } from 'react-router-dom';
import type { QueryObserverResult, RefetchOptions } from '@tanstack/react-query';
import Voucher_Detail_Venue from './Voucher_Detail_Venue';

// --- TYPES ---
type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
};

// Type cho Voucher (giữ nguyên nếu chưa có file riêng)
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
  const [selectedPrice, setSelectedPrice] = useState<number>(0);
  const [rawTotalPrice, setRawTotalPrice] = useState<number>(0);
  const [selectedVoucher, setSelectedVoucher] = useState<Voucher | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const { showNotification } = useNotification();
  const { mutate } = usePostData<ApiResponse<number>, any>('tickets');
  const navigate = useNavigate();

  // Lấy danh sách courts từ venue, mặc định là mảng rỗng nếu null
  const courts = venue.courts ?? [];

  const formatPrice = (price: number | string | null) => {
    const value = Number(price) || 0;
    return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
  };

  // --- LOGIC ---
  const calculateDiscount = (voucher: Voucher | null, total: number): number => {
    if (!voucher) return 0;
    const now = new Date();
    if (voucher.expires_at && new Date(voucher.expires_at) < now) return 0;

    let discount = 0;
    if (voucher.type === '%') {
      discount = (total * voucher.value) / 100;
      if (voucher.max_discount_amount && discount > voucher.max_discount_amount) {
        discount = voucher.max_discount_amount;
      }
    } else {
      discount = voucher.value;
    }
    return Math.min(discount, total);
  };

  // Tính tổng và giá cuối cùng khi chọn items hoặc voucher thay đổi
  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price || 0), 0);
    setRawTotalPrice(total);
    const discount = calculateDiscount(selectedVoucher, total);
    setSelectedPrice(Math.max(0, total - discount));
  }, [selectedItems, selectedVoucher]);

  // Reset khi thay đổi ngày
  useEffect(() => {
    refetch();
    setSelectedItems([]);
    setRawTotalPrice(0);
    setSelectedPrice(0);
    setSelectedVoucher(null);
  }, [selectedDate, refetch]);

  // Set court mặc định
  useEffect(() => {
    if (courts.length > 0 && activeCourtId === null) {
      setActiveCourtId(courts[0].id);
    }
  }, [courts, activeCourtId]);

  // --- Chọn / bỏ chọn khung giờ ---
  const handleSelectItem = (clickedItem: SelectedItem, status?: string | null) => {
    if (status !== 'open') {
      const msg =
        status === 'booked'
          ? 'Khung giờ này đã được người khác đặt.'
          : status === 'maintenance'
          ? 'Khung giờ này đang bảo trì.'
          : 'Khung giờ này không khả dụng.';
      return showNotification(msg, 'error');
    }

    // Validate thời gian đã qua
    const selectedDateTime = new Date(`${clickedItem.date}T${clickedItem.start_time}`);
    if (selectedDateTime < new Date()) {
      return showNotification('Khung giờ này đã qua, vui lòng chọn khung giờ khác.', 'error');
    }

    const isSelected = selectedItems.some(
      (item) => item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id
    );

    setSelectedItems((prev) =>
      isSelected
        ? prev.filter((i) => !(i.court_id === clickedItem.court_id && i.time_slot_id === clickedItem.time_slot_id))
        : [...prev, clickedItem]
    );
  };

  // --- Submit form ---
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!user) return showNotification('Vui lòng đăng nhập để đặt sân.', 'error');
    if (selectedItems.length === 0) return showNotification('Vui lòng chọn ít nhất 1 khung giờ.', 'error');

    // Validate khung giờ hiện tại trước submit
    for (const item of selectedItems) {
      const itemTime = new Date(`${item.date}T${item.start_time}`);
      if (itemTime < new Date()) {
        return showNotification(
          `Khung giờ ${item.start_time} - ${item.end_time} của ${item.name} đã qua, vui lòng chọn lại.`,
          'error'
        );
      }
    }

    const bookings = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: item.price,
    }));

    const total = selectedItems.reduce((sum, item) => sum + item.price, 0);
    const discount = calculateDiscount(selectedVoucher, total);

    setIsSubmitting(true);

    mutate(
      { user_id: user.id, promotion_id: selectedVoucher?.id || null, discount_amount: discount, bookings },
      {
        onSuccess: (res) => {
          if (res.success) {
            showNotification('Đặt sân thành công!', 'success');
            navigate(`/booking/${res.data}`);
            refetch(); // gọi lại API component cha
          } else {
            showNotification(res.message || 'Đặt sân thất bại.', 'error');
            refetch();
          }
        },
        onError: () => showNotification('Lỗi khi đặt sân.', 'error'),
        onSettled: () => setIsSubmitting(false),
      }
    );
  };

  // --- RENDER ---
  return (
    <div className="lg:col-span-2 order-1 lg:order-2">
      <div className="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden lg:sticky lg:top-24">
        {/* Header */}
        <div className="bg-gradient-to-r from-emerald-600 to-teal-500 p-4 text-white">
          <h4 className="text-lg font-bold flex items-center gap-2">
            <i className="fa-solid fa-calendar-check"></i>
            Đặt Lịch Ngay
          </h4>
          <p className="text-emerald-100 text-xs mt-1">Chọn ngày giờ phù hợp với bạn</p>
        </div>

        <div className="p-5 space-y-6">
          {/* Chọn ngày */}
          <div className="relative">
            <label className="block text-sm font-semibold text-gray-700 mb-2">Ngày thi đấu</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i className="fa-regular fa-calendar text-gray-400"></i>
              </div>
              <input 
                type="date" 
                value={selectedDate} 
                onChange={(e) => setSelectedDate(e.target.value)} 
                min={new Date().toISOString().slice(0, 10)}
                className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-gray-700 font-medium transition-all bg-gray-50"
              />
            </div>
          </div>

          {/* Chọn sân */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-2">Chọn sân</label>
            <div className="flex flex-wrap gap-2">
              {courts.map((court) => (
                <button 
                  key={court.id} 
                  onClick={() => setActiveCourtId(court.id)}
                  className={`px-4 py-2 rounded-lg text-sm font-medium border transition-all duration-200 flex items-center gap-2 ${
                    activeCourtId === court.id
                      ? 'bg-emerald-600 text-white border-emerald-600 shadow-md transform -translate-y-0.5'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-emerald-300'
                  }`}
                >
                  <i className={`fa-solid ${activeCourtId === court.id ? 'fa-check-circle' : 'fa-circle'} text-xs opacity-80`}></i>
                  {court.name}
                </button>
              ))}
            </div>
          </div>

          {/* Grid khung giờ */}
          <div>
            <div className="flex justify-between items-end mb-3">
              <label className="text-sm font-semibold text-gray-700">
                Khung giờ ({selectedItems.length} đang chọn)
              </label>
              <div className="flex gap-3 text-[10px] text-gray-500">
                <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-emerald-100 border border-emerald-500"></span>Trống</div>
                <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-emerald-600"></span>Chọn</div>
                <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-gray-200"></span>Đã đặt</div>
              </div>
            </div>
            <div className="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[300px] overflow-y-auto pr-1 custom-scrollbar">
              {courts
                .filter((c) => c.id === activeCourtId)
                .flatMap((court: any) => court.time_slots?.map((time: any) => ({ court, time })) ?? [])
                .map(({ court, time }: any) => {
                  const isSelected = selectedItems.some(
                    (it) => it.court_id === court.id && it.time_slot_id === time.id
                  );

                  const nowTime = new Date();
                  const isPast = new Date(`${selectedDate}T${time.start_time}`) < nowTime;

                  let baseClass = "relative p-2 rounded-lg border text-center transition-all duration-200 flex flex-col items-center justify-center h-16";
                  let statusClass = "";

                  if (time.status === 'open') {
                    statusClass = isPast
                      ? "bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed opacity-50"
                      : isSelected
                        ? "bg-emerald-600 border-emerald-600 text-white shadow-inner ring-2 ring-emerald-200"
                        : "bg-white border-emerald-100 text-gray-700 hover:border-emerald-500 hover:shadow-sm cursor-pointer";
                  } else {
                    statusClass = "bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed opacity-70";
                  }

                  return (
                    <button
                      key={`${court.id}-${time.id}`}
                      onClick={() =>
                        handleSelectItem(
                          {
                            court_id: court.id,
                            name: court.name,
                            time_slot_id: time.id,
                            start_time: time.start_time,
                            end_time: time.end_time,
                            date: selectedDate,
                            price: Number(time.price) || 0,
                          },
                          time.status
                        )
                      }
                      disabled={time.status !== 'open' || isPast}
                      className={`${baseClass} ${statusClass}`}
                    >
                      <span className="text-xs font-bold">{time.start_time.slice(0, 5)}</span>
                      <span className={`text-[10px] mt-1 ${isSelected ? 'text-emerald-100' : 'text-emerald-600 font-medium'}`}>
                        {Number(time.price / 1000)}k
                      </span>
                      {isSelected && (
                        <div className="absolute top-1 right-1">
                          <i className="fa-solid fa-circle-check text-[10px] text-white"></i>
                        </div>
                      )}
                    </button>
                  );
                })}
            </div>
          </div>

          {/* Form Thanh toán & Voucher */}
          <form onSubmit={handleSubmit} className="pt-4 border-t border-gray-100 space-y-4">
            {selectedItems.length > 0 && (
               <div className="bg-gray-50 p-3 rounded-lg text-xs space-y-1 max-h-24 overflow-y-auto">
                  {selectedItems.map((item, idx) => (
                     <div key={idx} className="flex justify-between text-gray-600">
                        <span>{item.name} ({item.start_time.slice(0,5)} - {item.end_time.slice(0,5)})</span>
                        <span className="font-medium">{formatPrice(item.price)}</span>
                     </div>
                  ))}
               </div>
            )}

            <Voucher_Detail_Venue onVoucherApply={setSelectedVoucher} totalPrice={rawTotalPrice} />

            <div className="space-y-2 pt-2">
              <div className="flex justify-between text-sm text-gray-500">
                <span>Tạm tính:</span>
                <span>{formatPrice(rawTotalPrice)}</span>
              </div>
              {selectedVoucher && (
                <div className="flex justify-between text-sm text-emerald-600">
                  <span>Giảm giá:</span>
                  <span>- {formatPrice(rawTotalPrice - selectedPrice)}</span>
                </div>
              )}
              <div className="flex justify-between items-center pt-2 border-t border-gray-100">
                <span className="font-bold text-gray-800">Tổng cộng:</span>
                <span className="font-extrabold text-xl text-amber-600">{formatPrice(selectedPrice)}</span>
              </div>
            </div>

            <button
              type="submit"
              disabled={selectedItems.length === 0 || isSubmitting}
              className={`w-full py-3.5 rounded-xl font-bold text-white shadow-lg transition-all duration-300 flex items-center justify-center gap-2 ${
                selectedItems.length === 0 || isSubmitting
                  ? 'bg-gray-300 cursor-not-allowed shadow-none'
                  : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 hover:shadow-emerald-500/30 transform hover:-translate-y-1'
              }`}
            >
              {isSubmitting ? (
                <>
                  <i className="fa-solid fa-circle-notch fa-spin"></i>
                  Đang xử lý...
                </>
              ) : (
                <>
                  Đặt ngay
                  <i className="fa-solid fa-arrow-right"></i>
                </>
              )}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Booking_Detail_Venue;