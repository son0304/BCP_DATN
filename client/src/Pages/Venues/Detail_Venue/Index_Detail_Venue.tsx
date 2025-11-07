import React, { useEffect, useState, useMemo } from 'react';
import { useFetchDataById } from '../../../Hooks/useApi';
import { useParams } from 'react-router-dom';
import type { Venue } from '../../../Types/venue';
import { fetchData } from '../../../Api/fetchApi';

import Gallery_Detail_Venue from './Gallery_Detail_Venue';
import Info_Detail_Venue from './Info_Detail_Venue';
import Booking_Detail_Venue from './Booking_Detail_Venue';

const Index_Detail_Venue: React.FC = () => {
  // ======= Lấy thông tin user từ localStorage =======
  const rawUser = typeof window !== 'undefined' ? localStorage.getItem('user') : null;
  const user = useMemo(() => {
    try {
      return rawUser ? JSON.parse(rawUser) : null;
    } catch {
      return null;
    }
  }, [rawUser]);

  // ======= State =======
  const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
  const [relatedVenues, setRelatedVenues] = useState<Venue[]>([]);
  const [relatedLoading, setRelatedLoading] = useState<boolean>(false);

  // ======= Lấy params & dữ liệu sân =======
  const { id } = useParams<{ id: string }>();
  const idVenue = Number(id);
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', idVenue, { date: selectedDate });

  // ======= Load sân lân cận =======
  useEffect(() => {
    const loadRelated = async () => {
      try {
        setRelatedLoading(true);
        const currentId = detail_venue?.data?.id;
        const res = await fetchData<any>('venues');
        const list = Array.isArray(res?.data)
          ? res.data
          : Array.isArray(res?.data?.data)
          ? res.data.data
          : [];
        const items = list.filter((v: any) => v.id !== currentId).slice(0, 4);
        setRelatedVenues(items.length ? items : detail_venue?.data ? [detail_venue.data] : []);
      } catch (err) {
        console.error('loadRelated error:', err);
        setRelatedVenues([]);
      } finally {
        setRelatedLoading(false);
      }
    };
    loadRelated();
  }, [detail_venue?.data?.id]);

  // ======= Loading UI =======
  if (isLoading || !detail_venue) {
    return (
      <div className="flex items-center justify-center h-full min-h-[560px] bg-[#F9FAFB] rounded-xl shadow-inner">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#10B981]" />
        <p className="ml-4 text-[#10B981] font-semibold">Đang tải lịch sân...</p>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;

  const formatPrice = (price: number) =>
    !price || isNaN(price) ? '0₫' : price.toLocaleString('vi-VN') + '₫';

  // ======= Render =======
  return (
    <div className="max-w-7xl mx-auto my-8 bg-[#F9FAFB] rounded-2xl shadow-2xl overflow-hidden border border-[#E5E7EB]">
      {/* ================= HERO + GALLERY ================= */}
      <Gallery_Detail_Venue venue={venue} formatPrice={formatPrice} />

      {/* ================= MAIN CONTENT ================= */}
      <div className="p-2 md:p-8 lg:p-10 space-y-10">
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
          {/* === CỘT TRÁI === */}
          <Info_Detail_Venue venue={venue} formatPrice={formatPrice} />

          {/* === CỘT PHẢI: ĐẶT LỊCH === */}
          <Booking_Detail_Venue
            venue={venue}
            user={user}
            refetch={refetch}
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
          />
        </div>
      </div>

      {/* ================= FOOTER ================= */}
      <div className="p-6 md:p-8 lg:p-10 border-t border-[#E5E7EB] bg-[#F9FAFB]">
        <h3 className="text-lg font-bold text-[#11182C] mb-4">Các sân thể thao lân cận</h3>
        {relatedLoading ? (
          <p className="text-gray-500 italic">Đang tải danh sách sân lân cận...</p>
        ) : (
          <div className="text-base text-[#6B7280] italic">
            [Nơi hiển thị các sân lân cận - cần xây dựng component riêng]
          </div>
        )}
      </div>
    </div>
  );
};

export default Index_Detail_Venue;
