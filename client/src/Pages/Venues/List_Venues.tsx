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

  // Xử lý dữ liệu (Giữ nguyên)
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
        <i className="fa-solid fa-triangle-exclamation text-3xl mb-3"></i>
        <p className="text-sm font-medium">Đã xảy ra lỗi khi tải dữ liệu sân!</p>
      </div>
    );

  return (
    <div className="w-full bg-white min-h-screen py-8 md:py-12 font-sans">
      {/* --- HEADER SECTION --- */}
      <div className="text-center mb-8 px-4">
        {/* Mobile: text-2xl, Desktop: text-3xl */}
        <h1 className="text-2xl md:text-3xl font-bold text-[#11182C] mb-2 tracking-tight">
          Khám Phá Sân Thể Thao
        </h1>
        <p className="text-gray-500 text-xs md:text-sm max-w-2xl mx-auto">
          Tìm kiếm và đặt sân nhanh chóng với hệ thống sân bãi chất lượng cao.
        </p>
      </div>

      {/* --- SEARCH BAR (Compact & Clean) --- */}
      <div className="max-w-4xl mx-auto px-4 mb-10 sticky top-20 md:top-24 z-30">
        <div className="bg-white p-1.5 md:p-2 rounded-2xl md:rounded-full shadow-xl shadow-gray-200/50 border border-gray-100 flex flex-col md:flex-row items-center gap-2 md:gap-0">
          
          {/* Input Tên */}
          <div className="relative w-full md:w-1/2 group">
            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <i className="fa-solid fa-magnifying-glass text-gray-400 text-xs md:text-sm group-focus-within:text-[#10B981] transition-colors"></i>
            </div>
            <input
              type="text"
              placeholder="Tìm tên sân..."
              value={searchName}
              onChange={(e) => setSearchName(e.target.value)}
              className="w-full pl-10 pr-4 py-2.5 bg-transparent rounded-full outline-none text-xs md:text-sm text-gray-700 placeholder-gray-400 font-medium"
            />
          </div>

          {/* Divider Desktop */}
          <div className="h-6 w-[1px] bg-gray-200 hidden md:block mx-2"></div>

          {/* Select Địa chỉ */}
          <div className="relative w-full md:w-1/3 group">
            <div className="absolute inset-y-0 left-0 pl-4 md:pl-2 flex items-center pointer-events-none">
              <i className="fa-solid fa-map-location-dot text-gray-400 text-xs md:text-sm group-focus-within:text-[#10B981] transition-colors"></i>
            </div>
            <select
              value={selectedAddress}
              onChange={(e) => {
                setSelectedAddress(e.target.value);
                if (window.innerWidth < 768) setTimeout(handleSearch, 100); 
              }}
              className="w-full pl-10 pr-8 py-2.5 bg-transparent cursor-pointer outline-none text-xs md:text-sm text-gray-700 font-medium appearance-none truncate hover:bg-gray-50 rounded-full transition-colors"
            >
              <option value="">Tất cả khu vực</option>
              {addressOptions.map((addr, idx) => (
                <option key={idx} value={addr}>
                  {addr}
                </option>
              ))}
            </select>
            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
              <i className="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
            </div>
          </div>

          {/* Button Tìm kiếm */}
          <button
            onClick={handleSearch}
            className="w-full md:w-auto px-6 py-2.5 bg-[#10B981] hover:bg-[#059669] text-white text-xs md:text-sm font-bold rounded-xl md:rounded-full shadow-md shadow-green-200 transition-all flex items-center justify-center gap-2 active:scale-95"
          >
            Tìm kiếm
          </button>
        </div>
      </div>

      {/* --- DANH SÁCH SÂN --- */}
      <div className="max-w-7xl mx-auto px-4">
        {/* Grid Responsive: Mobile 1 cột, Tablet 2 cột, Desktop 4 cột */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 md:gap-6">
          {isLoading ? (
            // Skeleton Loader (Loading State)
            Array.from({ length: limit || 4 }).map((_, i) => (
              <div key={i} className="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                <div className="h-44 bg-gray-100 animate-pulse"></div>
                <div className="p-3 space-y-2">
                  <div className="h-4 bg-gray-100 rounded w-3/4 animate-pulse"></div>
                  <div className="h-3 bg-gray-100 rounded w-1/2 animate-pulse"></div>
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
                  className="group bg-white rounded-xl shadow-sm hover:shadow-lg border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1"
                >
                  {/* Image Container: Mobile h-40, Desktop h-44 */}
                  <div className="relative overflow-hidden h-40 md:h-44">
                    <img
                      src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                      alt={venue.name}
                      className="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                    />
                    
                    {/* Badge Rating */}
                    <div className="absolute top-2 right-2 bg-white/95 backdrop-blur-sm px-1.5 py-0.5 rounded shadow-sm flex items-center gap-1 text-[10px] font-bold text-gray-800">
                      <i className="fa-solid fa-star text-amber-400 text-[8px]"></i>
                      <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                    </div>

                    {/* Badge Time */}
                    <div className="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white px-2 py-0.5 rounded flex items-center gap-1 text-[10px] font-medium">
                      <i className="fa-regular fa-clock text-[#10B981]"></i>
                      <span>
                        {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                      </span>
                    </div>
                  </div>

                  {/* Card Content */}
                  <div className="p-3 md:p-4 flex-1 flex flex-col">
                    {/* Tags */}
                    <div className="flex flex-wrap gap-1.5 mb-2">
                      {venue.venue_types?.length ? (
                        venue.venue_types.slice(0, 2).map((type, i) => (
                          <span
                            key={i}
                            className="text-[9px] md:text-[10px] font-bold uppercase tracking-wider bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100"
                          >
                            {type.name}
                          </span>
                        ))
                      ) : (
                        <span className="text-[10px] text-gray-400 italic">Đa năng</span>
                      )}
                    </div>

                    {/* Title: Text-sm (Mobile) / Text-base (Desktop) */}
                    <h3 className="text-sm md:text-base font-bold text-gray-800 mb-1 line-clamp-1 group-hover:text-[#10B981] transition-colors">
                      {venue.name}
                    </h3>

                    {/* Address: Text-xs */}
                    <div className="flex items-start gap-1.5 text-xs text-gray-500 mb-3">
                      <i className="fa-solid fa-location-dot text-gray-400 mt-0.5 flex-shrink-0"></i>
                      <span className="line-clamp-2 leading-tight">{venue.address_detail}</span>
                    </div>

                    {/* Card Footer */}
                    <div className="mt-auto pt-3 border-t border-gray-50 flex items-center justify-between">
                       <span className="text-[10px] md:text-xs text-gray-400 font-medium group-hover:text-[#10B981] transition-colors">Xem chi tiết</span>
                       <div className="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-[#10B981] group-hover:text-white transition-all">
                          <i className="fa-solid fa-arrow-right text-[10px]"></i>
                       </div>
                    </div>
                  </div>
                </div>
              );
            })
          ) : (
            // Empty State
            <div className="col-span-full flex flex-col items-center justify-center py-12 text-center">
              <div className="bg-gray-50 p-4 rounded-full mb-3">
                <i className="fa-solid fa-magnifying-glass text-2xl text-gray-400"></i>
              </div>
              <h3 className="text-sm font-bold text-gray-700 mb-1">Không tìm thấy sân nào</h3>
              <p className="text-xs text-gray-500 mb-4">Thử thay đổi từ khóa hoặc chọn khu vực khác.</p>
              <button 
                onClick={() => {setSearchName(""); setSelectedAddress(""); handleSearch();}}
                className="text-xs font-bold text-[#10B981] hover:underline"
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