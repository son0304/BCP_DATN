import { useState } from "react";
import { useFetchData } from "../../Hooks/useApi";
import Detail_Venue from "./Detail_Venue";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";

interface VenuesProps {
  limit?: number;
}

// Use shared Venue type from Types instead of redefining

const List_Venue = ({ limit }: VenuesProps) => {
  const [selectedVenue, setSelectedVenue] = useState<number | null>(null);
  const { data: venueData, isLoading, isError } = useFetchData<Venue[]>("venues");

  if (isLoading)
    return <p className="text-center text-gray-500">Đang tải dữ liệu...</p>;

  if (isError)
    return <p className="text-center text-red-500">Lỗi tải dữ liệu!</p>;

  const venues: Venue[] = (venueData?.data as Venue[]) || [];
  const displayedVenues = limit ? venues.slice(0, limit) : venues;

  return (
    <div>
      <div className="container max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2 md:px-0">
        {displayedVenues.length > 0 ? (
          displayedVenues.map((venue) => {
            const primaryImage = venue.images?.find(
              (img: Image) => img.is_primary === 1
            );

            return (
              <div
                key={venue.id}
                className="group relative bg-[#F3F4F6] backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden hover:-translate-y-2 flex flex-col"
              >
                <div className="relative overflow-hidden">
                  <img
                    onClick={() => setSelectedVenue(venue.id)}
                    src={
                      primaryImage?.url ||
                      "https://via.placeholder.com/400x300?text=No+Image"
                    }
                    alt={venue.name}
                    className="w-full h-48 md:h-56 object-cover cursor-pointer group-hover:scale-110 transition-transform duration-500"
                  />
                  <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 flex items-center gap-1 shadow">
                    <i className="fa-solid fa-star text-[#F59E0B] text-sm"></i>
                    <span className="text-sm font-bold text-[#111827]">
                      {Number(venue.reviews_avg_rating ?? 0).toFixed(1)}
                    </span>
                  </div>
                </div>

                <div className="p-5 flex flex-col flex-1 justify-between">
                  <div>
                    {venue.start_time && venue.end_time ? (
                      <p className="text-sm text-[#6B7280] mb-3">
                        🕒 <span className="font-semibold text-[#111827]">Giờ mở cửa:</span>{" "}
                        <span className="text-[#10B981] font-medium">
                          {venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}
                        </span>
                      </p>
                    ) : (
                      <p className="text-gray-400 italic mb-3">Chưa cập nhật giờ hoạt động</p>
                    )}

                    <p className="text-xs font-semibold text-[#6B7280] uppercase tracking-wide mb-2">
                      Loại sân
                    </p>
                    <div className="flex flex-wrap gap-2 mb-3">
                      {venue.types?.length ? (
                        venue.types.map((type) => (
                          <span
                            key={type.id}
                            className="px-3 py-1 bg-[#10B981]/20 text-[#10B981] text-xs font-medium rounded-full"
                          >
                            {type.name}
                          </span>
                        ))
                      ) : (
                        <span className="text-[#6B7280] text-sm">Chưa có loại sân</span>
                      )}
                    </div>

                    <h2 className="text-lg font-bold text-[#111827] mb-2">{venue.name}</h2>

                    <div className="flex items-start text-[#6B7280] mb-4">
                      <i className="fa-solid fa-location-dot mr-2 text-[#1E3A8A] mt-1"></i>
                      <span className="text-sm">{venue.address_detail}</span>
                    </div>
                  </div>

                  <button
                    onClick={() => setSelectedVenue(venue.id)}
                    className="w-full bg-[#3B82F6] hover:bg-[#1E3A8A] text-white font-semibold py-2 rounded-lg transition-all"
                  >
                    Xem chi tiết
                  </button>
                </div>
              </div>

            );
          })
        ) : (
          <p className="col-span-full text-center text-gray-500 italic">
            Không có sân nào được tìm thấy.
          </p>
        )}
      </div>

      {selectedVenue && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
          onClick={() => setSelectedVenue(null)}
        >
          <div
            className="bg-white w-[90%] max-w-5xl h-[85%] rounded-2xl shadow-2xl overflow-auto relative"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              className="absolute top-4 right-4 text-gray-700 hover:text-gray-900 text-2xl font-bold"
              onClick={() => setSelectedVenue(null)}
            >
              ✕
            </button>
            <Detail_Venue id={selectedVenue} />
          </div>
        </div>
      )}
    </div>
  );
};

export default List_Venue;
