import React from 'react'
import type { Venue } from '../../../Types/venue';
import type { Review } from '../../../Types/review';

const Info_Detail_Venue = ({ venue, formatPrice }: { venue: Venue, formatPrice: (price: number) => string; }) => {

  const services = (venue as any).services ?? ['Bãi gửi xe', 'Cho thuê dụng cụ', 'WC & phòng thay đồ', 'Nước uống'];
  const courts = venue.courts ?? [];

  const reviews = (venue as any).reviews ?? {
    avg_rating: (venue as any).reviews_avg_rating ?? 4.6,
    total: (venue as any).reviews_count ?? 18,
    breakdown: [
      { title: 'Chất lượng sân', score: 4.6 },
      { title: 'Dịch vụ', score: 4.4 },
      { title: 'Vị trí', score: 4.7 },
    ],
  };

  return (
    <div className="lg:col-span-3 space-y-8 order-2 lg:order-1">

      {/* Giới thiệu */}
      <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
        <h3 className="text-lg font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Giới thiệu</h3>
        <p className="text-base text-[#4B5563] leading-relaxed">
          {venue.description ?? 'Sân đang cập nhật mô tả chi tiết.'}
        </p>
      </section>

      {/* Tiện ích */}
      <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
        <h3 className="text-lg font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Tiện ích tại sân</h3>
        <ul className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-base text-[#4B5563]">
          {services.map((s: string, idx: number) => (
            <li key={idx} className="flex items-center gap-2 font-medium">
              <i className="fa-solid fa-circle-check text-[#10B981]" /> {s}
            </li>
          ))}
        </ul>
      </section>

      {/* Danh sách sân con */}
      <section className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
        <h3 className="text-lg font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Các sân con đang hoạt động</h3>
        <div className="overflow-x-auto">
          <table className="w-full text-base">
            <thead>
              <tr className="text-left text-sm text-[#6B7280] uppercase tracking-wider bg-[#F9FAFB]">
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

      {/* Đánh giá */}
      <section id="reviews" className="bg-white rounded-xl p-6 border border-[#E5E7EB] shadow-lg">
        <h3 className="text-lg font-bold text-[#11182C] mb-4 border-b border-[#E5E7EB] pb-2">Đánh giá khách hàng ({reviews.total})</h3>
        <div className="flex flex-wrap gap-8 items-center">
          <div className="text-center">
            <p className="text-2xl font-extrabold text-[#10B981]">{Number(reviews.avg_rating).toFixed(1)}</p>
            <p className="text-sm text-[#6B7280]">Điểm trung bình</p>
          </div>
          <div className="flex-1 space-y-2">
            {reviews.breakdown.map((r: any, i: number) => (
              <div key={i} className="flex items-center text-sm text-[#4B5563]">
                <span className="w-24 text-[#6B7280]">{r.title}</span>
                <div className="flex-1 h-2 mx-3 bg-[#E5E7EB] rounded-full overflow-hidden">
                  <div
                    className="h-full bg-[#F59E0B]"
                    style={{ width: `${(r.score / 5) * 100}%` }}
                  />
                </div>
                <span className="font-semibold">{r.score.toFixed(1)}</span>
              </div>
            ))}
          </div>
        </div>
        <div className="mt-6 pt-4 border-t border-[#E5E7EB] text-center">
          <button className="text-[#10B981] font-semibold hover:underline text-base">
            Xem tất cả đánh giá và bình luận
          </button>
        </div>
      </section>
    </div>
  )
}

export default Info_Detail_Venue