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

  // Xử lý dữ liệu
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

  if (isError)
    return (
      <div className="flex flex-col items-center justify-center py-20 text-red-500">
        <i className="fa-solid fa-triangle-exclamation text-4xl mb-3"></i>
        <p className="text-lg font-medium">Đã xảy ra lỗi khi tải dữ liệu sân!</p>
      </div>
    );

  return (
    <div className="w-full bg-gray-50 min-h-screen py-10 font-sans">
      {/* Header Section */}
      <div className="text-center mb-10 px-4">
        <h1 className="text-3xl md:text-4xl font-extrabold text-emerald-800 mb-3 tracking-tight">
          Khám Phá Sân Thể Thao
        </h1>
        <p className="text-gray-500 text-sm md:text-base max-w-2xl mx-auto">
          Tìm kiếm và đặt sân nhanh chóng, tiện lợi với hệ thống sân bãi chất lượng cao.
        </p>
      </div>

      {/* Thanh tìm kiếm - Floating Style */}
      <div className="max-w-5xl mx-auto px-4 mb-12 sticky top-4 z-30">
        <div className="bg-white p-2 rounded-full shadow-lg border border-gray-100 flex flex-col md:flex-row items-center gap-2">
          {/* Input Tên */}
          <div className="relative w-full md:w-1/2 group">
            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <i className="fa-solid fa-search text-gray-400 group-focus-within:text-emerald-600 transition-colors"></i>
            </div>
            <input
              type="text"
              placeholder="Tìm tên sân bóng, cầu lông..."
              value={searchName}
              onChange={(e) => setSearchName(e.target.value)}
              className="w-full pl-10 pr-4 py-3 rounded-full outline-none text-gray-700 placeholder-gray-400 focus:bg-gray-50 transition-all"
            />
          </div>

          <div className="h-8 w-[1px] bg-gray-200 hidden md:block"></div>

          {/* Select Địa chỉ */}
          <div className="relative w-full md:w-1/3 group">
            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <i className="fa-solid fa-map-marker-alt text-gray-400 group-focus-within:text-emerald-600 transition-colors"></i>
            </div>
            <select
              value={selectedAddress}
              onChange={(e) => {
                setSelectedAddress(e.target.value);
                // Tự động search trên mobile để trải nghiệm tốt hơn
                if (window.innerWidth < 768) setTimeout(handleSearch, 100); 
              }}
              className="w-full pl-10 pr-8 py-3 rounded-full outline-none text-gray-700 bg-transparent cursor-pointer focus:bg-gray-50 transition-all appearance-none truncate"
            >
              <option value="">Tất cả khu vực</option>
              {addressOptions.map((addr, idx) => (
                <option key={idx} value={addr}>
                  {addr}
                </option>
              ))}
            </select>
            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
              <i className="fa-solid fa-chevron-down text-xs text-gray-400"></i>
            </div>
          </div>

          {/* Button Tìm kiếm */}
          <button
            onClick={handleSearch}
            className="w-full md:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap"
          >
            <span>Tìm kiếm</span>
          </button>
        </div>
      </div>

      {/* Danh sách sân */}
      <div className="max-w-7xl mx-auto px-4">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
          {isLoading ? (
            // Loading Skeletons
            Array.from({ length: limit || 4 }).map((_, i) => (
              <div key={i} className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <div className="h-48 bg-gray-200 animate-pulse"></div>
                <div className="p-4 space-y-3">
                  <div className="h-5 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                  <div className="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                  <div className="flex justify-between pt-2">
                     <div className="h-8 bg-gray-200 rounded w-20 animate-pulse"></div>
                  </div>
                </div>
              </div>
            ))
          ) : displayedVenues.length > 0 ? (
            displayedVenues.map((venue) => {
              const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);

              return (
                <div
                  key={venue.id}
                  onClick={() => navigate(`/venues/${venue.id}`)}
                  className="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full"
                >
                  {/* Hình ảnh với Overlay */}
                  <div className="relative overflow-hidden h-48">
                    <img
                      src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                      alt={venue.name}
                      className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    />
                    
                    {/* Badge Rating */}
                    <div className="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 text-xs font-bold text-gray-800">
                      <i className="fa-solid fa-star text-amber-400"></i>
                      <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                    </div>

                    {/* Badge Time */}
                    <div className="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm text-white px-2.5 py-1 rounded-md flex items-center gap-1.5 text-xs font-medium">
                      <i className="fa-regular fa-clock text-emerald-400"></i>
                      <span>
                        {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                      </span>
                    </div>
                  </div>

                  {/* Nội dung */}
                  <div className="p-4 flex-1 flex flex-col">
                    {/* Tags */}
                    <div className="flex flex-wrap gap-2 mb-2">
                      {venue.venue_types?.length ? (
                        venue.venue_types.slice(0, 2).map((type, i) => (
                          <span
                            key={i}
                            className="text-[10px] font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md"
                          >
                            {type.name}
                          </span>
                        ))
                      ) : (
                        <span className="text-[10px] text-gray-400 italic">Đa năng</span>
                      )}
                    </div>

                    <h3 className="text-lg font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-emerald-700 transition-colors">
                      {venue.name}
                    </h3>

                    <div className="flex items-start gap-2 text-sm text-gray-500 mb-4">
                      <i className="fa-solid fa-location-dot text-emerald-500 mt-1"></i>
                      <span className="line-clamp-2 leading-snug">{venue.address_detail}</span>
                    </div>

                    {/* Footer Card */}
                    <div className="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                       <span className="text-xs text-gray-400 font-medium">Đặt ngay</span>
                       <button className="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                          <i className="fa-solid fa-arrow-right text-sm"></i>
                       </button>
                    </div>
                  </div>
                </div>
              );
            })
          ) : (
            // Empty State
            <div className="col-span-full flex flex-col items-center justify-center py-16 text-center">
              <div className="bg-gray-100 p-6 rounded-full mb-4">
                <i className="fa-solid fa-magnifying-glass text-4xl text-gray-400"></i>
              </div>
              <h3 className="text-xl font-semibold text-gray-700 mb-1">Không tìm thấy kết quả</h3>
              <p className="text-gray-500">Thử thay đổi từ khóa hoặc bộ lọc địa chỉ của bạn.</p>
              <button 
                onClick={() => {setSearchName(""); setSelectedAddress(""); handleSearch();}}
                className="mt-4 text-emerald-600 font-medium hover:underline"
              >
                Xóa bộ lọc
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default List_Venue;