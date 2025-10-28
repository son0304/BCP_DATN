import React, { useEffect, useState } from 'react';
import { useFetchDataById, usePostData } from '../../Hooks/useApi';
import { useNavigate, useParams } from 'react-router-dom';
import type { Venue, EnrichedTimeSlot } from '../../Types/venue';
import type { Image } from '../../Types/image';
import type { Court } from '../../Types/court';
import type { ApiResponse } from '../../Types/api';
import { useNotification } from '../../Components/Notification';
type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
};

const Detail_Venue = () => {
  const user = JSON.parse(String(localStorage.getItem('user')))
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().slice(0, 10));
  const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<number>(0);
  const [isActiveCourtId, setIsActiveCourtId] = useState<number | null>(null);
  const { showNotification } = useNotification();

  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const idVenue = Number(id);
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', idVenue, { date: selectedDate });
  const { mutate } = usePostData<ApiResponse<number>, any>('tickets');

  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price || 0), 0);
    setSelectedPrice(total);
  }, [selectedItems]);

  useEffect(() => {
    refetch();
    setSelectedItems([]);
  }, [selectedDate, refetch]);

  useEffect(() => {
    if (detail_venue?.data?.courts?.length && isActiveCourtId === null) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    } else if (detail_venue?.data?.courts?.length && !detail_venue.data.courts.some(c => c.id === isActiveCourtId)) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    }
  }, [detail_venue]);
  // Loading state display
  if (isLoading || !detail_venue) {
    return (
      <div className="flex items-center justify-center h-full min-h-[500px] bg-gray-50 rounded-xl shadow-inner">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#348738]"></div>
        <p className="ml-4 text-[#348738] font-semibold">Đang tải lịch sân...</p>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;
  const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);
  const courts = venue.courts ?? [];

  const handleChangeDate = (e: React.ChangeEvent<HTMLInputElement>) => setSelectedDate(e.target.value);

  const handleSelectItem = (clickedItem: SelectedItem, status: string | null | undefined) => {
    if (status !== 'open') {
      const message =
        status === 'booked'
          ? 'Khung giờ này đã được người khác đặt trước.'
          : status === 'maintenance'
            ? 'Khung giờ này đang được bảo trì. Vui lòng chọn khung giờ khác.'
            : 'Khung giờ này hiện không khả dụng để đặt.';

      showNotification(message, 'error');
      return;
    }

    // Logic để đảm bảo người dùng chỉ chọn 1 slot mỗi sân trong giỏ hàng (optional, but good practice)
    // Dựa trên logic gốc, cho phép chọn nhiều slot trên nhiều sân
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
      showNotification("Vui lòng chọn khung giờ muốn đặt.", "error");
      return;
    }
    if (!user) {
      showNotification("Vui lòng đăng nhập để book sân.", "error");
      return
    }

    const itemsData = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: item.price,
    }));

    const bookingData = {
      user_id: user.id,
      promotion_id: null,
      discount_amount: 0,
      bookings: itemsData,
    };

    showNotification("Đang tiến hành đặt sân...", "success"); // Show pending notification

    mutate(bookingData, {
      onSuccess: (response) => {
        const { success, message, data } = response;
        if (success) {
          showNotification("Đặt sân thành công! Chuyển hướng đến chi tiết đơn hàng.", "success");
          navigate(`/booking/${data}`);
        } else {
          showNotification(
            message || "Một hoặc nhiều slot đã được đặt. Vui lòng thử lại.",
            "error"
          );
          refetch(); // Tải lại lịch
        }
      },
      onError: (error: Error) => {
        showNotification(`Đã xảy ra lỗi: ${error.message}`, "error");
        console.error(error);
      },
    });
  };

  // Helper function to format price
  const formatPrice = (price: number) => {
    return price.toLocaleString('vi-VN') + '₫';
  };

  return (
    <>

      <div className="max-w-4xl mx-auto min-h-screen bg-white rounded-xl shadow-2xl overflow-hidden mb-10">
        {/* Hero Section */}
        <div
          className="relative h-64 md:h-80 bg-cover bg-center bg-no-repeat rounded-t-xl"
          style={{ backgroundImage: `url(${primaryImage?.url || 'https://placehold.co/800x300/348738/ffffff?text=BCP+Sports'})` }}
        >
          <div className="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
          <div className="absolute bottom-6 left-6 right-6 text-white">
            <h1 className="text-3xl md:text-5xl font-extrabold mb-2 leading-tight">{venue.name}</h1>
            <div className="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
              <div className="flex items-center gap-1">
                <i className="fa-solid fa-star text-yellow-400"></i>
                <span className="font-semibold">{Number(venue.reviews_avg_rating ?? 0).toFixed(1)}/5.0</span>
              </div>
              <div className="flex items-center gap-1">
                <i className="fa-solid fa-phone text-orange-400"></i>
                <span>{venue?.phone}</span>
              </div>
              <div className="flex items-center gap-1">
                <i className="fa-solid fa-location-dot text-orange-400"></i>
                <span>{venue.address_detail}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Main Content & Booking Form */}
        <div className="p-6 lg:p-10 space-y-10">
          {/* Venue Types */}
          {venue.venueTypes && (
            <div>
              <h3 className="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Các loại hình sân</h3>
              <div className="flex flex-wrap gap-3">
                {venue.venueTypes.length > 0 ? (
                  venue.venueTypes.map((type) => (
                    <span
                      key={type.id}
                      className="px-4 py-2 bg-[#348738]/10 text-[#348738] text-sm font-medium rounded-full border border-[#348738]/30 transition duration-200 hover:bg-[#348738] hover:text-white"
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
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 mb-6 border-gray-200">
                <h3 className="text-2xl font-extrabold text-[#2d6a2d] mb-4 sm:mb-0">Chọn lịch sân</h3>
                <input
                  type="date"
                  value={selectedDate}
                  onChange={handleChangeDate}
                  min={new Date().toISOString().slice(0, 10)} // Only allow today and future dates
                  className="px-4 py-2 text-gray-700 border-2 rounded-xl border-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition duration-200"
                />
              </div>

              <div>
                {/* --- Thanh chọn sân --- */}
                <div className="flex flex-wrap gap-2 md:gap-4 mb-6 p-2 bg-gray-100 rounded-lg shadow-inner">
                  {courts.map((court: Court) => (
                    <button
                      key={court.id}
                      onClick={() => setIsActiveCourtId(court.id)}
                      className={`px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-300 transform hover:scale-105
                                              ${isActiveCourtId === court.id
                          ? 'court-button-active shadow-md'
                          : 'bg-white text-gray-700 court-button-inactive'
                        }`}
                    >
                      {court.name}
                    </button>
                  ))}
                </div>

                {/* --- Hiển thị time slot của sân được chọn --- */}
                <div className="mt-8">
                  {courts
                    .filter((court) => court.id === isActiveCourtId)
                    .map((court: Court & { time_slots?: EnrichedTimeSlot[] }) => (
                      <div key={court.id} className="bg-white border border-gray-100 rounded-xl p-6 shadow-xl">
                        <h4 className="text-xl font-bold text-gray-800 border-b pb-3 mb-4">{court.name}</h4>
                        <div className="text-sm text-gray-600 space-y-1 mb-6 flex flex-wrap gap-x-8">
                          <p><span className="font-medium text-gray-700">Mặt sân:</span> {court.surface}</p>
                          <p><span className="font-medium text-gray-700">Loại:</span> {court.is_indoor ? 'Trong nhà' : 'Ngoài trời'}</p>
                        </div>

                        <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                          {court.time_slots?.map((time) => {
                            const isSelected = selectedItems.some(
                              (item) => item.court_id === court.id && item.time_slot_id === time.id
                            );

                            let timeClass = '';
                            let tooltipText = '';
                            switch (time.status) {
                              case 'maintenance':
                                timeClass = 'bg-gray-200 text-gray-500 cursor-not-allowed opacity-70';
                                tooltipText = 'Đang bảo trì';
                                break;
                              case 'booked':
                                timeClass = 'bg-red-500/10 text-red-600 cursor-not-allowed';
                                tooltipText = 'Đã có người đặt';
                                break;
                              case 'closed':
                                timeClass = 'bg-yellow-500/10 text-yellow-600 cursor-not-allowed';
                                tooltipText = 'Đã đóng cửa';
                                break;
                              case 'open':
                                timeClass = 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 cursor-pointer shadow-sm';
                                tooltipText = 'Sẵn sàng đặt';
                                break;
                              default:
                                timeClass = 'bg-gray-100 text-gray-400 cursor-not-allowed';
                                tooltipText = 'Không khả dụng';
                            }

                            if (isSelected) {
                              timeClass = 'bg-orange-500 text-white ring-2 ring-offset-2 ring-orange-300 shadow-md cursor-pointer hover:bg-orange-600';
                            }

                            return (
                              <div key={time.id} className="text-center group relative">
                                <button
                                  type="button"
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
                                  className={`w-full h-full p-3 rounded-xl transition-all duration-200 font-semibold flex flex-col justify-center items-center ${timeClass}`}
                                  title={tooltipText}
                                >
                                  <span className="text-sm">{time.label ?? ''}</span>
                                  <span className="text-xs font-medium opacity-90">
                                    {time.price ? formatPrice(time.price) : '-'}
                                  </span>
                                </button>
                                {/* Only show line-through for non-open slots */}
                                {time.status !== 'open' && !isSelected && (
                                  <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div className="w-full h-0.5 bg-red-400 transform rotate-12"></div>
                                  </div>
                                )}
                              </div>
                            );
                          })}
                        </div>
                        <div className="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
                          <p className="font-semibold mb-2">Chú thích trạng thái:</p>
                          <ul className="grid grid-cols-2 gap-2">
                            <li><span className="inline-block w-3 h-3 bg-green-50 border border-green-200 rounded-full mr-2"></span>Khung giờ mở</li>
                            <li><span className="inline-block w-3 h-3 bg-red-500/10 rounded-full mr-2"></span>Đã được đặt</li>
                            <li><span className="inline-block w-3 h-3 bg-yellow-500/10 rounded-full mr-2"></span>Đã đóng cửa</li>
                            <li><span className="inline-block w-3 h-3 bg-gray-200 rounded-full mr-2"></span>Đang bảo trì</li>
                            <li><span className="inline-block w-3 h-3 bg-orange-500 rounded-full mr-2"></span>Đang chọn</li>
                          </ul>
                        </div>
                      </div>
                    ))}
                </div>
              </div>
            </div>
          ) : (
            <div className="text-center py-12 bg-orange-50 rounded-xl border border-orange-200">
              <i className="fa-solid fa-triangle-exclamation text-orange-500 text-3xl mb-4"></i>
              <p className="text-gray-700 font-medium">Rất tiếc, địa điểm này chưa có sân nào được tạo hoặc không có lịch vào ngày đã chọn.</p>
            </div>
          )}
        </div>

        {/* Booking Summary & Action (Sticky Footer) */}
        <div className="sticky bottom-0 bg-white p-6 shadow-top z-10 border-t border-gray-100">
          <form onSubmit={handleSubmit}>
            <h2 className="font-bold text-xl text-gray-800 mb-4 flex justify-between items-center">
              <span>Giỏ hàng của bạn ({selectedItems.length} slot)</span>
              <span className="text-3xl font-extrabold text-orange-600 ml-4">{formatPrice(selectedPrice)}</span>
            </h2>

            <div className="space-y-3 mb-6 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
              {selectedItems.length === 0 ? (
                <div className="text-center text-gray-500 py-4 italic border-dashed border-2 border-gray-200 rounded-lg">
                  <p>Vui lòng chọn khung giờ muốn đặt ở trên.</p>
                </div>
              ) : (
                selectedItems.map((item) => (
                  <div key={`${item.court_id}-${item.time_slot_id}`} className="p-3 rounded-lg border border-orange-200 bg-orange-50 flex justify-between items-center transition duration-200">
                    <div className="flex items-center gap-3">
                      <i className="fa-solid fa-circle-check text-orange-500"></i>
                      <div>
                        <p className="font-semibold text-gray-800">{item.name}</p>
                        <p className="text-sm text-gray-600">
                          {item.start_time.slice(0, 5)} - {item.end_time.slice(0, 5)} ({item.date})
                        </p>
                      </div>
                    </div>
                    <p className="font-bold text-lg text-orange-600">
                      {formatPrice(item.price)}
                    </p>
                  </div>
                ))
              )}
            </div>

            <button
              type="submit"
              disabled={selectedItems.length === 0}
              className={`w-full font-bold py-4 px-6 rounded-xl shadow-xl flex items-center justify-center gap-2 transition-all duration-300 uppercase tracking-wider
                              ${selectedItems.length === 0
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-gradient-to-r from-orange-500 to-orange-700 hover:from-orange-600 hover:to-orange-800 text-white hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-orange-300'
                }`}
            >
              <i className="fa-solid fa-arrow-right-to-bracket"></i>
              <span>Thanh toán và Đặt sân ngay</span>
            </button>
          </form>
        </div>
      </div>
    </>
  );
};

export default Detail_Venue;