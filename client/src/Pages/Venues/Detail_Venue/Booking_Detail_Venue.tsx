import React, { useEffect, useState } from 'react';
import { useNotification } from '../../../Components/Notification';
import type { Venue } from '../../../Types/venue';
import type { User } from '../../../Types/user';
import { usePostData } from '../../../Hooks/useApi';
import type { ApiResponse } from '../../../Types/api';
import { useNavigate } from 'react-router-dom';
import type { QueryObserverResult, RefetchOptions } from '@tanstack/react-query';
import Voucher_Detail_Venue from './Voucher_Detail_Venue';

type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
};

export type Voucher = {
  id: number;
  code: string;
  value: number;
  type: '%' | 'VND';
  expires_at: string | null;
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
  const [selectedVoucher, setSelectedVoucher] = useState<Voucher | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const { showNotification } = useNotification();
  const { mutate } = usePostData<ApiResponse<number>, any>('tickets');
  const navigate = useNavigate();

  const courts = venue.courts ?? [];

  // ===== Format tiền
  const formatPrice = (price: number | string) => {
    const value = Number(price) || 0;
    return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
  };

  // ===== Tính tổng tiền
  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price || 0), 0);
    let finalPrice = total;
    if (selectedVoucher) {
      finalPrice -= selectedVoucher.type === '%' ? (total * selectedVoucher.value) / 100 : selectedVoucher.value;
    }
    setSelectedPrice(Math.max(0, finalPrice));
  }, [selectedItems, selectedVoucher]);

  // ===== Khi đổi ngày => reset selectedItems
  useEffect(() => {
    refetch();
    setSelectedItems([]);
  }, [selectedDate, refetch]);

  // ===== Chọn sân con mặc định
  useEffect(() => {
    if (courts.length && activeCourtId === null) setActiveCourtId(courts[0].id);
  }, [courts, activeCourtId]);

  // ===== Chọn slot
  const handleSelectItem = (clickedItem: SelectedItem, status?: string | null) => {
    if (status !== 'open') {
      const msg =
        status === 'booked'
          ? 'Khung giờ này đã được người khác đặt trước.'
          : status === 'maintenance'
          ? 'Khung giờ này đang bảo trì.'
          : 'Khung giờ này không khả dụng.';
      return showNotification(msg, 'error');
    }

    const isSelected = selectedItems.some(
      (item) => item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id
    );

    setSelectedItems((prev) =>
      isSelected ? prev.filter((i) => !(i.court_id === clickedItem.court_id && i.time_slot_id === clickedItem.time_slot_id)) : [...prev, clickedItem]
    );
  };

  // ===== Submit booking
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!user) return showNotification('Vui lòng đăng nhập để đặt sân.', 'error');
    if (selectedItems.length === 0) return showNotification('Chọn ít nhất 1 khung giờ.', 'error');

    const bookings = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: item.price,
    }));

    const total = selectedItems.reduce((sum, item) => sum + item.price, 0);
    const discount = selectedVoucher
      ? selectedVoucher.type === '%'
        ? (total * selectedVoucher.value) / 100
        : selectedVoucher.value
      : 0;

    setIsSubmitting(true);

    mutate(
      { user_id: user.id, promotion_id: selectedVoucher?.id || null, discount_amount: discount, bookings },
      {
        onSuccess: (res) => {
          if (res.success) {
            showNotification('Đặt sân thành công!', 'success');
            navigate(`/booking/${res.data}`);
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

  return (
    <div className="space-y-6 lg:col-span-2 order-1 lg:order-2">
      <div className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-2xl lg:sticky lg:top-8">
        <h4 className="text-lg font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">
          Đặt lịch thuê sân
        </h4>

        {/* Chọn ngày */}
        <div className="mb-4">
          <label className="text-base font-semibold text-[#4B5563]">Chọn ngày</label>
          <input
            type="date"
            className="w-full mt-2 px-3 py-2 border border-[#E5E7EB] rounded-lg focus:ring-2 focus:ring-[#10B981] outline-none text-[#4B5563]"
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
            min={new Date().toISOString().slice(0, 10)}
          />
        </div>

        {/* Chọn sân con */}
        <div className="mb-4">
          <label className="text-base font-semibold text-[#4B5563]">Chọn sân con</label>
          <div className="mt-2 flex flex-wrap gap-2">
            {courts.map((court) => (
              <button
                key={court.id}
                onClick={() => setActiveCourtId(court.id)}
                className={`px-4 py-2 rounded-xl text-sm font-medium border transition-all duration-200 ${
                  activeCourtId === court.id ? 'bg-[#10B981] text-white shadow-md' : 'bg-[#F9FAFB] text-[#4B5563] hover:bg-gray-100 border-[#E5E7EB]'
                }`}
              >
                {court.name}
              </button>
            ))}
          </div>
        </div>

        {/* Khung giờ */}
        <div>
          <p className="text-base font-semibold text-[#4B5563] mb-3">
            Khung giờ có sẵn ({selectedItems.length} đã chọn)
          </p>
          <div className="grid grid-cols-3 gap-2 max-h-80 overflow-y-auto pr-1">
            {courts
              .filter((c) => c.id === activeCourtId)
              .flatMap((court: any) => court.time_slots?.map((time: any) => ({ court, time })) ?? [])
              .map(({ court, time }: any) => {
                const isSelected = selectedItems.some(
                  (it) => it.court_id === court.id && it.time_slot_id === time.id
                );

                let cls =
                  time.status === 'open'
                    ? 'bg-green-50 text-green-800 border border-green-200'
                    : time.status === 'booked'
                    ? 'bg-red-100 text-red-600 cursor-not-allowed opacity-60'
                    : 'bg-gray-100 text-gray-500 cursor-not-allowed';

                if (isSelected) cls = 'bg-[#10B981] text-white ring-2 ring-green-300 shadow-md';

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
                    disabled={time.status !== 'open'}
                    className={`p-3 rounded-lg flex flex-col items-center font-semibold text-sm ${cls}`}
                  >
                    <div>{time.label ?? `${time.start_time.slice(0, 5)} - ${time.end_time.slice(0, 5)}`}</div>
                    <div className="text-xs mt-1">{formatPrice(time.price)}</div>
                  </button>
                );
              })}
          </div>
        </div>

        {/* Form tổng tiền */}
        <form onSubmit={handleSubmit} className="mt-6 space-y-4">
          <Voucher_Detail_Venue onVoucherApply={setSelectedVoucher} />

          <div className="pt-4 border-t border-gray-200">
            <div className="flex justify-between">
              <span>Tổng thanh toán:</span>
              <span className="font-bold text-amber-600">{formatPrice(selectedPrice)}</span>
            </div>
            <button
              type="submit"
              disabled={selectedItems.length === 0 || isSubmitting}
              className={`w-full mt-4 py-3 rounded-lg text-white font-semibold flex items-center justify-center gap-2 ${
                selectedItems.length === 0 || isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-600'
              }`}
            >
              {isSubmitting && <span className="loader-border animate-spin inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full" />}
              {isSubmitting ? 'Đang đặt...' : `Đặt ngay (${selectedItems.length})`}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default Booking_Detail_Venue;
