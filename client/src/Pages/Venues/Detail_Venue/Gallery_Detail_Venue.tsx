import { useState } from "react";
import type { Image } from "../../../Types/image";
import type { Venue } from "../../../Types/venue";
import { useFetchDataById } from "../../../Hooks/useApi";
import type { Province } from "../../../Types/province";
import type { District } from "../../../Types/district";
import type { User } from "../../../Types/user";

const Gallery_Detail_Venue = ({
  venue,
  formatPrice,
}: {
  venue: Venue;
  formatPrice: (price: number) => string;
}) => {
  const [galleryIndex, setGalleryIndex] = useState<number>(0);

  const { data: provinceResp } = useFetchDataById<Province>(
    "province",
    Number(venue.province_id)
  );
  
  const { data: districtResp } = useFetchDataById<District>(
    "district",
    Number(venue.district_id)
  );

  const { data: ownerResp } = useFetchDataById<User>(
    "user",
    venue.owner_id
  );

  const ownerName =
    venue.owner?.name ?? ownerResp?.data?.name ?? "—";
  const provinceName = provinceResp?.data?.name ?? "—";
  const districtName = districtResp?.data?.name ?? "—";

  const images: Image[] = (venue as any).images ?? (venue as any).photos ?? [];
  const primaryImage =
    images.find((img: Image) => img.is_primary === 1) ??
    images[0] ??
    { url: "https://placehold.co/1200x700/10B981/ffffff?text=BCP+Sports" };
  const gallery = images.length > 0 ? images : [primaryImage];

  const courts = venue.courts ?? [];

  const priceRange = (() => {
    const prices: number[] = [];
    courts.forEach((c: any) =>
      c.time_slots?.forEach((t: any) => {
        if (t.price) prices.push(Number(t.price));
      })
    );
    if (prices.length === 0) return null;
    const min = Math.min(...prices),
      max = Math.max(...prices);
    return min === max
      ? `${formatPrice(min)}`
      : `${formatPrice(min)} - ${formatPrice(max)}`;
  })();

  return (
    <div className="lg:flex p-2 md:p-8 lg:p-10 bg-[#F9FAFB] rounded-2xl shadow-lg gap-6">
      <div className="lg:w-3/5 space-y-4">
        {/* Ảnh chính */}
        <div
          className="h-72 lg:h-[460px] rounded-2xl bg-cover bg-center relative shadow-md overflow-hidden transition-all duration-500"
          style={{ backgroundImage: `url(${gallery[galleryIndex].url})` }}
        >
          <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
          <div className="absolute bottom-6 left-6 right-6 text-white">
            <h1 className="text-2xl md:text-3xl font-extrabold leading-tight">
              {venue.name}
            </h1>
            <div className="mt-2 flex flex-wrap gap-4 text-sm opacity-95">
              <div className="flex items-center gap-2">
                <i className="fa-solid fa-star text-[#F59E0B]" />
                <span className="font-semibold">
                  {Number(venue.reviews_avg_rating ?? 0).toFixed(1)}/5.0
                </span>
                <span className="text-gray-300">
                  ({venue.reviews?.length} đánh giá)
                </span>
              </div>
              <div className="flex items-center gap-2">
                <i className="fa-solid fa-location-dot text-[#F59E0B]" />
                <span className="text-gray-200">
                  {venue.address_detail} - {districtName} - {provinceName}
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Gallery thumbnails */}
        <div className="p-2 rounded-xl bg-white shadow-sm border border-gray-200">
          <div className="flex gap-3 overflow-x-auto pb-1">
            {gallery.map((img, idx) => (
              <button
                key={idx}
                onClick={() => setGalleryIndex(idx)}
                className={`flex-shrink-0 w-28 h-16 rounded-lg overflow-hidden border transition-all duration-300 ${
                  galleryIndex === idx
                    ? "ring-2 ring-offset-1 ring-[#10B981] border-[#10B981]"
                    : "border-gray-200"
                }`}
              >
                <img
                  src={img.url}
                  alt={`${venue.name}-img-${idx}`}
                  className="w-full h-full object-cover"
                />
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Thông tin cơ bản */}
      <div className="lg:w-2/5 p-6 bg-white rounded-2xl shadow-md border border-gray-200 flex flex-col gap-6">
        <h4 className="text-lg font-bold text-[#11182C]">Thông tin cơ bản</h4>
        <div className="space-y-4 text-base text-[#4B5563]">
          <div>
            <i className="fa-solid fa-user w-5 text-center"></i>
            <span>
              Chủ sân:{" "}
              <span className="font-bold text-[#F59E0B]">{ownerName}</span>
            </span>
          </div>
          <div className="flex items-center gap-2">
            <i className="fa-solid fa-clock w-5 text-center text-[#10B981]" />
            <span>
              Giờ mở cửa:{" "}
              <span className="font-semibold text-[#11182C]">
                {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
              </span>
            </span>
          </div>
          <div className="flex items-center gap-2">
            <i className="fa-solid fa-money-bill-wave w-5 text-center text-[#10B981]" />
            <span>
              Giá thuê:{" "}
              <span className="font-bold text-[#F59E0B]">
                {priceRange ?? "Liên hệ"}
              </span>
            </span>
          </div>
          <div className="pt-2">
            <p className="font-semibold text-[#11182C] mb-1 text-base">
              Vị trí trên Bản đồ
            </p>
            <div className="bg-gray-200 h-40 rounded-lg flex items-center justify-center text-sm text-[#6B7280] italic">
              [Vị trí Bản đồ Google Map]
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Gallery_Detail_Venue;
