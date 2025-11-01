import { useState } from "react";
import { useFetchData } from "../../Hooks/useApi";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";
import { useNavigate } from "react-router-dom";

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

  // Tạo danh sách địa chỉ duy nhất
  const addressOptions = Array.from(new Set(venues.map((v) => v.address_detail)));

  const displayedVenues = filteredVenues
    ? filteredVenues
    : limit
      ? venues.slice(0, limit)
      : venues;

  const handleSearch = () => {
    let filtered = venues;

    if (searchName) {
      filtered = filtered.filter((v) =>
        v.name.toLowerCase().includes(searchName.toLowerCase())
      );
    }

    if (selectedAddress) {
      filtered = filtered.filter((v) => v.address_detail === selectedAddress);
    }

    setFilteredVenues(filtered);
  };

  return (
    <div className="w-full bg-gray-50 py-8">
      <h1 className="text-2xl md:text-3xl font-extrabold text-[#2d6a2d] my-6 text-center">
        Danh sách sân thể thao
      </h1>

      {/* Hàng lọc: tên sân + select địa chỉ + button */}
      <div className="max-w-3xl mx-auto flex flex-col sm:flex-row items-center gap-4 px-4 mb-8">
        <input
          type="text"
          placeholder="Nhập tên sân..."
          value={searchName}
          onChange={(e) => setSearchName(e.target.value)}
          className="flex-1 min-w-[150px] px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2d6a2d] transition duration-300"
        />

        <select
          value={selectedAddress}
          onChange={(e) => setSelectedAddress(e.target.value)}
          className="flex-1 min-w-[150px] px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#2d6a2d] transition duration-300"
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
          className="min-w-[120px] px-5 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition duration-300 flex items-center justify-center shadow-md"
        >
          <i className="fa-solid fa-magnifying-glass mr-2"></i>
          Tìm kiếm
        </button>
      </div>

      <div className="max-w-7xl mx-auto px-4 md:px-6">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {isLoading ? (
            Array.from({ length: limit || 4 }).map((_, index) => (
              <div
                key={index}
                className="bg-white rounded-2xl shadow-md animate-pulse overflow-hidden border border-gray-200 h-full flex flex-col"
              >
                <div className="w-full h-64 bg-gray-200"></div>
                <div className="p-5 space-y-3">
                  <div className="h-5 bg-gray-300 w-3/4 rounded"></div>
                  <div className="h-4 bg-gray-200 w-1/2 rounded"></div>
                  <div className="h-4 bg-gray-200 w-2/3 rounded"></div>
                </div>
              </div>
            ))
          ) : displayedVenues.length > 0 ? (
            displayedVenues.map((venue) => {
              const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);

              return (
                <div
                  key={venue.id}
                  className="relative bg-white rounded-3xl shadow-lg hover:shadow-2xl overflow-hidden border border-gray-100 transition-transform duration-500 hover:-translate-y-2 flex flex-col h-full"
                >
                  {/* Ảnh sân */}
                  <div className="relative">
                    <img
                      onClick={() => navigate(`/venues/${venue.id}`)}
                      src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                      alt={venue.name}
                      className="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110 cursor-pointer"
                    />

                    {/* Nút “Mở cửa” */}
                    <div className="absolute top-3 right-3 bg-orange-500 text-white text-sm px-3 py-1 rounded-full shadow">
                      {venue.start_time && venue.end_time ? (
                        <span className="font-medium">{venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}</span>
                      ) : (
                        <span className="font-medium">Chưa có giờ hoạt động</span>
                      )}
                    </div>

                    {/* Đánh giá */}
                    <div className="absolute bottom-0 left-0 bg-orange-500 text-white px-3 py-2 rounded-tr-2xl flex items-center gap-2">
                      <span className="bg-white text-[#2d6a2d] text-sm font-semibold px-2 py-0.5 rounded">
                        {Number(venue.reviews_avg_rating)?.toFixed(1) ?? "0.0"}
                      </span>
                      <span><i className="fa-star fa-solid text-sm text-yellow-300"></i></span>
                    </div>
                  </div>

                  {/* Nội dung */}
                  <div className="p-5 flex-1 flex flex-col">
                    <h3 className="text-xl font-semibold text-gray-900 mb-2 line-clamp-1">{venue.name}</h3>

                    {/* Hiển thị loại sân */}
                    <div className="mb-2 text-sm text-gray-700">
                      <div className="flex flex-wrap gap-2">
                        {venue.venue_types &&
                          Array.from(new Set(venue.venue_types.map((vt) => vt.name))).map((name, idx) => (
                            <span
                              key={idx}
                              className="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium"
                            >
                              {name}
                            </span>
                          ))}
                      </div>
                    </div>

                    <div className="flex items-start text-gray-600 text-sm mb-2">
                      <i className="fa-solid fa-location-dot text-[#2d6a2d] mt-1 mr-2"></i>
                      <span className="line-clamp-2">{venue.address_detail}</span>
                    </div>

                    {venue.phone && (
                      <div className="flex items-center text-gray-600 text-sm mb-2">
                        <i className="fa-solid fa-phone text-[#2d6a2d] mr-2"></i>
                        {venue.phone}
                      </div>
                    )}

                    <div className="mt-3 text-sm text-gray-500">
                      <p className="font-semibold text-gray-700 mb-1">Dịch vụ:</p>
                      <div className="flex gap-3 text-[#2d6a2d] text-lg">
                        <i className="fa-solid fa-car"></i>
                        <i className="fa-solid fa-shower"></i>
                        <i className="fa-solid fa-cup-straw-swoosh"></i>
                        <i className="fa-solid fa-wifi"></i>
                      </div>
                    </div>
                  </div>

                  {/* Footer */}
                  <div className="border-t px-4 py-3 flex items-center justify-between bg-gray-50">
                    <div className="flex items-center gap-2">
                      <img
                        src={primaryImage?.url || "/images/default-venue.jpg"}
                        alt={venue.name}
                        className="w-8 h-8 rounded-full object-cover"
                      />
                      <span className="text-sm font-medium text-gray-700">{venue.name}</span>
                    </div>
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
    </div>

  );
};

export default List_Venue;
