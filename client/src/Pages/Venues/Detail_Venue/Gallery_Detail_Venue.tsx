import { useState } from "react";
import type { Image } from "../../../Types/image";
import type { Venue } from "../../../Types/venue";
import { useFetchDataById } from "../../../Hooks/useApi";
import type { Province } from "../../../Types/province";
import type { District } from "../../../Types/district";
import type { User } from "../../../Types/user";

const Gallery_Detail_Venue = ({ venue, formatPrice }: {venue: Venue, formatPrice: (price: number) => string;}) => {
  const [galleryIndex, setGalleryIndex] = useState<number>(0);
  const { data: province } = useFetchDataById<Province>('province', Number(venue.province_id));
  const { data: district } = useFetchDataById<District>('district', Number(venue.district_id));
  const { data: owner } = useFetchDataById<User>('district', Number(venue.owner_id));

  const images: Image[] = (venue as any).images ?? (venue as any).photos ?? [];
  const primaryImage = images.find((img: Image) => img.is_primary === 1) ?? images[0] ?? { url: 'https://placehold.co/1200x700/10B981/ffffff?text=BCP+Sports' };
  const gallery = images.length > 0 ? images : [primaryImage];
  const courts = venue.courts ?? [];

  const priceRange = (() => {
    const prices: number[] = [];
    courts.forEach((c: any) => c.time_slots?.forEach((t: any) => { if (t.price) prices.push(Number(t.price)); }));
    if (prices.length === 0) return null;
    const min = Math.min(...prices), max = Math.max(...prices);
    return min === max ? `${formatPrice(min)}` : `${formatPrice(min)} - ${formatPrice(max)}`;
  })();

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
      <div className="flex flex-col lg:flex-row gap-6">
        
        {/* === LEFT: IMAGE GALLERY === */}
        <div className="lg:w-2/3 space-y-3">
          {/* Main Image - Compact Height (Max h-80/96) */}
          <div
            className="h-56 md:h-80 lg:h-96 rounded-xl bg-cover bg-center relative shadow-inner overflow-hidden group"
            style={{ backgroundImage: `url(${gallery[galleryIndex].url})` }}
          >
            <div className="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-all" />
          </div>

          {/* Thumbnails - Smaller */}
          {gallery.length > 1 && (
            <div className="flex gap-2 overflow-x-auto pb-1 custom-scrollbar">
                {gallery.map((img, idx) => (
                <button
                    key={idx}
                    onClick={() => setGalleryIndex(idx)}
                    className={`flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition-all ${galleryIndex === idx
                    ? 'border-[#10B981] ring-1 ring-[#10B981]'
                    : 'border-transparent opacity-70 hover:opacity-100'
                    }`}
                >
                    <img src={img.url} alt="thumbnail" className="w-full h-full object-cover" />
                </button>
                ))}
            </div>
          )}
        </div>

        {/* === RIGHT: INFO (Compact) === */}
        <div className="lg:w-1/3 flex flex-col justify-center">
            <h1 className="text-xl md:text-2xl font-bold text-gray-800 leading-tight mb-2">
                {venue.name}
            </h1>
            
            {/* Rating */}
            <div className="flex items-center gap-2 text-xs mb-4">
               <div className="flex text-[#F59E0B]">
                  {[1,2,3,4,5].map(i => <i key={i} className={`fa-solid fa-star ${i <= Math.round(Number(venue.reviews_avg_rating)) ? '' : 'text-gray-300'}`}></i>)}
               </div>
               <span className="font-semibold text-gray-600">{Number(venue.reviews_avg_rating ?? 0).toFixed(1)}/5.0</span>
               <span className="text-gray-400">({venue.reviews?.length} đánh giá)</span>
            </div>

            <div className="h-[1px] bg-gray-100 w-full mb-4"></div>

            {/* Details List */}
            <div className="space-y-3 text-sm text-gray-600">
                <div className="flex items-start gap-3">
                    <i className="fa-solid fa-location-dot text-[#10B981] mt-1 w-4 text-center"></i>
                    <span className="leading-snug">
                        {venue.address_detail}, {district?.data.name}, {province?.data.name}
                    </span>
                </div>
                
                <div className="flex items-center gap-3">
                    <i className="fa-regular fa-clock text-[#10B981] w-4 text-center"></i>
                    <span>
                        <span className="font-semibold text-gray-800">{venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}</span>
                    </span>
                </div>

                <div className="flex items-center gap-3">
                    <i className="fa-solid fa-tag text-[#10B981] w-4 text-center"></i>
                    <span>
                        Giá: <span className="font-bold text-[#F59E0B] text-base">{priceRange ?? 'Liên hệ'}</span>
                    </span>
                </div>

                <div className="flex items-center gap-3">
                    <i className="fa-regular fa-user text-[#10B981] w-4 text-center"></i>
                    <span>Chủ sân: {owner?.data.name ?? 'Admin'}</span>
                </div>
            </div>

            {/* Action Buttons (Ví dụ: Chia sẻ, Yêu thích) */}
            <div className="flex gap-3 mt-6">
                <button className="flex-1 py-2 rounded-lg border border-gray-200 text-gray-500 text-xs font-semibold hover:bg-gray-50 transition">
                    <i className="fa-solid fa-share-nodes mr-1"></i> Chia sẻ
                </button>
                <button className="flex-1 py-2 rounded-lg border border-gray-200 text-red-500 text-xs font-semibold hover:bg-red-50 transition">
                    <i className="fa-regular fa-heart mr-1"></i> Yêu thích
                </button>
            </div>
        </div>
      </div>
    </div>
  )
}

export default Gallery_Detail_Venue;