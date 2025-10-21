import React, { useEffect, useState } from 'react';
import { useFetchDataById, usePostData } from '../../Hooks/useApi';
import { useNavigate } from 'react-router-dom';
import { useOutletContext } from 'react-router-dom';
import type { OutletContext } from '../../Types/OutletContext';
import type { Venue, EnrichedTimeSlot } from '../../Types/venue';
import type { Image } from '../../Types/image';
import type { Court } from '../../Types/court';
import type { ApiResponse } from '../../Types/api';

type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
};

const Detail_Venue = ({ id }: { id: number | string }) => {
  const { setNotification } = useOutletContext<OutletContext>();
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().slice(0, 10));
  const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<number>(0);
  const [isActiveCourtId, setIsActiveCourtId] = useState<number | null>(null)


  const navigate = useNavigate();
  // Gọi API đúng: GET /api/venues/{id}?date=YYYY-MM-DD
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', id, { date: selectedDate });

  const { mutate } = usePostData<ApiResponse<number>, unknown>('tickets');

  // Tính tổng tiền
  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price || 0), 0);
    setSelectedPrice(total);
  }, [selectedItems]);

  // Tải lại dữ liệu khi đổi ngày
  useEffect(() => {
    refetch();
    setSelectedItems([]);
  }, [selectedDate, refetch]);

  useEffect(() => {
    if (detail_venue?.data?.courts?.length && isActiveCourtId === null) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    }
  }, [detail_venue, isActiveCourtId]);


  // Giao diện Loading
  if (isLoading || !detail_venue) {
    return (
      <div className="flex items-center justify-center h-full min-h-[500px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#348738]"></div>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;
  const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);
  const courts = venue.courts ?? [];



  const handleChangeDate = (e: React.ChangeEvent<HTMLInputElement>) => setSelectedDate(e.target.value);

  const handleSelectItem = (clickedItem: SelectedItem, status: string | null | undefined) => {
    if (status !== 'open') {
      const message = status === 'booked' ? 'Khung giờ này đã được đặt.'
        : status === 'maintenance' ? 'Khung giờ này đang bảo trì.'
          : 'Khung giờ này không khả dụng.';
      setNotification({ message, type: 'error' });
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

  // Logic gửi dữ liệu đặt sân (đã được cập nhật)
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (selectedItems.length === 0) {
      setNotification({ message: 'Vui lòng chọn sân và khung giờ!', type: 'error' });
      return;
    }

    const itemsData = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: item.price, // Gửi 'unit_price' cho đúng với yêu cầu của backend
    }));

    const bookingData = {
      user_id: 1, // Hardcoded user_id for now
      promotion_id: null,
      discount_amount: 0,
      bookings: itemsData,
    };



    mutate(bookingData, {
      onSuccess: (response) => {
        const { success, message, data } = response;
        if (success) {
          setNotification({ message: 'Đặt sân thành công!', type: 'success' });
          navigate(`/booking/${data}`);
        } else {
          setNotification({ message: message || 'Một hoặc nhiều slot đã được đặt. Vui lòng thử lại.', type: 'error' });
          refetch(); // Tải lại lịch để thấy slot nào đã bị chiếm
        }
      },
      onError: (error) => {
        setNotification({ message: 'Đã xảy ra lỗi. Vui lòng thử lại.', type: 'error' });
        console.log(error);
      },
    });
  };

  return (
    <div className="w-full h-full">
      {/* Hero Section */}
      <div
        className="relative h-64 md:h-80 bg-cover bg-center bg-no-repeat"
        style={{ backgroundImage: `url(${primaryImage?.url || 'https://via.placeholder.com/800x300?text=BCP+Sports'})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
        <div className="absolute bottom-4 left-4 right-4 text-white">
          <h1 className="text-2xl md:text-4xl font-bold mb-2">{venue.name}</h1>
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-1">
              <i className="fa-solid fa-star text-yellow-400"></i>
              <span className="font-semibold">{Number(venue.reviews_avg_rating ?? 0).toFixed(1)}</span>
            </div>
            <div className="flex items-center gap-1">
              <i className="fa-solid fa-phone text-[#348738] w-5"></i>
              <span className="text-sm">{venue?.phone}</span>
            </div>
            <div className="flex items-center gap-1">
              <i className="fa-solid fa-location-dot text-[#348738] w-5"></i>
              <span className="text-sm">{venue.address_detail}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="p-6 space-y-8">
        {/* --- ĐÃ SỬA: Đổi 'types' thành 'venue_types' cho khớp với API --- */}
        {venue.venueTypes && (
          <div>
            <h3 className="text-lg font-bold text-gray-800 mb-3">Loại sân</h3>
            <div className="flex flex-wrap gap-2">
              {venue.venueTypes.length > 0 ? (
                venue.venueTypes.map((type) => (
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
        )}

        {/* Court & Slot Selection */}
        {courts.length > 0 ? (
          <div>
            <div className="flex justify-between items-center border-b pb-3 mb-4 border-gray-200">
              <h3 className="text-xl font-bold text-gray-800">Chọn lịch sân</h3>
              <input
                type="date"
                value={selectedDate}
                onChange={handleChangeDate}
                className="px-3 py-2 text-gray-700 border-2 rounded-lg border-gray-300 focus:outline-none focus:border-[#348738] focus:ring-1 focus:ring-[#348738]"
              />
            </div>

            <div>
              {/* --- Thanh chọn sân --- */}
              <div className="flex border-b border-[#348738] divide-x divide-[#348738]">
                {courts.map((court: Court) => (
                  <button
                    key={court.id}
                    onClick={() => setIsActiveCourtId(court.id)}
                    className={`px-3 py-2 transition-colors duration-200
                       ${isActiveCourtId === court.id
                        ? 'bg-[#348738] text-white'
                        : 'hover:bg-orange-600 hover:text-white'
                      }`}
                  >
                    {court.name}
                  </button>
                ))}
              </div>

              {/* --- Hiển thị time slot của sân được chọn --- */}
              <div className="mt-6">
                {courts
                  .filter((court) => court.id === isActiveCourtId)
                  .map((court: Court & { time_slots?: EnrichedTimeSlot[] }) => (
                    <div key={court.id} className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                      <h4 className="text-lg font-semibold text-[#2d6a2d] mb-2">{court.name}</h4>
                      <div className="text-sm text-gray-600 space-y-1 mb-4">
                        <p><span className="font-medium text-gray-700">Mặt sân:</span> {court.surface}</p>
                        <p><span className="font-medium text-gray-700">Loại:</span> {court.is_indoor ? 'Trong nhà' : 'Ngoài trời'}</p>
                      </div>

                      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-x-2 gap-y-4">
                        {court.time_slots?.map((time) => {
                          const isSelected = selectedItems.some(
                            (item) => item.court_id === court.id && item.time_slot_id === time.id
                          );

                          let timeClass = '';
                          switch (time.status) {
                            case 'maintenance':
                              timeClass = 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed line-through';
                              break;
                            case 'booked':
                              timeClass = 'bg-red-100 text-red-400 border border-red-200 cursor-not-allowed line-through';
                              break;
                            case 'closed':
                              timeClass = 'bg-yellow-100 text-yellow-500 border border-yellow-200 cursor-not-allowed line-through';
                              break;
                            case 'open':
                              timeClass = 'bg-green-50 text-green-800 border border-green-300 hover:bg-green-100 cursor-pointer';
                              break;
                            default:
                              timeClass = 'bg-gray-100 text-gray-400 cursor-not-allowed line-through';
                          }

                          if (isSelected) {
                            timeClass = 'bg-[#348738] text-white ring-2 ring-offset-1 ring-[#2d6a2d] cursor-pointer';
                          }

                          return (
                            <div key={time.id} className="text-center">
                              <p
                                onClick={() =>
                                  handleSelectItem(
                                    {
                                      court_id: court.id,
                                      name: court.name,
                                      time_slot_id: time.id,
                                      start_time: time.start_time,
                                      end_time: time.end_time,
                                      date: selectedDate,
                                      price: time.price || 0,
                                    },
                                    time.status
                                  )
                                }
                                className={`p-2 rounded-lg transition-all duration-200 font-medium ${timeClass}`}
                              >
                                {time.label ?? ''}
                              </p>
                              <span className="text-xs text-gray-500 mt-1 block">
                                {time.price ? `${time.price / 1000}k` : '-'}
                              </span>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  ))}
              </div>
            </div>

          </div>
        ) : (
          <div className="text-center py-10 bg-gray-50 rounded-lg">
            <p className="text-gray-500">Địa điểm này chưa có sân nào được tạo.</p>
          </div>
        )}

        {/* Booking Summary & Action */}
        <div className="p-5 border-t border-gray-200 sticky bottom-0 bg-white shadow-top rounded-b-2xl">
          <h2 className="font-bold text-lg text-gray-800 mb-4">
            Giỏ hàng của bạn
          </h2>

          {selectedItems.length === 0 ? (
            <div className="text-center text-gray-500 py-4 italic">
              <p>Chưa có sân nào được chọn.</p>
            </div>
          ) : (
            <div className="space-y-3 mb-4 max-h-40 overflow-y-auto pr-2">
              {selectedItems.map((item) => (
                <div key={`${item.court_id}-${item.time_slot_id}`} className="p-3 rounded-lg border border-gray-200 bg-gray-50 flex justify-between items-center">
                  <div>
                    <p className="font-semibold text-[#2d6a2d]">{item.name}</p>
                    <p className="text-sm text-gray-600">
                      {item.start_time.slice(0, 5)} - {item.end_time.slice(0, 5)}
                    </p>
                  </div>
                  <p className="font-bold text-[#348738] text-lg">
                    {Number(item.price).toLocaleString('vi-VN')}₫
                  </p>
                </div>
              ))}
            </div>
          )}

          <div className="text-right border-t pt-4 mt-4">
            <p className="font-semibold text-gray-700">
              Tổng cộng:{' '}
              <span className="text-2xl font-bold text-[#2d6a2d] ml-2">{selectedPrice.toLocaleString('vi-VN')}₫</span>
            </p>
          </div>

          <form onSubmit={handleSubmit} className="mt-6">
            <button
              type="submit"
              disabled={selectedItems.length === 0}
              className={`w-full font-bold py-3 px-6 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all duration-300 ${selectedItems.length === 0
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2'
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