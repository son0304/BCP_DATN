import React, { useEffect, useState, useMemo } from 'react';
import { useFetchDataById } from '../../../Hooks/useApi';
import { useParams } from 'react-router-dom';
import type { Venue } from '../../../Types/venue';
import { fetchData } from '../../../Api/fetchApi';

import Gallery_Detail_Venue from './Gallery_Detail_Venue';
import Info_Detail_Venue from './Info_Detail_Venue';
import Booking_Detail_Venue from './Booking_Detail_Venue';

const Index_Detail_Venue: React.FC = () => {
  const rawUser = typeof window !== 'undefined' ? localStorage.getItem('user') : null;
  const user = useMemo(() => {
    try {
      return rawUser ? JSON.parse(rawUser) : null;
    } catch {
      return null;
    }
  }, [rawUser]);

  const [selectedDate, setSelectedDate] = useState<string>(new Date().toISOString().slice(0, 10));
  const [relatedVenues, setRelatedVenues] = useState<Venue[]>([]);
  const [relatedLoading, setRelatedLoading] = useState<boolean>(false);

  const { id } = useParams<{ id: string }>();
  const idVenue = Number(id);
  const { data: detail_venue, isLoading, refetch } = useFetchDataById<Venue>('venue', idVenue, { date: selectedDate });

  useEffect(() => {
    const loadRelated = async () => {
      try {
        setRelatedLoading(true);
        const currentId = detail_venue?.data?.id;
        const res = await fetchData<any>('venues');
        const list = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : [];
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

  if (isLoading || !detail_venue) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[500px] bg-white">
        <div className="w-10 h-10 border-4 border-[#10B981] border-t-transparent rounded-full animate-spin"></div>
        <p className="mt-3 text-xs text-gray-500 font-medium">Đang tải dữ liệu...</p>
      </div>
    );
  }

  const venue: Venue = detail_venue.data;
  const formatPrice = (price: number) => !price || isNaN(price) ? '0₫' : price.toLocaleString('vi-VN') + '₫';

  return (
    <div className="max-w-7xl mx-auto my-6 px-4 md:px-6">
      
      {/* 1. TOP: GALLERY & BASIC INFO */}
      <Gallery_Detail_Venue venue={venue} formatPrice={formatPrice} />

      {/* 2. BODY: BOOKING (LEFT) & DETAIL INFO (RIGHT) */}
      <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        {/* Booking Section (Main) - Vị trí bên TRÁI */}
        {/* Lưu ý: Nếu trong file Booking_Detail_Venue.tsx có class 'lg:order-2', 
            bạn nên sửa nó thành 'lg:order-1' hoặc xóa class order đi để nó tự động nằm bên trái. */}
        <Booking_Detail_Venue
            venue={venue}
            user={user}
            refetch={refetch}
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
        />

        {/* Info Detail Section (Sidebar) - Vị trí bên PHẢI */}
        {/* Thay đổi: lg:order-1 thành lg:order-2 (hoặc last) */}
        <div className="lg:col-span-2 order-2 lg:order-2 space-y-6">
            <Info_Detail_Venue venue={venue} formatPrice={formatPrice} />
            
            {/* Related Venues Placeholder */}
            <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <h4 className="text-sm font-bold text-gray-800 mb-3 border-b border-gray-50 pb-2">
                    Gợi ý sân gần đây
                </h4>
                {relatedLoading ? (
                    <div className="text-xs text-gray-400 italic">Đang tải...</div>
                ) : (
                    <div className="space-y-3">
                        {relatedVenues.length > 0 ? relatedVenues.map(v => (
                            <div key={v.id} className="flex gap-2 items-center hover:bg-gray-50 p-1 rounded cursor-pointer transition">
                                <div className="w-12 h-12 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                     <img src={(v as any).images?.[0]?.url || 'https://via.placeholder.com/50'} className="w-full h-full object-cover" alt=""/>
                                </div>
                                <div>
                                    <p className="text-xs font-bold text-gray-700 line-clamp-1">{v.name}</p>
                                    <p className="text-[10px] text-gray-400 line-clamp-1">{v.address_detail}</p>
                                </div>
                            </div>
                        )) : <p className="text-xs text-gray-400">Không có sân nào khác.</p>}
                    </div>
                )}
            </div>
        </div>

      </div>
    </div>
  );
};

export default Index_Detail_Venue;