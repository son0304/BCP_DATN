import React, { useEffect, useState } from 'react';
import { useFetchDataById, usePostData } from '../../Hooks/useApi';
import { useNavigate } from 'react-router-dom';
import { useOutletContext } from 'react-router-dom';
import type { OutletContext } from '../../Types/OutletContext';
import type { Venue } from '../../Types/venue';
import type { Image } from '../../Types/image';
import type { Court } from '../../Types/court';
import type { TimeSlot } from '../../Types/timeSlot';
import type { ApiResponse } from '../../Types/api';

type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price_per_hour: number;
};

const Detail_Venue = ({ id }: { id: number | string }) => {
  const { setNotification } = useOutletContext<OutletContext>();
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().slice(0, 10));
  const [selectedVoucher, setSelectedVoucher] = useState('');
  const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<number>(0);

  const navigate = useNavigate();
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', id, { date: selectedDate });
  
  const { mutate } = usePostData<ApiResponse<number>, unknown>('tickets');

  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price_per_hour || 0), 0);
    setSelectedPrice(total);
  }, [selectedItems]);

  useEffect(() => {
    refetch(); 
    setSelectedItems([]); 
  }, [selectedDate, refetch]);

  if (isLoading || !detail_venue) {
    return (
      <div className="flex items-center justify-center h-64">
        {/* Loading màu xanh lá */}
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#348738]"></div>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;
  const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);
  const allImages = venue.images || [];

  const courts = venue.courts ?? [];

  const handleChangeDate = (e: React.ChangeEvent<HTMLInputElement>) => setSelectedDate(e.target.value);
  const handleChangeVoucher = (e: React.ChangeEvent<HTMLInputElement>) => setSelectedVoucher(e.target.value);

  const handleSelectItem = (clickedItem: SelectedItem, isBooking: string | null | undefined) => {
    if (isBooking === 'confirmed' || isBooking === 'pending') {
      setNotification({ message: 'Khung giờ này đã có người đặt.', type: 'error' });
      return;
    }

    const isSelected = selectedItems.some(
      (item) => item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id
    );

    if (isSelected) {
      setSelectedItems((prev) =>
        prev.filter(
          (item) => !(item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id)
        )
      );
    } else {
      setSelectedItems((prev) => [...prev, clickedItem]);
    }
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (selectedItems.length === 0) {
      setNotification({ message: 'Vui lòng chọn sân và khung giờ trước khi đặt!', type: 'error' });
      return;
    }

    const itemsData = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: item.price_per_hour,
    }));

    const bookingData = {
      user_id: 1, 
      promotion_id: null,
      discount_amount: 0,
      bookings: itemsData,
    };

    mutate(bookingData, {
      onSuccess: (response) => {
        const { success, message, data } = response;
        if (success) {
          setNotification({ message, type: 'success' });
          navigate(`/booking/${data}`);
        } else {
          setNotification({ message: message || 'Slot đã tồn tại. Đặt slot khác', type: 'error' });
        }
      },
      onError: (error) => {
        setNotification({ message: 'Slot đã tồn tại. Đặt slot khác', type: 'error' });
        console.log(error);
      },
    });
  };

  return (
    <div className="w-full h-full">
      {/* Hero Section */}
      <div
        className="relative h-64 md:h-80 bg-cover bg-center bg-no-repeat rounded-t-2xl"
        style={{ backgroundImage: `url(${primaryImage?.url || 'https://via.placeholder.com/800x400?text=No+Image'})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent rounded-t-2xl"></div>
        <div className="absolute bottom-4 left-4 right-4 text-white">
          <h1 className="text-2xl md:text-4xl font-bold mb-2">{venue.name}</h1>
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-1">
              <i className="fa-solid fa-star text-yellow-400"></i>
              <span className="font-semibold">{Number(venue.reviews_avg_rating ?? 0).toFixed(1)}</span>
            </div>
            <div className="flex items-center gap-1">
              {/* Icon xanh lá */}
              <i className="fa-solid fa-location-dot text-[#348738] w-5"></i>
              <span className="text-sm">{venue.address_detail}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="p-6 space-y-6">
        {/* Types (xanh lá) */}
        <div>
          <h3 className="text-lg font-bold text-gray-800 mb-3">Loại sân</h3>
          <div className="flex flex-wrap gap-2">
            {venue.types && venue.types.length > 0 ? (
              venue.types.map((type) => (
                <span
                  key={type.id}
                  className="px-4 py-2 bg-gradient-to-r from-[#348738]/10 to-[#2d6a2d]/10 text-[#348738] text-sm font-medium rounded-full border border-[#348738]/20"
                >
                  {type.name}
                </span>
              ))
            ) : (
              <span className="px-4 py-2 bg-gray-100 text-gray-500 text-sm font-medium rounded-full">
                Chưa có loại sân
              </span>
            )}
          </div>
        </div>

        {/* Description */}
        <div>
          <h3 className="text-lg font-bold text-gray-800 mb-3">Mô tả</h3>
          <p className="text-gray-600 leading-relaxed">{venue.description || 'Chưa có mô tả chi tiết về sân này.'}</p>
        </div>

        {/* Images (tag "Chính" màu xanh lá) */}
        {allImages.length > 0 && (
          <div>
            <h3 className="text-lg font-bold text-gray-800 mb-3">Hình ảnh</h3>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              {allImages.slice(0, 6).map((image: Image, index: number) => (
                <div key={index} className="relative group">
                  <img
                    src={image.url}
                    alt={`${venue.name} - Hình ${index + 1}`}
                    className="w-full h-32 object-cover rounded-lg shadow-md group-hover:shadow-lg transition-shadow duration-300"
                  />
                  {image.is_primary === 1 && (
                    <div className="absolute top-2 right-2 bg-[#348738] text-white px-2 py-1 rounded-full text-xs font-medium">
                      Chính
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Contact Info (icons xanh lá) */}
        <div>
          <h3 className="text-lg font-bold text-gray-800 mb-3">Thông tin liên hệ</h3>
          <div className="space-y-2">
            <div className="flex items-center gap-3 text-gray-600">
              <i className="fa-solid fa-location-dot text-[#348738] w-5"></i>
              <span>{venue.address_detail}</span>
            </div>
            {venue.phone && (
              <div className="flex items-center gap-3 text-gray-600">
                <i className="fa-solid fa-phone text-[#348738] w-5"></i>
                <span>{venue.phone}</span>
              </div>
            )}
            {venue.email && (
              <div className="flex items-center gap-3 text-gray-600">
                <i className="fa-solid fa-envelope text-[#348738] w-5"></i>
                <span>{venue.email}</span>
              </div>
            )}
          </div>
        </div>

        {/* Courts List (border, focus, card border, card title màu xanh lá) */}
        {courts.length > 0 && (
          <div>
            <div className="flex justify-between border-b pb-2 mb-4 border-[#348738]/30">
              <h3 className="text-lg font-bold text-gray-800 mb-4">Danh sách sân</h3>
              <input
                type="date"
                value={selectedDate} 
                onChange={handleChangeDate}
                className="w-full px-4 py-2 text-gray-700 text-base border-2 rounded-lg border-[#348738] focus:outline-none focus:border-[#246026] focus:ring-1 focus:ring-[#348738]"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {courts.map((court: Court & { time_slots?: TimeSlot[] }) => (
                <div
                  key={court.id}
                  className="bg-white border border-[#348738]/20 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-300"
                >
                  <h4 className="text-lg font-semibold text-[#2d6a2d] mb-2">{court.name}</h4>
                  <div className="text-sm text-gray-600 space-y-1">
                    <p>
                      <span className="font-medium text-gray-700">Mặt sân:</span> {court.surface}
                    </p>
                    <p>
                      <span className="font-medium text-gray-700">Giá/giờ:</span>{' '}
                      {Number(court.price_per_hour).toLocaleString()} VNĐ
                    </p>
                    <p>
                      <span className="font-medium text-gray-700">Loại:</span>{' '}
                      {court.is_indoor ? 'Trong nhà' : 'Ngoài trời'}
                    </p>
                  </div>

                  <div className="grid grid-cols-2 gap-2 mt-2">
                    {court.time_slots?.map((time) => {
                      const isSelected = selectedItems.some(
                        (item) => item.court_id === court.id && item.time_slot_id === time.id
                      );
                      
                      const isBooked = time.is_booking === 'confirmed' || time.is_booking === 'pending';

                      // Slot đã chọn màu xanh lá (màu chủ đạo)
                      const timeClass = isSelected
                        ? 'bg-[#348738] text-white hover:bg-[#2d6a2d] cursor-pointer' 
                        : isBooked
                          ? time.is_booking === 'confirmed'
                            ? 'bg-red-500 text-white opacity-60 cursor-not-allowed'
                            : 'bg-yellow-400 text-black opacity-60 cursor-not-allowed'
                          : 'bg-gray-200 text-gray-800 hover:bg-gray-300 cursor-pointer'; 

                      return (
                        <p
                          key={time.id}
                          onClick={() =>
                            handleSelectItem({
                              court_id: court.id,
                              name: court.name,
                              time_slot_id: time.id,
                              start_time: time.start_time,
                              end_time: time.end_time,
                              date: selectedDate,
                              price_per_hour: court.price_per_hour,
                            },
                            time.is_booking 
                            )
                          }
                          className={`p-1 rounded text-center transition ${timeClass}`}
                        >
                          {time.label ?? ''}
                        </p>
                      );
                    })}
                  </div>

                </div>
              ))}
            </div>
          </div>
        )}

        {/* Selected Items & Booking (border, text, item bg... màu xanh lá) */}
        <div className="p-4 border rounded-xl border-[#348738]">
          <h1 className="font-bold border-b text-lg border-[#348738] pb-4 mb-4">
            Thông tin sân bạn chọn
          </h1>

          {selectedItems.length === 0 ? (
            <div className="text-center text-gray-500 py-4 italic">
              <p>Chưa có thông tin sân được chọn</p>
            </div>
          ) : (
            <div className="space-y-3 mb-3">
              {selectedItems.map((item) => (
                <div
                  key={`${item.court_id}-${item.time_slot_id}`}
                  className="p-3 rounded-lg border border-[#348738]/30 bg-[#f8fff8]"
                >
                  <p className="font-semibold text-[#2d6a2d] text-base mb-1">
                    Sân: <span className="text-gray-800">{item.name}</span>
                  </p>
                  <p className="text-sm text-gray-600">
                    <span className="font-medium text-[#348738]">Thời gian:</span> {item.start_time} - {item.end_time}
                  </p>
                  <p className="text-sm text-gray-600">
                    <span className="font-medium text-[#348738]">Ngày:</span> {item.date}
                  </p>
                  <p>Giá: {Number(item.price_per_hour).toLocaleString('vi-VN')}₫</p>
                </div>
              ))}
            </div>
          )}
            
          <p className="font-semibold text-[#2d6a2d] mt-2">
            Tổng tạm tính:{' '}
            <span className="text-lg text-[#348738]">{selectedPrice.toLocaleString('vi-VN')}₫</span>
          </p>

          <form onSubmit={handleSubmit} className="mt-4">
            <input
              type="text"
              name="voucher"
              placeholder="Voucher"
              className="my-2 border border-[#348738] p-2 rounded-xl w-full"
              onChange={handleChangeVoucher}
            />

            {/* --- ĐỔI MÀU CTA --- */}
            {/* Nút CTA chính màu Cam */}
            <button
              type="submit"
              disabled={selectedItems.length === 0}
              className={`flex-1 w-full font-bold py-3 px-6 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all duration-300 ${selectedItems.length === 0
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white hover:shadow-xl'
                }`}
            >
              <i className="fa-solid fa-calendar-plus"></i>
              <span>Đặt sân ngay</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Detail_Venue;