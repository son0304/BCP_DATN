import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useFetchData } from "../../Hooks/useApi";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";

interface VenuesProps {
  limit?: number;
}

const List_Venue = ({ limit }: VenuesProps) => {
  const navigate = useNavigate();
  const { data: venueData, isLoading, isError } = useFetchData<Venue[]>("venues");


  const [searchName, setSearchName] = useState("");
  const [selectedAddress, setSelectedAddress] = useState("");
  const [filteredVenues, setFilteredVenues] = useState<Venue[] | null>(null);

  if (isError)
    return (
      <p className="text-center text-red-500 py-10">
        Đã xảy ra lỗi khi tải dữ liệu sân!
      </p>
    );

  const venues: Venue[] = (venueData?.data as Venue[]) || [];
  const addressOptions = Array.from(new Set(venues.map((v) => v.address_detail)));

  const displayedVenues = filteredVenues
    ? filteredVenues
    : limit
      ? venues.slice(0, limit)
      : venues;

  const handleSearch = () => {
    let filtered = venues;
    if (searchName)
      filtered = filtered.filter((v) =>
        v.name.toLowerCase().includes(searchName.toLowerCase())
      );
    if (selectedAddress)
      filtered = filtered.filter((v) => v.address_detail === selectedAddress);
    setFilteredVenues(filtered);
  };

  return (
    <div className="w-full bg-gray-50 py-8 md:py-10">
      <h1 className="text-xl sm:text-2xl md:text-3xl font-extrabold text-[#2d6a2d] text-center mb-6">
        Danh sách sân thể thao
      </h1>

      {/* Thanh tìm kiếm */}
      <div className="max-w-7xl mx-auto px-4 mb-8">
        <div className="flex flex-wrap md:flex-nowrap items-center gap-3 md:gap-4">
          <input
            type="text"
            placeholder="Nhập tên sân..."
            value={searchName}
            onChange={(e) => setSearchName(e.target.value)}
            className="flex-grow px-3 py-2 md:px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2d6a2d] outline-none text-sm md:text-base"
          />

          <select
            value={selectedAddress}
            onChange={(e) => {
              setSelectedAddress(e.target.value);
              if (window.innerWidth < 768) handleSearch();
            }}
            className="w-36 md:w-72 px-3 py-2 border border-gray-300 rounded-lg text-sm md:text-base focus:ring-2 focus:ring-[#2d6a2d] outline-none"
          >
            <option value="">Chọn địa chỉ...</option>
            {addressOptions.map((addr, idx) => (
              <option key={idx} value={addr}>
                {addr}
              </option>
            ))}
          </select>

          <button
            onClick={handleSearch}
            className="px-4 py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition duration-300 flex items-center justify-center text-sm md:text-base"
          >
            <i className="fa-solid fa-magnifying-glass mr-1 hidden md:inline"></i>
            <span className="hidden md:inline">Tìm kiếm</span>
            <i className="fa-solid fa-filter md:hidden"></i>
          </button>
        </div>
      </div>

      {/* Danh sách sân */}
      <div className="max-w-7xl mx-auto px-4">
        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          {isLoading
            ? Array.from({ length: limit || 4 }).map((_, i) => (
              <div
                key={i}
                className="bg-white rounded-xl shadow-md animate-pulse border border-gray-200"
              >
                <div className="w-full h-28 sm:h-36 bg-gray-200"></div>
                <div className="p-3 space-y-2">
                  <div className="h-4 bg-gray-300 w-3/4 rounded"></div>
                  <div className="h-3 bg-gray-200 w-2/3 rounded"></div>
                </div>
              </div>
            ))
            : displayedVenues.length > 0
              ? displayedVenues.map((venue) => {
                const primaryImage = venue.images?.find(
                  (img: Image) => img.is_primary === 1
                );

                return (
                  <div
                    key={venue.id}
                    className="bg-white rounded-xl border border-gray-200 overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col"
                  >
                    {/* Ảnh */}
                    <div className="relative">
                      <img
                        onClick={() => navigate(`/venues/${venue.id}`)}
                        src={
                          primaryImage?.url ||
                          "https://via.placeholder.com/400x300?text=BCP+Sports"
                        }
                        alt={venue.name}
                        className="w-full h-28 sm:h-36 object-cover cursor-pointer"
                      />
                      <div className="absolute top-0 right-0 bg-[#10B981] text-white px-2 py-1 rounded-bl-md flex items-center gap-1 shadow-md text-xs">
                        <i className="fa-solid fa-star text-yellow-400"></i>
                        <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                      </div>
                      <div className="absolute bottom-0 left-0 bg-[#10B981] text-white px-2 py-1 rounded-tr-md flex items-center gap-1 shadow-md text-xs">
                        <i className="fa-regular fa-clock text-white mr-1"></i>
                        <span>
                          {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                        </span>
                      </div>
                    </div>

                    {/* Nội dung */}
                    <div className="p-3 flex-1 flex flex-col">
                      <div className="flex flex-wrap gap-1 mb-1">
                        {venue.venue_types?.length ? (
                          venue.venue_types.map((type, i) => (
                            <span
                              key={i}
                              className="text-[10px] sm:text-[11px] bg-[#D1FAE5] text-[#065F46] px-1.5 py-0.5 rounded-full font-medium line-clamp-1"
                            >
                              {type.name}
                            </span>
                          ))
                        ) : (
                          <span className="text-[11px] text-gray-500 italic">
                            Chưa có loại hình
                          </span>
                        )}
                      </div>

                      <h3 className="text-[13px] sm:text-[15px] font-semibold text-[#11182C] mb-1 line-clamp-1">
                        {venue.name}
                      </h3>

                      <div className="flex items-start text-[11px] sm:text-[13px] text-gray-600 mb-2">
                        <i className="fa-solid fa-location-dot text-[#10B981] mt-0.5 mr-1 flex-shrink-0"></i>
                        <span className="line-clamp-2">{venue.address_detail}</span>
                      </div>

                      <button
                        onClick={() => navigate(`/venues/${venue.id}`)}
                        className="mt-auto c hover:bg-[#D97706] text-white font-semibold text-[11px] sm:text-[13px] py-1.5 sm:py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
                      >
                        Xem chi tiết
                      </button>
                    </div>
                  </div>
                );
              })
              : (
                <p className="col-span-full text-center text-gray-500 italic py-10">
                  Không có sân nào được tìm thấy.
                </p>
              )}
        </div>
      </div>
    </div>
  );
};

export default List_Venue;
