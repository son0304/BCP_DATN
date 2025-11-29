import React, { useState } from 'react';
import type { Venue } from '../../../Types/venue';
import { Link } from 'react-router-dom';

const Info_Detail_Venue = ({ venue, formatPrice }: { venue: Venue, formatPrice: (price: number) => string; }) => {
  const [selectedComment, setSelectedComment] = useState(false);

  const services = (venue as any).services ?? ['Bãi gửi xe', 'Cho thuê dụng cụ', 'WC & phòng thay đồ', 'Nước uống'];
  const courts = venue.courts ?? [];
  const reviews = venue.reviews ?? [];

  return (
    <div className="lg:col-span-3 space-y-5 order-2 lg:order-1">

      {/* --- 1. GIỚI THIỆU --- */}
      <section className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-3 border-b border-gray-100 pb-2">
            <i className="fa-regular fa-file-lines text-emerald-600"></i>
            <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Giới thiệu</h3>
        </div>
        <p className="text-sm text-gray-600 leading-6 text-justify">
          {venue.description ?? 'Sân đang cập nhật mô tả chi tiết.'}
        </p>
      </section>

      {/* --- 2. TIỆN ÍCH --- */}
      <section className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-4 border-b border-gray-100 pb-2">
            <i className="fa-solid fa-bell-concierge text-emerald-600"></i>
            <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Tiện ích</h3>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
          {services.map((s: string, idx: number) => (
            <div key={idx} className="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 p-2 rounded-md border border-gray-100">
              <i className="fa-solid fa-check text-emerald-500 text-xs" /> 
              <span className="font-medium">{s}</span>
            </div>
          ))}
        </div>
      </section>

      {/* --- 3. DANH SÁCH SÂN CON --- */}
      <section className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-3 border-b border-gray-100 pb-2">
            <i className="fa-solid fa-layer-group text-emerald-600"></i>
            <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Sân con hoạt động</h3>
        </div>
        
        <div className="overflow-hidden rounded-lg border border-gray-100">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <th className="py-3 px-4 text-left">Tên Sân</th>
                <th className="py-3 px-4 text-center">Khung giờ mở</th>
                <th className="py-3 px-4 text-right">Giá từ</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {courts.map((c) => {
                const slots = c.time_slots ?? [];
                const openSlots = slots.filter((s: any) => s.status === 'open');
                const prices = slots.map((s: any) => Number(s.price)).filter(p => !isNaN(p));
                const minPrice = prices.length ? Math.min(...prices) : 0;
                
                return (
                  <tr key={c.id} className="hover:bg-emerald-50/50 transition-colors">
                    <td className="py-3 px-4 font-medium text-gray-800">{c.name}</td>
                    <td className="py-3 px-4 text-center">
                        <span className="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-emerald-100 bg-emerald-600 rounded-full">
                            {openSlots.length}
                        </span>
                    </td>
                    <td className="py-3 px-4 text-right font-bold text-amber-500">
                        {minPrice ? formatPrice(minPrice) : 'Liên hệ'}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </section>

      {/* --- 4. ĐÁNH GIÁ --- */}
      <section id="reviews" className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-4 border-b border-gray-100 pb-2">
            <i className="fa-solid fa-star text-amber-500"></i>
            <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Đánh giá ({reviews?.length})</h3>
        </div>

        {/* Tổng quan điểm số - Layout gọn hơn */}
        <div className="flex items-center gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
          <div className="text-center">
            <div className="text-3xl font-black text-gray-800 leading-none">
              {Number(venue.reviews_avg_rating).toFixed(1)}
            </div>
            <div className="flex justify-center gap-0.5 my-1">
                 {Array.from({ length: 5 }).map((_, i) => (
                    <i key={i} className={`fa-solid fa-star text-xs ${i < Math.round(Number(venue.reviews_avg_rating)) ? 'text-amber-400' : 'text-gray-300'}`}></i>
                 ))}
            </div>
            <p className="text-xs text-gray-500 uppercase font-semibold">Trung bình</p>
          </div>
          
          <div className="flex-1 h-px bg-gray-200 hidden sm:block"></div> {/* Đường kẻ trang trí */}
          
          <div className="text-sm text-gray-500 flex-1">
             <p>Bạn đánh giá thế nào về sân này?</p>
             <button 
                onClick={() => setSelectedComment(true)}
                className="mt-1 text-emerald-600 font-semibold hover:underline text-xs"
             >
                Viết đánh giá ngay
             </button>
          </div>
        </div>

        {/* Danh sách bình luận */}
        {selectedComment ? (
            <div className="animate-fade-in-down">
                {/* Form viết bình luận */}
                <div className="mb-6 bg-white border border-gray-200 p-3 rounded-lg">
                    <textarea
                        placeholder="Chia sẻ trải nghiệm của bạn..."
                        className="w-full text-sm p-2 border-0 focus:ring-0 resize-none outline-none text-gray-700"
                        rows={3}
                    />
                    <div className="flex justify-between items-center mt-2 pt-2 border-t border-gray-100">
                        <label className="cursor-pointer text-gray-400 hover:text-emerald-600 transition">
                            <i className="fa-solid fa-camera mr-1"></i>
                            <span className="text-xs">Thêm ảnh</span>
                            <input type="file" className="hidden" />
                        </label>
                        <button className="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded shadow-sm transition">
                            Gửi
                        </button>
                    </div>
                </div>

                <div className="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                    {reviews.length === 0 && (
                        <p className="text-center text-gray-400 text-sm italic">Chưa có đánh giá nào.</p>
                    )}
                    {reviews.map((review) => (
                        <div key={review.id} className="flex gap-3 border-b border-gray-100 pb-3 last:border-0">
                            {/* Avatar nhỏ gọn */}
                            <div className="flex-shrink-0">
                                {review.user?.avt ? (
                                    <img src={review.user.avt} alt="avt" className="w-8 h-8 rounded-full object-cover" />
                                ) : (
                                    <div className="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                                        {review.user?.name?.charAt(0).toUpperCase() || "K"}
                                    </div>
                                )}
                            </div>
                            
                            <div className="flex-1">
                                <div className="flex justify-between items-start">
                                    <h4 className="text-sm font-bold text-gray-800">{review.user?.name || "Khách"}</h4>
                                    <span className="text-[10px] text-gray-400">{new Date(review.created_at).toLocaleDateString('vi-VN')}</span>
                                </div>
                                <div className="flex text-[10px] text-amber-400 mb-1">
                                    {Array.from({ length: 5 }).map((_, i) => (
                                        <i key={i} className={`fa-solid fa-star ${i < review.rating ? '' : 'text-gray-200'}`}></i>
                                    ))}
                                </div>
                                <p className="text-sm text-gray-600">{review.comment}</p>
                            </div>
                        </div>
                    ))}
                </div>
                
                <button 
                    onClick={() => setSelectedComment(false)} 
                    className="w-full mt-3 py-2 text-center text-xs text-gray-400 hover:text-gray-600"
                >
                    <i className="fa-solid fa-chevron-up mr-1"></i> Thu gọn
                </button>
            </div>
        ) : (
            <button 
                onClick={() => setSelectedComment(true)}
                className="w-full py-2 bg-gray-50 hover:bg-gray-100 text-emerald-600 text-sm font-medium rounded transition border border-dashed border-emerald-200"
            >
                Xem tất cả {reviews.length} đánh giá
            </button>
        )}

      </section>
    </div>
  )
}

export default Info_Detail_Venue;