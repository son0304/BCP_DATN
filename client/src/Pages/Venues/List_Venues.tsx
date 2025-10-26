import { useState } from "react";
import { useFetchData } from "../../Hooks/useApi";
import Detail_Venue from "./Detail_Venue";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";
import { useNavigate } from "react-router-dom";

interface VenuesProps {
  limit?: number;
}

// Component "khung xương" cho hiệu ứng tải trang chuyên nghiệp
const VenueCardSkeleton = () => (
  <div className="bg-white rounded-2xl shadow-lg overflow-hidden animate-pulse border border-gray-200">
    <div className="w-full h-56 bg-gray-200"></div>
    <div className="p-5">
      <div className="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
      <div className="h-6 bg-gray-300 rounded w-1/2 mb-4"></div>
      <div className="h-4 bg-gray-300 rounded w-full mb-6"></div>
      <div className="h-10 bg-gray-300 rounded-lg w-full"></div>
    </div>
  </div>
);


const List_Venue = ({ limit }: VenuesProps) => {
  const nagvigate = useNavigate();
  const { data: venueData, isLoading, isError } = useFetchData<Venue[]>("venues");

  if (isError)
    return <p className="col-span-full text-center text-red-500 py-10">Đã xảy ra lỗi khi tải dữ liệu sân!</p>;

  const venues: Venue[] = (venueData?.data as Venue[]) || [];
  const displayedVenues = limit ? venues.slice(0, limit) : venues;

  return (
    <div>
      <div className="container max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-2 md:px-0">

        {isLoading ? (
          // Hiển thị skeleton loader trong khi tải
          Array.from({ length: limit || 4 }).map((_, index) => (
            <VenueCardSkeleton key={index} />
          ))
        ) : displayedVenues.length > 0 ? (
          displayedVenues.map((venue) => {
            const primaryImage = venue.images?.find(
              (img: Image) => img.is_primary === 1
            );

            return (
              <div
                key={venue.id}
                className="group relative bg-white rounded-2xl border border-gray-100 shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden hover:-translate-y-2 flex flex-col"
              >
                <div className="relative overflow-hidden">
                  <img
                    onClick={() => nagvigate(`/venues/${venue.id}`)}
                    src={
                      primaryImage?.url ||
                      "https://via.placeholder.com/400x300?text=BCP+Sports"
                    }
                    alt={venue.name}
                    className="w-full h-48 md:h-56 object-cover cursor-pointer group-hover:scale-110 transition-transform duration-500"
                  />
                  <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 flex items-center gap-1 shadow">
                    <i className="fa-solid fa-star text-yellow-500 text-sm"></i>
                    <span className="text-sm font-bold text-gray-800">
                      {Number(venue.reviews_avg_rating ?? 0).toFixed(1)}
                    </span>
                  </div>
                </div>

                <div className="p-5 flex flex-col flex-1 justify-between">
                  <div>
                    {venue.start_time && venue.end_time ? (
                      <p className="text-sm text-gray-500 mb-3">
                        🕒 <span className="font-semibold text-gray-800">Mở cửa:</span>{" "}
                        <span className="text-[#348738] font-medium">
                          {venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}
                        </span>
                      </p>
                    ) : (
                      <p className="text-sm text-gray-400 italic mb-3">Chưa có giờ hoạt động</p>
                    )}

                    <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
                      Loại sân
                    </p>
                    <div className="flex flex-wrap gap-2 mb-3">
                      {venue.venueTypes?.length ? (
                        venue.venueTypes.map((type) => (
                          <span
                            key={type.id}
                            className="px-3 py-1 bg-[#348738]/10 text-[#348738] text-xs font-bold rounded-full"
                          >
                            {type.name}
                          </span>
                        ))
                      ) : (
                        <span className="text-gray-500 text-sm">Chưa có</span>
                      )}
                    </div>

                    <h2 className="text-lg font-bold text-gray-900 mb-2">{venue.name}</h2>

                    <div className="flex items-start text-gray-500 mb-4">
                      <i className="fa-solid fa-location-dot mr-2 text-[#348738] mt-1"></i>
                      <span className="text-sm">{venue.address_detail}</span>
                    </div>
                  </div>

                  <button
                    onClick={() => nagvigate(`/venues/${venue.id}`)}
                    className="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2"
                  >
                    Xem chi tiết
                  </button>
                </div>
              </div>
            );
          })
        ) : (
          <p className="col-span-full text-center text-gray-500 italic py-10">
            Không có sân nào được tìm thấy.
          </p>
        )}
      </div>

   
    </div>
  );
};

export default List_Venue;