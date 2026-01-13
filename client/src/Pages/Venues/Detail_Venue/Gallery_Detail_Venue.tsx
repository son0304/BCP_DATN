import React, { useState } from "react";
import type { Image } from "../../../Types/image";
import type { Venue } from "../../../Types/venue";
import { useFetchDataById } from "../../../Hooks/useApi";
import type { Province } from "../../../Types/province";
import type { District } from "../../../Types/district";
import type { User } from "../../../Types/user";

// Props nhận vào đủ dữ liệu để hiển thị
interface GalleryProps {
    venue: Venue;
    formatPrice: (price: number) => string;
}

const Gallery_Detail_Venue: React.FC<GalleryProps> = ({ venue, formatPrice }) => {
    const [galleryIndex, setGalleryIndex] = useState<number>(0);
    const [showFullDesc, setShowFullDesc] = useState<boolean>(false); // State để xem thêm mô tả

    // Fetch các dữ liệu liên quan
    const { data: province } = useFetchDataById<Province>('province', Number(venue.province_id));
    const { data: district } = useFetchDataById<District>('district', Number(venue.district_id));
    const { data: owner } = useFetchDataById<User>('user', Number(venue.owner_id)); // Sửa endpoint 'district' thành 'user' cho đúng logic

    // Xử lý ảnh
    const images: Image[] = (venue as any).images ?? (venue as any).photos ?? [];
    // Chỉ lấy ảnh thuộc loại venue
    const venueImages: Image[] = images.filter((img: Image) => img.type === 'venue');

    // Chọn ảnh chính
    const primaryImage: Image = venueImages.find((img: Image) => img.is_primary === 1) ?? venueImages[0] ?? { url: 'https://placehold.co/1200x700/10B981/ffffff?text=BCP+Sports', };

    // Tạo gallery
    const gallery: Image[] =
        venueImages.length > 0
            ? venueImages
            : [primaryImage];

    // Xử lý sân con
    const courts = venue.courts ?? [];

    // Tính khoảng giá
    const priceRange = (() => {
        const prices: number[] = [];
        courts.forEach((c: any) => c.time_slots?.forEach((t: any) => { if (t.price) prices.push(Number(t.price)); }));
        if (prices.length === 0) return null;
        const min = Math.min(...prices), max = Math.max(...prices);
        return min === max ? `${formatPrice(min)}` : `${formatPrice(min)} - ${formatPrice(max)}`;
    })();

    return (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-8">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {/* =======================
            CỘT TRÁI: ẢNH (Chiếm 5/12 - Khoảng 40%)
           ======================= */}
                <div className="lg:col-span-5 flex flex-col gap-3">
                    {/* Main Image - Giữ chiều cao vừa phải (h-64) như yêu cầu */}
                    <div
                        className="h-64 sm:h-72 lg:h-[22rem] w-full rounded-xl bg-cover bg-center relative shadow-sm overflow-hidden group border border-gray-100"
                        style={{ backgroundImage: `url(${gallery[galleryIndex].url})` }}
                    >
                        <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-60 group-hover:opacity-40 transition-opacity" />
                        <div className="absolute bottom-3 right-3 bg-black/70 text-white text-xs px-3 py-1 rounded-full backdrop-blur-md font-medium">
                            📷 {galleryIndex + 1} / {gallery.length}
                        </div>
                    </div>

                    {/* Thumbnails Row */}
                    {gallery.length > 1 && (
                        <div className="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                            {gallery.map((img, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => setGalleryIndex(idx)}
                                    className={`flex-shrink-0 w-20 h-16 rounded-lg overflow-hidden border-2 transition-all ${galleryIndex === idx
                                        ? 'border-emerald-500 ring-2 ring-emerald-500/20 opacity-100'
                                        : 'border-transparent opacity-60 hover:opacity-100'
                                        }`}
                                >
                                    <img src={img.url} alt="thumb" className="w-full h-full object-cover" />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {/* =======================
            CỘT PHẢI: THÔNG TIN TỔNG HỢP (Chiếm 7/12 - Khoảng 60%)
           ======================= */}
                <div className="lg:col-span-7 flex flex-col h-full">

                    {/* 1. HEADER & RATING */}
                    <div className="border-b border-gray-100 pb-4 mb-4">
                        <div className="flex justify-between items-start">
                            <h1 className="text-2xl font-bold text-gray-800 leading-tight">
                                {venue.name}
                            </h1>
                            <span className="shrink-0 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-100">
                                Đang hoạt động
                            </span>
                        </div>

                        <div className="flex items-center gap-4 mt-2 text-sm">
                            <div className="flex items-center gap-1 text-amber-400">
                                <span className="font-bold text-gray-800 text-base">{Number(venue.reviews_avg_rating ?? 0).toFixed(1)}</span>
                                <i className="fa-solid fa-star text-sm"></i>
                            </div>
                            <span className="text-gray-400">|</span>
                            <span className="text-gray-500 underline decoration-gray-300 cursor-pointer hover:text-emerald-600">
                                {venue.reviews?.length || 0} đánh giá
                            </span>
                            <span className="text-gray-400">|</span>
                            <span className="text-gray-500">
                                Chủ sân: <strong>{venue.owner?.name ?? 'Hệ thống'}</strong>
                            </span>
                        </div>
                    </div>

                    {/* 2. THÔNG TIN CƠ BẢN (Icons Grid) */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-4 mb-5 text-sm text-gray-600">
                        <div className="flex items-start gap-3">
                            <i className="fa-solid fa-location-dot text-emerald-500 mt-1 w-4"></i>
                            <span className="line-clamp-2">
                                {venue.address_detail}, {district?.data.name}, {province?.data.name}
                            </span>
                        </div>
                        <div className="flex items-center gap-3">
                            <i className="fa-regular fa-clock text-blue-500 w-4"></i>
                            <span>
                                Mở cửa: <span className="font-semibold text-gray-800">{venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}</span>
                            </span>
                        </div>
                        <div className="flex items-center gap-3 sm:col-span-2">
                            <i className="fa-solid fa-tag text-amber-500 w-4"></i>
                            <span className="flex items-center gap-2">
                                Giá tham khảo:
                                <span className="font-bold text-amber-600 text-lg">
                                    {priceRange ?? 'Liên hệ'}
                                </span>
                                <span className="text-xs text-gray-400">/giờ</span>
                            </span>
                        </div>
                    </div>

                    {/* 3. MÔ TẢ (Merged từ Info) */}
                    <div className="bg-gray-50 rounded-lg p-3 mb-4 text-sm text-gray-600 relative group">
                        <p className={`${showFullDesc ? '' : 'line-clamp-2'} transition-all duration-300 leading-relaxed`}>
                            {venue.description ? venue.description : 'Chưa có mô tả chi tiết.'}
                        </p>
                        {venue.description && venue.description.length > 150 && (
                            <button
                                onClick={() => setShowFullDesc(!showFullDesc)}
                                className="text-xs font-bold text-emerald-600 hover:underline mt-1 focus:outline-none"
                            >
                                {showFullDesc ? 'Thu gọn' : 'Xem thêm'}
                            </button>
                        )}
                    </div>

                    {/* 4. DANH SÁCH SÂN CON COMPACT (Merged từ Info) */}
                    <div className="flex-1 min-h-[100px] flex flex-col">
                        <h4 className="text-xs font-bold text-gray-500 uppercase mb-2 flex items-center gap-2">
                            <i className="fa-solid fa-layer-group"></i> Danh sách sân con ({courts.length})
                        </h4>

                        <div className="flex-1 overflow-y-auto max-h-[160px] pr-1 custom-scrollbar space-y-2">
                            {courts.length > 0 ? courts.map((c) => {
                                const slots = c.time_slots ?? [];
                                const prices = slots.map((s: any) => Number(s.price)).filter(p => !isNaN(p) && p > 0);
                                const minPrice = prices.length ? Math.min(...prices) : 0;

                                return (
                                    <div key={c.id} className="flex justify-between items-center p-2 rounded border border-gray-100 hover:bg-emerald-50/50 hover:border-emerald-100 transition-colors cursor-default bg-white">
                                        <span className="font-medium text-sm text-gray-700">{c.name}</span>
                                        <div className="flex items-center gap-3">
                                            <span className="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-500">
                                                {slots.filter((s: any) => s.status === 'open').length > 0 ? 'Còn trống' : 'Hết chỗ'}
                                            </span>
                                            <span className="text-sm font-bold text-amber-600 w-20 text-right">
                                                {minPrice ? formatPrice(minPrice) : 'Liên hệ'}
                                            </span>
                                        </div>
                                    </div>
                                )
                            }) : (
                                <p className="text-sm text-gray-400 italic">Chưa có thông tin sân con.</p>
                            )}
                        </div>
                    </div>

                    {/* ACTION BUTTONS (Optional) */}
                    <div className="mt-4 pt-3 border-t border-gray-100 flex gap-3">
                        <button className="text-gray-500 hover:text-emerald-600 text-sm font-medium transition flex items-center gap-1">
                            <i className="fa-solid fa-share-nodes"></i> Chia sẻ
                        </button>
                        <button className="text-gray-500 hover:text-red-500 text-sm font-medium transition flex items-center gap-1">
                            <i className="fa-regular fa-heart"></i> Yêu thích
                        </button>
                    </div>

                </div>
            </div>
        </div>
    )
}

export default Gallery_Detail_Venue;