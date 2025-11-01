// Detail_Venue.tsx
import React, { useEffect, useState, useMemo } from 'react';
import { useFetchDataById, usePostData } from '../../Hooks/useApi';
import { useNavigate, useParams } from 'react-router-dom';
import type { Venue, EnrichedTimeSlot } from '../../Types/venue';
import type { Image } from '../../Types/image';
import type { Court } from '../../Types/court';
import type { ApiResponse } from '../../Types/api';
import { useNotification } from '../../Components/Notification';
import { fetchData } from '../../Api/fetchApi';

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
  // safe parse user
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
  const { mutate } = usePostData<ApiResponse<number>, any>('tickets');
  if (detail_venue) {
    console.log(detail_venue);

  }
  // Recalculate total whenever selectedItems change
  useEffect(() => {
    const total = selectedItems.reduce((sum, item) => sum + Number(item.price || 0), 0);
    setSelectedPrice(total);
  }, [selectedItems]);

  // Refetch when date changes and clear selection
  useEffect(() => {
    refetch();
    setSelectedItems([]);
  }, [selectedDate, refetch]);

  // Sync active court when detail_venue loads/changes
  useEffect(() => {
    if (detail_venue?.data?.courts?.length && isActiveCourtId === null) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    } else if (detail_venue?.data?.courts?.length && !detail_venue.data.courts.some((c: any) => c.id === isActiveCourtId)) {
      setIsActiveCourtId(detail_venue.data.courts[0].id);
    }
  }, [detail_venue, isActiveCourtId]);

  // Load related venues (best-effort, not critical)
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
      <div className="flex items-center justify-center h-full min-h-[560px] bg-gray-50 rounded-xl shadow-inner">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#348738]" />
        <p className="ml-4 text-[#348738] font-semibold">Đang tải lịch sân...</p>
      </div>
    );
  }

  // Data normalization
  const venue: Venue = detail_venue.data;
  const images: Image[] = (venue as any).images ?? (venue as any).photos ?? [];
  const primaryImage = images.find((img: Image) => img.is_primary === 1) ?? images[0] ?? { url: 'https://placehold.co/1200x700/348738/ffffff?text=BCP+Sports' };
  const gallery = images.length > 0 ? images : [primaryImage];
  const courts = venue.courts ?? [];

  // Placeholder services/reviews structure if API doesn't provide
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
      unit_price: Number(item.price), // ensure numeric
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
        // Axios error may carry response data
        const msg = error?.response?.data?.message ?? error?.message ?? 'Đã xảy ra lỗi.';
        showNotification(`Đã xảy ra lỗi: ${msg}`, 'error');
        console.error(error);
      },
    });
  };

  const formatPrice = (price: number) => price.toLocaleString('vi-VN') + '₫';

  // derive price range if possible
  const priceRange = (() => {
    const prices: number[] = [];
    courts.forEach((c: any) => c.time_slots?.forEach((t: any) => { if (t.price) prices.push(Number(t.price)); }));
    if (prices.length === 0) return null;
    const min = Math.min(...prices), max = Math.max(...prices);
    return min === max ? `${formatPrice(min)}` : `${formatPrice(min)} - ${formatPrice(max)}`;
  })();

  return (
    <div className="max-w-6xl mx-auto my-8 bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
      {/* Top hero + summary */}
      <div className="md:flex">
        <div className="md:w-2/3 relative">
          <div
            className="h-72 md:h-[460px] bg-cover bg-center"
            style={{ backgroundImage: `url(${primaryImage.url})` }}
          >
            <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
            <div className="absolute bottom-6 left-6 text-white">
              <h1 className="text-3xl md:text-4xl font-extrabold leading-tight">{venue.name}</h1>
              <div className="mt-2 flex flex-wrap gap-4 text-sm opacity-90">
                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-star text-yellow-400" />
                  <span className="font-semibold">{Number(reviews.avg_rating ?? 0).toFixed(1)}/5.0</span>
                  <span className="text-gray-200">({reviews.total})</span>
                </div>

                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-location-dot text-orange-400" />
                  <span>{(venue as any).address_detail ?? (venue as any).address ?? 'Địa chỉ đang cập nhật'}</span>
                </div>

                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-phone text-green-300" />
                  <span>{venue.phone ?? 'Chưa có'}</span>
                </div>

                {venue.start_time && venue.end_time && (
                  <div className="flex items-center gap-2">
                    <i className="fa-regular fa-clock text-[#2d6a2d]" />
                    <span className="font-medium">{venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}</span>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* gallery thumbnails */}
          <div className="p-4 md:p-5 border-t border-gray-100 bg-white">
            <div className="flex gap-3 overflow-x-auto pb-1">
              {gallery.map((img: any, idx: number) => (
                <button
                  key={idx}
                  onClick={() => setGalleryIndex(idx)}
                  className={`flex-shrink-0 w-28 h-16 rounded-lg overflow-hidden border ${galleryIndex === idx ? 'ring-2 ring-offset-1 ring-[#348738]' : 'border-gray-200'}`}
                >
                  <img src={img.url} alt={`${venue.name}-img-${idx}`} className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Right summary & sticky booking (sidebar) */}
        <aside className="md:w-1/3 p-6 border-l border-gray-100 bg-gradient-to-b from-white to-gray-50">
          <div className="space-y-4">
            <div>
              <p className="text-sm text-gray-500">Giá tham khảo</p>
              <h3 className="text-2xl font-bold text-[#2d6a2d]">{priceRange ?? 'Liên hệ'}</h3>
            </div>

            <div>
              <p className="text-sm text-gray-500">Giờ mở cửa</p>
              {venue.start_time && venue.end_time ? (
                <p className="mt-1 font-medium">{venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}</p>
              ) : (
                <p className="mt-1 font-medium">Đang cập nhật</p>
              )}
            </div>

            <div>
              <p className="text-sm text-gray-500">Dịch vụ</p>
              <ul className="mt-2 grid grid-cols-1 gap-1 text-sm text-gray-700">
                {services.map((s: string, i: number) => (
                  <li key={i} className="flex items-center gap-2">
                    <i className="fa-solid fa-check text-green-400 text-xs" />
                    <span>{s}</span>
                  </li>
                ))}
              </ul>
            </div>

            <div>
              <p className="text-sm text-gray-500">Liên hệ nhanh</p>
              <div className="mt-2 flex gap-2">
                <button className="flex-1 inline-flex items-center justify-center gap-2 py-2 rounded-lg bg-[#348738] text-white font-semibold">
                  <i className="fa-solid fa-phone" />
                  <span>{venue.phone ?? 'Chưa có'}</span>
                </button>
                <button
                  onClick={() => showNotification('Chức năng chat (placeholder).')}
                  className="px-4 py-2 rounded-lg border border-gray-200"
                >
                  Chat
                </button>
              </div>
            </div>
          </div>
        </aside>
      </div>

      {/* Main booking + info area */}
      <div className="p-8 lg:p-10 space-y-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Left content (description, courts, reviews, related) */}
          <div className="lg:col-span-2 space-y-6">
            {/* Court list */}
            <section className="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <h3 className="text-lg font-semibold mb-3">Danh sách sân con</h3>
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="text-left text-gray-500">
                      <th className="py-2">Sân</th>
                      <th className="py-2">Số khung giờ mở</th>
                      <th className="py-2">Giá từ</th>
                    </tr>
                  </thead>
                  <tbody>
                    {courts.length === 0 && (
                      <tr className="border-t">
                        <td className="py-3 text-gray-500" colSpan={3}>Chưa có sân nào.</td>
                      </tr>
                    )}
                    {courts.map((c: any) => {
                      const slots = (c.time_slots ?? []) as any[];
                      const openSlots = slots.filter(s => s.status === 'open');
                      const prices = slots.map(s => Number(s.price)).filter(p => !isNaN(p));
                      const minPrice = prices.length ? Math.min(...prices) : null;
                      return (
                        <tr key={c.id} className="border-t">
                          <td className="py-2 font-medium">{c.name}</td>
                          <td className="py-2">{openSlots.length}</td>
                          <td className="py-2">{minPrice !== null ? formatPrice(minPrice) : '—'}</td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </section>

            {/* Reviews */}
            <section className="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <div className="flex items-center justify-between">
                <h3 className="text-lg font-semibold">Đánh giá</h3>
                <div className="text-sm text-gray-600">
                  <span className="font-bold text-[#2d6a2d] mr-1">{Number(reviews.avg_rating ?? 0).toFixed(1)}</span>
                  <span> / 5 ({reviews.total})</span>
                </div>
              </div>

              <div className="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                {reviews.breakdown.map((r: any, idx: number) => (
                  <div key={idx} className="p-3 rounded-lg bg-gray-50">
                    <p className="text-sm text-gray-600">{r.title}</p>
                    <p className="mt-2 font-semibold">{(r.score ?? 0).toFixed(1)}/5</p>
                  </div>
                ))}
              </div>

              <div className="mt-4">
                <div className="border-t pt-4 text-sm text-gray-700">
                  <p className="font-medium">Nguyễn A</p>
                  <p className="text-xs text-gray-500">2 tuần trước</p>
                  <p className="mt-2">Sân mới, nhân viên thân thiện. Rất thích!</p>
                </div>
                <button onClick={() => showNotification('Xem tất cả đánh giá (placeholder)')} className="mt-3 text-sm underline text-[#348738]">
                  Xem tất cả đánh giá
                </button>
              </div>
            </section>

            {/* Related venues */}
            <section className="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
              <h3 className="text-lg font-semibold mb-3">Sân liên quan</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {relatedLoading && (
                  <div className="col-span-full text-sm text-gray-500">Đang tải sân liên quan...</div>
                )}
                {!relatedLoading && relatedVenues.length === 0 && (
                  <div className="col-span-full text-sm text-gray-500">Chưa có sân liên quan.</div>
                )}
                {!relatedLoading && relatedVenues.map((r: any) => {
                  const images: Image[] = (r as any).images ?? (r as any).photos ?? [];
                  const primary = images.find((img: Image) => img.is_primary === 1) ?? images[0] ?? { url: 'https://placehold.co/300x200/348738/ffffff?text=S%C3%A2n' };
                  return (
                    <button
                      key={r.id}
                      onClick={() => navigate(`/venues/${r.id}`)}
                      className="flex items-start gap-3 p-3 border rounded-lg text-left hover:border-[#348738] transition-all hover:shadow-sm"
                    >
                      <div className="w-20 h-20 flex-shrink-0 overflow-hidden rounded-lg border border-gray-100">
                        <img src={primary.url} alt={r.name} className="w-full h-full object-cover" />
                      </div>
                      <div className="flex-1">
                        <p className="font-medium text-gray-800 line-clamp-1">{r.name || 'Địa điểm'}</p>
                        <p className="text-xs text-gray-600 mt-1 line-clamp-2">{(r as any).address_detail || 'Địa chỉ đang cập nhật'}</p>
                        {r.phone && <p className="text-xs text-gray-500 mt-1"><i className="fa-solid fa-phone text-[#348738] mr-1" />{r.phone}</p>}
                      </div>
                    </button>
                  );
                })}
              </div>
            </section>
          </div>

          {/* Right: booking controls (sticky sidebar) */}
          <div className="space-y-6">
            <div className="bg-white rounded-xl p-6 border border-gray-100 shadow sticky top-24">
              <h4 className="text-lg font-semibold mb-3">Đặt sân</h4>

              <div className="mb-3">
                <label className="text-sm text-gray-600">Chọn ngày</label>
                <input
                  type="date"
                  className="w-full mt-2 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#348738]"
                  value={selectedDate}
                  onChange={handleChangeDate}
                  min={new Date().toISOString().slice(0, 10)}
                />
              </div>

              <div className="mb-4">
                <label className="text-sm text-gray-600">Chọn sân</label>
                <div className="mt-2 flex flex-wrap gap-2">
                  {courts.map((court: Court) => (
                    <button
                      key={court.id}
                      onClick={() => setIsActiveCourtId(court.id)}
                      className={`px-3 py-2 rounded-lg text-sm border ${isActiveCourtId === court.id ? 'bg-[#348738] text-white' : 'bg-white'}`}
                    >
                      {court.name}
                    </button>
                  ))}
                </div>
              </div>

              {/* Time slots for active court */}
              <div>
                <p className="text-sm text-gray-600 mb-2">Khung giờ ({selectedItems.length} đã chọn)</p>
                <div className="grid grid-cols-3 sm:grid-cols-4 gap-2">
                  {courts
                    .filter(c => c.id === isActiveCourtId)
                    .flatMap((court: any) => court.time_slots?.map((time: any) => ({ court, time })) ?? [])
                    .map(({ court, time }: any) => {
                      const isSelected = selectedItems.some((it) => it.court_id === court.id && it.time_slot_id === time.id);
                      let base = 'text-xs p-2 rounded-md font-semibold';
                      let cls = time.status === 'open'
                        ? 'bg-green-50 text-green-700 border border-green-200'
                        : time.status === 'booked'
                          ? 'bg-red-50 text-red-600 cursor-not-allowed'
                          : time.status === 'maintenance'
                            ? 'bg-gray-100 text-gray-500 cursor-not-allowed'
                            : 'bg-yellow-50 text-yellow-700 cursor-not-allowed';
                      if (isSelected) cls = 'bg-orange-500 text-white ring-2 ring-orange-300';
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
                          className={`${base} ${cls}`}
                        >
                          <div>{time.label ?? `${time.start_time?.slice(0, 5)} - ${time.end_time?.slice(0, 5)}`}</div>
                          <div className="text-[10px] mt-1">{time.price ? formatPrice(Number(time.price)) : '-'}</div>
                        </button>
                      );
                    })}
                </div>
              </div>

              <form onSubmit={handleSubmit}>
                <div className="mt-4">
                  <div className="flex justify-between items-center">
                    <div>
                      <p className="text-sm text-gray-600">Tổng</p>
                      <p className="text-xl font-bold text-orange-600">{formatPrice(selectedPrice)}</p>
                    </div>
                    <div>
                      <button
                        type="submit"
                        disabled={selectedItems.length === 0}
                        className={`px-4 py-2 rounded-lg font-bold text-white ${selectedItems.length === 0 ? 'bg-gray-300' : 'bg-gradient-to-r from-[#348738] to-green-700'}`}
                      >
                        Đặt ngay
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            {/* Contact / location box */}
            <div className="bg-white rounded-xl shadow-md p-5 mt-6 border border-gray-100">
              <div className="flex items-center justify-between mb-3">
                <h2 className="text-lg font-semibold text-gray-700">Địa điểm / Liên lạc</h2>
              </div>

              <div className="border-t border-gray-100 pt-3 space-y-3 text-sm">
                {venue.address_detail && (
                  <div className="flex flex-wrap items-start">
                    <i className="fa-solid fa-location-dot text-[#348738] w-5 mt-0.5" />
                    <span className="font-semibold text-gray-700 mr-2 whitespace-nowrap">Địa chỉ:</span>
                    <span className="text-gray-600 break-words flex-1 min-w-[200px]">{venue.address_detail}</span>
                  </div>
                )}

                {venue.phone && (
                  <div className="flex items-start">
                    <i className="fa-solid fa-phone text-[#348738] w-5 mt-0.5" />
                    <span className="font-semibold text-gray-700 mr-2">Phone:</span>
                    <span className="text-gray-600">{venue.phone}</span>
                  </div>
                )}

                <div className="flex items-start">
                  <i className="fa-solid fa-user-tie text-[#348738] w-5 mt-0.5" />
                  <span className="font-semibold text-gray-700 mr-2">Chủ sân:</span>
                  <span className="text-gray-600">{venue.name}</span>
                </div>
              </div>
            </div>

            {/* Small tips / rules */}
            <div className="bg-white rounded-xl p-4 border border-gray-100 text-sm text-gray-600">
              <p className="font-semibold mb-2">Lưu ý</p>
              <ul className="list-disc ml-5 space-y-1">
                <li>Vui lòng đến trước 10 phút để chuẩn bị.</li>
                <li>Quy định hủy: hủy trước 24 giờ hoàn tiền 100% (placeholder).</li>
                <li>Liên hệ hotline để hỗ trợ đặt số lượng lớn.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Detail_Venue;
