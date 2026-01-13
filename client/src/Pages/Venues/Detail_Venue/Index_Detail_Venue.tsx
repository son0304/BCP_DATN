import React, { useEffect, useState, useMemo } from 'react';
import { useFetchData, useFetchDataById } from '../../../Hooks/useApi';
import { useParams } from 'react-router-dom';
import type { Venue } from '../../../Types/venue';
import { fetchData } from '../../../Api/fetchApi';

import Gallery_Detail_Venue from './Gallery_Detail_Venue'; // Đã chứa logic hiển thị ảnh và thông tin
import { useNotification } from '../../../Components/Notification';
import Order_Container from './Order/Order_Container';
import Review_Venue from './Review';
import type { Ticket } from '../../../Types/tiket';
import RelatedVenue from '../RelatedVenues';

const Index_Detail_Venue: React.FC = () => {
  const rawUser = typeof window !== 'undefined' ? localStorage.getItem('user') : null;
  const user = useMemo(() => {
    try {
      return rawUser ? JSON.parse(rawUser) : null;
    } catch {
      return null;
    }
  }, [rawUser]);

  const { showNotification } = useNotification();
  const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
  const [relatedLoading, setRelatedLoading] = useState<boolean>(false);

  const { id } = useParams<{ id: string }>();
  const idVenue = Number(id);
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', idVenue, { date: selectedDate });
  console.log('detail_venue', detail_venue);

  const { data } = useFetchData('tickets')
  const tickets = data?.data as Ticket[] || [];
  const promotions = detail_venue?.data?.promotions || [];
  console.log('promotions', promotions);


  // Logic load related giữ nguyên...
  useEffect(() => {
    const loadRelated = async () => {
      try {
        setRelatedLoading(true);
        const currentId = detail_venue?.data?.id;
        const res = await fetchData<any>('venues');
        const list = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : [];
        const items = list.filter((v: any) => v.id !== currentId).slice(0, 4);
        setRelatedVenues(items.length ? items : detail_venue?.data ? [detail_venue.data] : []);
      } catch (err: any) {
        setRelatedVenues([]);
      } finally {
        setRelatedLoading(false);
      }
    };
    if (detail_venue?.data?.id) loadRelated();
  }, [detail_venue?.data?.id]);

  if (isLoading || !detail_venue) return <div className="p-10 text-center">Đang tải...</div>;

  const venue: Venue = detail_venue.data;
  const reviews = venue.reviews ?? [];

  const formatPrice = (price: number) => !price || isNaN(price) ? '0₫' : price.toLocaleString('vi-VN') + '₫';

  return (
    <div className="max-w-7xl mx-auto my-6 px-4 md:px-6 space-y-8">

      {/* 1. TOP: GALLERY & INFO ĐÃ GỘP (Full Width) */}
      <section>
        <Gallery_Detail_Venue venue={venue} formatPrice={formatPrice} />
      </section>

      {/* 2. MIDDLE: BOOKING (Nằm riêng 1 khối Div để Full Ngang) */}
      <section className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="border-b border-gray-100 pb-4 mb-6">
          <h3 className="text-xl font-bold text-gray-800 flex items-center gap-2">
            📅 Đặt lịch & Dịch vụ
          </h3>
          <p className="text-sm text-gray-500 mt-1">Chọn khung giờ bạn muốn đặt và thêm các dịch vụ đi kèm.</p>
        </div>

        {/* Booking component sẽ tự giãn ra 100% chiều rộng của cha */}
        <Order_Container id={venue.id} promotions={promotions} />
      </section>

      {/* 3. BOTTOM: REVIEWS & RELATED (Chia cột ở dưới cùng) */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {/* Cột trái: Đánh giá (Chiếm 2/3) */}
        <div className="lg:col-span-2">
          {/* Nếu bạn có component Review_Venue */}
          {/* <Review_Venue venue={venue} ... /> */}

          {/* Placeholder nếu chưa import Review */}
          <div className="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h3 className="font-bold text-lg mb-4">Đánh giá từ khách hàng</h3>
            <Review_Venue venue={venue} reviews={reviews} tickets={tickets} user={user} refetch={refetch} />
            <p className="text-gray-500">Danh sách đánh giá sẽ hiện ở đây...</p>
          </div>
        </div>

        {/* Cột phải: Gợi ý sân (Chiếm 1/3) */}
        <div className="lg:col-span-1">
          <div className="bg-white p-5 rounded-xl border border-gray-200 shadow-sm sticky top-6">
            <h4 className="text-sm font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100">
              Gợi ý sân khác
            </h4>
            {relatedLoading ? (
              <div className="text-center text-xs text-gray-400">Đang tải...</div>
            ) : (
              <RelatedVenue currentVenueId={venue.id} />
            )}
          </div>
        </div>

      </div>

    </div>
  );
};

export default Index_Detail_Venue;