import React, { useState } from 'react'
import type { Venue } from '../../../Types/venue';
import type { Review } from '../../../Types/review';
import { Link } from 'react-router-dom';

const Info_Detail_Venue = ({ venue, formatPrice }: { venue: Venue, formatPrice: (price: number) => string; }) => {
  const [selectedComment, setSelectedComment] = useState(false)

  const services = (venue as any).services ?? ['Bãi gửi xe', 'Cho thuê dụng cụ', 'WC & phòng thay đồ', 'Nước uống'];
  const courts = venue.courts ?? [];
  const reviews = venue.reviews ?? [];





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
      <section id="reviews" className="bg-white rounded-xl p-6 border border-gray-200 shadow-lg">
        {/* Header điểm đánh giá */}
        <div className="flex flex-wrap items-center gap-8 mb-6">
          <div className="text-center flex-shrink-0">
            <p className="text-3xl font-extrabold text-green-500">
              {Number(venue.reviews_avg_rating).toFixed(1)}
            </p>
            <p className="text-sm text-gray-500">Điểm trung bình</p>
          </div>

          <div className="flex-1 space-y-2">
            <div className="h-3 w-full bg-gray-200 rounded-full overflow-hidden">
              <div
                className="h-full bg-yellow-500 transition-all duration-500"
                style={{ width: `${(reviews?.length / 5) * 100}%` }}
              />
            </div>
            <p className="text-sm text-gray-500 mt-1">{reviews?.length} đánh giá</p>
          </div>
        </div>

        {/* Danh sách bình luận */}
        {
          selectedComment && (
            <div className="bg-gray-50 rounded-xl p-4 shadow-inner max-h-96 overflow-auto mb-4">
              <h4 className="text-lg font-semibold text-gray-700 mb-4">Danh sách bình luận</h4>

              {reviews.length === 0 && (
                <p className="text-sm text-gray-500">Chưa có bình luận nào</p>
              )}

              {reviews.map((review) => (
                <div key={review.id} className="mb-4 border-b border-gray-200 pb-3">

                  {/* Avatar + Tên */}
                  <div className="flex items-center gap-3 mb-1">
                    {review.user?.avt ? (
                      <img
                        src={review.user.avt}
                        alt="avatar"
                        className="w-8 h-8 rounded-full object-cover border border-gray-300"
                      />
                    ) : (
                      <div className="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-semibold">
                        {review.user?.name?.charAt(0).toUpperCase() || "K"}
                      </div>
                    )}

                    <p className="font-medium text-gray-800 text-sm">
                      {review.user?.name || "Khách"}
                    </p>
                  </div>

                  {/* Sao đánh giá */}
                  <div className="flex items-center gap-1">
                    {Array.from({ length: review.rating }).map((_, index) => (
                      <i key={index} className="fa-solid fa-star text-[#F59E0B] text-xs" />
                    ))}
                    {Array.from({ length: 5 - review.rating }).map((_, index) => (
                      <i key={index} className="fa-solid fa-star text-gray-300 text-xs" />
                    ))}
                  </div>

                  {/* Nội dung bình luận */}
                  <p className="text-gray-600 text-sm mt-1">{review.comment}</p>

                  {/* Thời gian */}
                  <p className="text-xs text-gray-400 mt-1">
                    {new Date(review.created_at).toLocaleDateString()}
                  </p>
                </div>

              ))}
            </div>
          )
        }

        {/* Nút xem tất cả bình luận */}
        {
          !selectedComment ? (
            <div className="mt-6 text-center">
              <button
                onClick={() => setSelectedComment(true)}
                className="text-green-600 font-semibold hover:underline transition text-base"
              >
                Xem tất cả đánh giá và bình luận
              </button>
            </div>
          ) : (
            <div className="mt-6 border-t border-gray-200 pt-5">

              {/* Form viết bình luận */}
              <div className="flex flex-col gap-3 mb-6">
                <textarea
                  placeholder="Viết bình luận của bạn..."
                  className="w-full p-3 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-green-400 transition"
                  rows={4}
                />

                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <input type="file" id="review-image" className="hidden" />
                    <label
                      htmlFor="review-image"
                      className="px-4 py-2 bg-green-100 text-green-700 rounded-lg cursor-pointer hover:bg-green-200 transition"
                    >
                      Chọn ảnh
                    </label>
                    <span className="text-sm text-gray-500">Tối đa 1 ảnh</span>
                  </div>

                  <button
                    className="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold"
                  >
                    Gửi bình luận
                  </button>
                </div>
              </div>

              {/* Nút thu gọn */}
              <div className="text-center">
                <button
                  onClick={() => setSelectedComment(false)}
                  className="text-gray-500 hover:text-gray-700 hover:underline transition text-sm"
                >
                  Thu gọn ▲
                </button>
              </div>

            </div>
          )
        }

      </section>

    </div>
  )
}

export default Info_Detail_Venue