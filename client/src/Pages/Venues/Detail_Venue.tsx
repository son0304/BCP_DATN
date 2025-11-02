import React, { useEffect, useState, useMemo } from 'react';
import { useFetchDataById, usePostData } from '../../Hooks/useApi';
import { useNavigate, useParams } from 'react-router-dom';
import type { Venue, EnrichedTimeSlot } from '../../Types/venue';
import type { Image } from '../../Types/image';
import type { ApiResponse } from '../../Types/api';
import { useNotification } from '../../Components/Notification';
import { fetchData } from '../../Api/fetchApi';
import type { Province } from '../../Types/province';
import type { District } from '../../Types/district';
import type { User } from '../../Types/user';

type SelectedItem = {
  court_id: number;
  name: string;
  time_slot_id: number;
  start_time: string;
  end_time: string;
  date: string;
  price: number;
};

const Detail_Venue: React.FC = () => {
  const rawUser = typeof window !== 'undefined' ? localStorage.getItem('user') : null;
  const user = useMemo(() => {
    try {
      return rawUser ? JSON.parse(rawUser) : null;
    } catch {
      return null;
    }
  }, [rawUser]);

  const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
  const [selectedItems, setSelectedItems] = useState<SelectedItem[]>([]);
  const [selectedPrice, setSelectedPrice] = useState<number>(0);
  const [isActiveCourtId, setIsActiveCourtId] = useState<number | null>(null);
  const [galleryIndex, setGalleryIndex] = useState<number>(0);
  const [relatedVenues, setRelatedVenues] = useState<Venue[]>([]);
  const [relatedLoading, setRelatedLoading] = useState<boolean>(false);

  const { showNotification } = useNotification();
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const idVenue = Number(id);

  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', idVenue, { date: selectedDate });
  const { data: province } = useFetchDataById<Province>('province', Number(detail_venue?.data.province_id));
  const { data: district } = useFetchDataById<District>('district', Number(detail_venue?.data.district_id))
  const { data: owner } = useFetchDataById<User>('district', Number(detail_venue?.data.owner_id));

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
    } else if (detail_venue?.data?.courts?.length && !detail_venue.data.courts.some((c: any) => c.id === isActiveCourtId)) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    }
  }, [detail_venue, isActiveCourtId]);

  useEffect(() => {
    const loadRelated = async () => {
      try {
        setRelatedLoading(true);
        const currentId = detail_venue?.data?.id;
        const res = await fetchData<any>('venues');
        const list = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : [];
        const items = list.filter((v: any) => v.id !== currentId).slice(0, 4);
        setRelatedVenues(items.length ? items : (detail_venue?.data ? [detail_venue.data] : []));
      } catch (err) {
        console.error('loadRelated error:', err);
        setRelatedVenues([]);
      } finally {
        setRelatedLoading(false);
      }
    };
    loadRelated();
  }, [detail_venue?.data?.id]);

  if (isLoading || !detail_venue) {
    return (
      <div className="flex items-center justify-center h-full min-h-[560px] bg-[#F9FAFB] rounded-xl shadow-inner">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#10B981]" />
        <p className="ml-4 text-[#10B981] font-semibold">Đang tải lịch sân...</p>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;
  const images: Image[] = (venue as any).images ?? (venue as any).photos ?? [];
  const primaryImage = images.find((img: Image) => img.is_primary === 1) ?? images[0] ?? { url: 'https://placehold.co/1200x700/10B981/ffffff?text=BCP+Sports' };
  const gallery = images.length > 0 ? images : [primaryImage];
  const courts = venue.courts ?? [];

  const services = (venue as any).services ?? ['Bãi gửi xe', 'Cho thuê dụng cụ', 'WC & phòng thay đồ', 'Nước uống'];
  const reviews = (venue as any).reviews ?? {
    avg_rating: (venue as any).reviews_avg_rating ?? 4.6,
    total: (venue as any).reviews_count ?? 18,
    breakdown: [
      { title: 'Chất lượng sân', score: 4.6 },
      { title: 'Dịch vụ', score: 4.4 },
      { title: 'Vị trí', score: 4.7 },
    ],
  };

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

    const isSelected = selectedItems.some(
      (item) => item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id
    );

    if (isSelected) {
      setSelectedItems((prev) =>
        prev.filter((item) => !(item.court_id === clickedItem.court_id && item.time_slot_id === clickedItem.time_slot_id))
      );
    } else {
      setSelectedItems((prev) => [...prev, clickedItem]);
    }
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (selectedItems.length === 0) {
      showNotification('Vui lòng chọn khung giờ muốn đặt.', 'error');
      return;
    }
    if (!user) {
      showNotification('Vui lòng đăng nhập để book sân.', 'error');
      return;
    }

    const itemsData = selectedItems.map((item) => ({
      court_id: item.court_id,
      time_slot_id: item.time_slot_id,
      date: item.date,
      unit_price: Number(item.price),
    }));

    const bookingData = {
      user_id: user.id,
      promotion_id: null,
      discount_amount: 0,
      bookings: itemsData,
    };

    showNotification('Đang tiến hành đặt sân...', 'success');

    mutate(bookingData, {
      onSuccess: (response) => {
        const { success, message, data } = response;
        if (success) {
          showNotification('Đặt sân thành công! Chuyển hướng đến chi tiết đơn hàng.', 'success');
          navigate(`/booking/${data}`);
        } else {
          showNotification(message || 'Một hoặc nhiều slot đã được đặt. Vui lòng thử lại.', 'error');
          refetch();
        }
      },
      onError: (error: any) => {
        const msg = error?.response?.data?.message ?? error?.message ?? 'Đã xảy ra lỗi.';
        showNotification(`Đã xảy ra lỗi: ${msg}`, 'error');
      },
    });
  };

  const formatPrice = (price: number) => price.toLocaleString('vi-VN') + '₫';

  const priceRange = (() => {
    const prices: number[] = [];
    courts.forEach((c: any) => c.time_slots?.forEach((t: any) => { if (t.price) prices.push(Number(t.price)); }));
    if (prices.length === 0) return null;
    const min = Math.min(...prices), max = Math.max(...prices);
    return min === max ? `${formatPrice(min)}` : `${formatPrice(min)} - ${formatPrice(max)}`;
  })();
  // --- HẾT LOGIC HANDLERS ---


  return (
    <div className="max-w-7xl mx-auto my-8 bg-[#F9FAFB] rounded-2xl shadow-2xl overflow-hidden border border-[#E5E7EB]">
      {/* -------------------- PHẦN TRÊN: IMAGE GALLERY & HERO -------------------- */}
      <div className="lg:flex p-6 md:p-8 lg:p-10 bg-[#F9FAFB] rounded-2xl shadow-lg gap-6">
        <div className="lg:w-3/5 space-y-4">
          <div
            className="h-72 lg:h-[460px] rounded-2xl bg-cover bg-center relative shadow-md overflow-hidden transition-all duration-500"
            style={{ backgroundImage: `url(${gallery[galleryIndex].url})` }}
          >
            {/* Overlay */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />

            {/* Thông tin trên ảnh */}
            <div className="absolute bottom-6 left-6 right-6 text-white">
              <h1 className="text-3xl md:text-4xl font-extrabold leading-tight text-shadow-lg">{venue.name}</h1>
              <div className="mt-2 flex flex-wrap gap-4 text-base opacity-95">
                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-star text-[#F59E0B]" />
                  <span className="font-semibold">{Number(reviews.avg_rating ?? 0).toFixed(1)}/5.0</span>
                  <span className="text-gray-300">({reviews.total} đánh giá)</span>
                </div>
                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-location-dot text-[#F59E0B]" />
                  <span className="text-gray-200">{venue.address_detail + '-' + district?.data.name + '-' + province?.data.name ?? 'Địa chỉ đang cập nhật'}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Gallery Thumbnails */}
          <div className="p-2 rounded-xl bg-white shadow-sm border border-gray-200">
            <div className="flex gap-3 overflow-x-auto pb-1">
              {gallery.map((img, idx) => (
                <button
                  key={idx}
                  onClick={() => setGalleryIndex(idx)}
                  className={`flex-shrink-0 w-28 h-16 rounded-lg overflow-hidden border transition-all duration-300 ${galleryIndex === idx
                    ? 'ring-2 ring-offset-1 ring-[#10B981] border-[#10B981]'
                    : 'border-gray-200'
                    }`}
                >
                  <img src={img.url} alt={`${venue.name}-img-${idx}`} className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Thông tin bên phải */}
        <div className="lg:w-2/5 p-6 bg-white rounded-2xl shadow-md border border-gray-200 flex flex-col gap-6">
          <h4 className="text-xl font-bold text-[#11182C]">Thông tin cơ bản</h4>

          <div className="space-y-4 text-sm text-[#6B7280]">

            <div>
              <i className="fa-solid fa-user w-5 text-center"></i>
              <span>Chủ sân: <span className="font-bold text-[#F59E0B]">{owner?.data.name ?? "Kh có thông tin"}</span></span>
            </div>

            <div className="flex items-center gap-2">
              <i className="fa-solid fa-clock w-5 text-center text-[#10B981]" />
              <span>Giờ mở cửa: <span className="font-semibold text-[#11182C]">{venue.start_time?.slice(0, 5) ?? '—'} - {venue.end_time?.slice(0, 5) ?? '—'}</span></span>
            </div>

            <div className="flex items-center gap-2">
              <i className="fa-solid fa-money-bill-wave w-5 text-center text-[#10B981]" />
              <span>Giá thuê: <span className="font-bold text-[#F59E0B]">{priceRange ?? 'Liên hệ'}</span></span>
            </div>

            {/* Bản đồ */}
            <div className='pt-2'>
              <p className='font-semibold text-[#11182C] mb-1'>Vị trí trên Bản đồ</p>
              <div className='bg-gray-200 h-40 rounded-lg flex items-center justify-center text-sm text-[#6B7280] italic'>
                [Vị trí Bản đồ Google Map]
              </div>
            </div>
          </div>
        </div>
      </div>


      {/* -------------------- MAIN CONTENT: THÔNG TIN VÀ ĐẶT LỊCH -------------------- */}
      <div className="p-6 md:p-8 lg:p-10 space-y-10">
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">

          {/* === [CỘT TRÁI - 2/3 - THÔNG TIN CHI TIẾT & ĐÁNH GIÁ] === */}
          <div className="lg:col-span-3 space-y-8 order-2 lg:order-1">

            {/* 1. Mô tả */}
            <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
              <h3 className="text-xl font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Giới thiệu</h3>
              <p className="text-[#4B5563] text-base leading-relaxed">{venue.description ?? 'Sân đang cập nhật mô tả chi tiết.'}</p>
            </section>

            {/* 2. Tiện ích */}
            <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
              <h3 className="text-xl font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Tiện ích tại sân</h3>
              <ul className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-base text-[#4B5563]">
                {services.map((s: string, idx: number) => (
                  <li key={idx} className="flex items-center gap-2 font-medium">
                    <i className="fa-solid fa-circle-check text-lg text-[#10B981]" /> {s}
                  </li>
                ))}
              </ul>
            </section>

            {/* 3. Danh sách sân con (Chi tiết) */}
            <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
              <h3 className="text-xl font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Các sân con đang hoạt động</h3>
              <div className="overflow-x-auto">
                <table className="w-full text-base">
                  <thead>
                    <tr className="text-left text-[#6B7280] uppercase tracking-wider bg-[#F9FAFB]">
                      <th className="py-3 px-3">Tên Sân</th>
                      <th className="py-3 px-3">Khung giờ mở</th>
                      <th className="py-3 px-3">Giá khởi điểm</th>
                    </tr>
                  </thead>
                  <tbody>
                    {courts.map((c) => {
                      const slots = c.time_slots ?? [];
                      const openSlots = slots.filter((s: any) => s.status === 'open');
                      const prices = slots.map((s: any) => Number(s.price)).filter(p => !isNaN(p));
                      const minPrice = prices.length ? Math.min(...prices) : 0;
                      return (
                        <tr key={c.id} className="border-t border-[#E5E7EB] hover:bg-gray-50 transition-colors duration-200">
                          <td className="py-3 px-3 font-semibold text-[#11182C]">{c.name}</td>
                          <td className="py-3 px-3 text-center text-[#4B5563]">{openSlots.length}</td>
                          <td className="py-3 px-3 text-[#F59E0B] font-bold">{minPrice ? formatPrice(minPrice) : 'Liên hệ'}</td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </section>

            {/* 4. Đánh giá */}
            <section id="reviews" className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
              <h3 className="text-xl font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Đánh giá khách hàng ({reviews.total})</h3>
              <div className="flex flex-wrap gap-8 items-center">
                <div className="text-center">
                  <p className="text-5xl font-extrabold text-[#10B981]">{Number(reviews.avg_rating).toFixed(1)}</p>
                  <p className="text-base text-[#6B7280]">Điểm trung bình</p>
                </div>
                <div className="flex-1 space-y-2">
                  {reviews.breakdown.map((r: any, i: number) => (
                    <div key={i} className="flex items-center text-sm text-[#4B5563]">
                      <span className="w-24 text-[#6B7280]">{r.title}</span>
                      <div className="flex-1 h-2 mx-3 bg-[#E5E7EB] rounded-full overflow-hidden">
                        <div
                          className="h-full bg-[#F59E0B]" // Màu Amber cho thanh progress
                          style={{ width: `${(r.score / 5) * 100}%` }}
                        />
                      </div>
                      <span className='font-semibold'>{r.score.toFixed(1)}</span>
                    </div>
                  ))}
                </div>
              </div>
              {/* Khu vực hiển thị danh sách đánh giá chi tiết */}
              <div className='mt-6 pt-4 border-t border-[#E5E7EB] text-center'>
                <button className='text-[#10B981] font-semibold hover:underline text-base'>
                  Xem tất cả đánh giá và bình luận
                </button>
              </div>
            </section>
          </div>

          {/* === [CỘT PHẢI - 1/3 - KHU VỰC ĐẶT LỊCH (BOOKING WIDGET)] === */}
          <div className="space-y-6 lg:col-span-2 order-1 lg:order-2">
            {/* Widget Đặt sân - sticky trên Desktop */}
            <div className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-2xl lg:sticky lg:top-8">
              <h4 className="text-xl font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Đặt lịch thuê sân</h4>

              {/* 1. Chọn ngày */}
              <div className="mb-4">
                <label className="text-base font-semibold text-[#4B5563]">Chọn ngày</label>
                <input
                  type="date"
                  className="w-full mt-2 px-3 py-2 border border-[#E5E7EB] rounded-lg focus:ring-2 focus:ring-[#10B981] outline-none transition-shadow text-[#4B5563]"
                  value={selectedDate}
                  onChange={handleChangeDate}
                  min={new Date().toISOString().slice(0, 10)}
                />
              </div>

              {/* 2. Chọn sân */}
              <div className="mb-4">
                <label className="text-base font-semibold text-[#4B5563]">Chọn sân con</label>
                <div className="mt-2 flex flex-wrap gap-2">
                  {courts.map((court) => (
                    <button
                      key={court.id}
                      onClick={() => setIsActiveCourtId(court.id)}
                      className={`px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border ${isActiveCourtId === court.id ? 'bg-[#10B981] text-white shadow-md' : 'bg-[#F9FAFB] text-[#4B5563] hover:bg-gray-100 border-[#E5E7EB]'}`}
                    >
                      {court.name}
                    </button>
                  ))}
                </div>
              </div>

              {/* 3. Khung giờ */}
              <div>
                <p className="text-base font-semibold text-[#4B5563] mb-3">
                  Khung giờ có sẵn (<span className='text-[#F59E0B]'>{selectedItems.length}</span> đã chọn)
                </p>
                <div className="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-2 max-h-80 overflow-y-auto pr-1">
                  {courts
                    .filter(c => c.id === isActiveCourtId)
                    .flatMap((court: any) => court.time_slots?.map((time: any) => ({ court, time })) ?? [])
                    .map(({ court, time }: any) => {
                      const isSelected = selectedItems.some((it) => it.court_id === court.id && it.time_slot_id === time.id);

                      let cls = 'opacity-100 hover:opacity-80 transition-opacity duration-200';
                      if (time.status === 'open') {
                        cls += ' bg-green-50 text-green-800 border border-green-200';
                      } else if (time.status === 'booked') {
                        cls += ' bg-red-100 text-[#EF4444] cursor-not-allowed opacity-50'; // Màu Đỏ cho đã đặt
                      } else { // maintenance hoặc status khác
                        cls += ' bg-[#E5E7EB] text-[#6B7280] cursor-not-allowed opacity-70'; // Màu Xám Nhạt cho disable
                      }

                      if (isSelected) cls = 'bg-[#10B981] text-white ring-2 ring-green-300 shadow-md border-transparent'; // Màu Chủ đạo khi được chọn

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
                          className={`text-xs p-2 rounded-md font-semibold flex flex-col justify-center items-center ${cls}`}
                        >
                          <div className='font-bold'>{time.label ?? `${time.start_time?.slice(0, 5)} - ${time.end_time?.slice(0, 5)}`}</div>
                          <div className="text-[10px] mt-0.5 opacity-90">{time.price ? formatPrice(Number(time.price)) : '—'}</div>
                        </button>
                      );
                    })}
                </div>
              </div>

              {/* 4. Tổng và Nút Đặt */}
              <form onSubmit={handleSubmit}>
                <div className="mt-6 pt-4 border-t border-[#E5E7EB] flex justify-between items-center">
                  <div>
                    <p className="text-base text-[#4B5563]">Tổng cộng</p>
                    <p className="text-2xl font-extrabold text-[#F59E0B]">{formatPrice(selectedPrice)}</p>
                  </div>
                  <button
                    type="submit"
                    disabled={selectedItems.length === 0}
                    className={`px-5 py-3 rounded-xl font-bold text-white transition-all duration-300 shadow-lg ${selectedItems.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#10B981] hover:bg-green-700'}`}
                  >
                    <i className="fa-solid fa-calendar-check mr-2" />
                    Đặt ngay ({selectedItems.length})
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      {/* -------------------- FOOTER (Related Venues) -------------------- */}
      <div className="p-6 md:p-8 lg:p-10 border-t border-[#E5E7EB] bg-[#F9FAFB]">
        <h3 className="text-xl font-bold text-[#11182C] mb-4">Các sân thể thao lân cận</h3>
        <div className='text-base text-[#6B7280] italic'>
          [Nơi hiển thị các sân lân cận - cần xây dựng component riêng]
        </div>
      </div>
    </div>
  );
};

export default Detail_Venue;