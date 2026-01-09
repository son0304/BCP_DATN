import { useState, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { useFetchData } from "../../Hooks/useApi";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";
import axios from "axios";

// Mở rộng Type để nhận thêm các trường từ Backend mới
interface VenueEnhanced extends Venue {
  is_on_sale?: boolean;
  is_featured?: boolean;
  is_promoted?: boolean;
}

interface VenueType { id: number; name: string; }
interface Province { id: number; name: string; }
interface District { id: number; name: string; province_id: number; }
interface VenuesProps { limit?: number; }

const List_Venue = ({ limit }: VenuesProps) => {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();

  const [searchName, setSearchName] = useState(searchParams.get("name") || "");
  const [selectedType, setSelectedType] = useState(searchParams.get("type_id") || "");
  const [selectedProvince, setSelectedProvince] = useState(searchParams.get("province_id") || "");
  const [selectedDistrict, setSelectedDistrict] = useState(searchParams.get("district_id") || "");
  const [districts, setDistricts] = useState<District[]>([]);

  const apiParams = new URLSearchParams();
  if (searchParams.get("name")) apiParams.append("name", searchParams.get("name")!);
  if (searchParams.get("type_id")) apiParams.append("type_id", searchParams.get("type_id")!);
  if (searchParams.get("province_id")) apiParams.append("province_id", searchParams.get("province_id")!);
  if (searchParams.get("district_id")) apiParams.append("district_id", searchParams.get("district_id")!);

  const { data: venueData, isLoading, isError } = useFetchData<any>(`venues?${apiParams.toString()}`);
  const { data: typesData } = useFetchData<VenueType[]>("venueType");
  const { data: provincesData } = useFetchData<Province[]>("provinces");

  const venues: VenueEnhanced[] = venueData?.data?.data || [];
  console.log(venues);

  const venueTypes = (typesData?.data as VenueType[]) || [];
  const provinces = (provincesData?.data as Province[]) || [];

  useEffect(() => {
    if (selectedProvince) {
      const fetchDistricts = async () => {
        try {
          const response = await axios.get(`http://localhost:8000/api/districts`, {
            params: { province_id: selectedProvince }
          });
          setDistricts(response.data.data || response.data);
        } catch (error) {
          setDistricts([]);
        }
      };
      fetchDistricts();
    } else {
      setDistricts([]);
      setSelectedDistrict("");
    }
  }, [selectedProvince]);

  const handleUpdateParams = () => {
    const params: any = {};
    if (searchName) params.name = searchName;
    if (selectedType) params.type_id = selectedType;
    if (selectedProvince) params.province_id = selectedProvince;
    if (selectedDistrict) params.district_id = selectedDistrict;
    setSearchParams(params);
  };

  const finalDisplay = limit ? venues.slice(0, limit) : venues;

  if (isError) return <div className="py-20 text-center text-red-500">Đã xảy ra lỗi!</div>;

  return (
    <div className="w-full bg-slate-50 min-h-screen py-8 md:py-12 font-sans">
      {/* HEADER */}
      <div className="text-center mb-8 px-4">
        <h1 className="text-2xl md:text-3xl font-bold text-[#11182C] mb-2 tracking-tight">Danh Sách Sân Thể Thao</h1>
        <p className="text-gray-500 text-xs md:text-sm max-w-2xl mx-auto">
          Hơn {venueData?.data?.total || venues.length} sân bóng sẵn sàng phục vụ bạn.
        </p>
      </div>

      {/* FILTER BAR */}
      <div className="max-w-7xl mx-auto px-4 mb-10 sticky top-20 z-30">
        <div className="bg-white p-4 rounded-xl shadow-lg border border-gray-100">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="text" placeholder="Tìm tên sân..." value={searchName} onChange={(e) => setSearchName(e.target.value)} className="w-full pl-3 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-[#10B981] outline-none" />
            <select value={selectedType} onChange={(e) => setSelectedType(e.target.value)} className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none">
              <option value="">Tất cả môn</option>
              {venueTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
            </select>
            <select value={selectedProvince} onChange={(e) => { setSelectedProvince(e.target.value); setSelectedDistrict(""); }} className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none">
              <option value="">Tỉnh / Thành</option>
              {provinces.map(p => <option key={p.id} value={p.id}>{p.name}</option>)}
            </select>
            <select value={selectedDistrict} onChange={(e) => setSelectedDistrict(e.target.value)} disabled={!selectedProvince} className="w-full px-3 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm outline-none">
              <option value="">Quận / Huyện</option>
              {districts.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
            </select>
            <button onClick={handleUpdateParams} className="w-full py-2.5 bg-[#10B981] text-white text-sm font-bold rounded-lg shadow-md hover:bg-[#059669]">Lọc Kết Quả</button>
          </div>
        </div>
      </div>

      {/* DANH SÁCH SÂN */}
      <div className="max-w-7xl mx-auto px-4">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {isLoading ? (
            Array.from({ length: 4 }).map((_, i) => <div key={i} className="bg-white rounded-2xl h-64 animate-pulse"></div>)
          ) : finalDisplay.map((venue) => {
            const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1) || venue.images?.[0];

            return (
              <div key={venue.id} onClick={() => navigate(`/venues/${venue.id}`)} className="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
                <div className="relative overflow-hidden h-44">

                  {/* --- KHU VỰC BADGES (THÊM MỚI) --- */}
                  <div className="absolute top-2 left-2 flex flex-col gap-1.5 z-10">
                    {/* Badge Quảng cáo (Màu xanh) */}
                    {(venue.is_featured || venue.is_promoted) && (
                      <span className="bg-emerald-500 text-white text-[10px] font-black px-2 py-1 rounded shadow-sm uppercase flex items-center gap-1">
                        <i className="fa-solid fa-crown text-[8px]"></i> Nổi bật
                      </span>
                    )}
                    {/* Badge Sale (Màu vàng) */}
                    {venue.is_on_sale && (
                      <span className="bg-amber-400 text-white text-[10px] font-black px-2 py-1 rounded shadow-sm uppercase flex items-center gap-1">
                        <i className="fa-solid fa-bolt text-[8px]"></i> Giảm giá
                      </span>
                    )}
                  </div>

                  <img src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"} alt={venue.name} className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700" />
                  <div className="absolute top-3 right-3 bg-white/95 backdrop-blur px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 text-xs font-bold text-gray-800">
                    <i className="fa-solid fa-star text-amber-400"></i>
                    <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                  </div>
                </div>

                <div className="p-4 flex-1 flex flex-col">
                  <div className="flex flex-wrap gap-2 mb-2">
                    {venue.venue_types?.map((type, i) => (
                      <span key={i} className="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md">{type.name}</span>
                    ))}
                  </div>
                  <h3 className="text-base font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-[#10B981] transition-colors">{venue.name}</h3>
                  <div className="flex items-start gap-2 text-xs text-gray-500 mb-4">
                    <i className="fa-solid fa-location-dot text-emerald-500 mt-0.5 flex-shrink-0"></i>
                    <span className="line-clamp-2 leading-snug">{venue.address_detail}</span>
                  </div>
                  <div className="mt-auto pt-3 border-t border-gray-50 flex items-center justify-between">
                    <div className="flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                      <i className="fa-regular fa-clock text-[#10B981]"></i>
                      {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                    </div>
                    <button className="text-xs font-bold text-[#10B981] bg-emerald-50 px-3 py-1.5 rounded-full group-hover:bg-[#10B981] group-hover:text-white transition-all">Đặt sân</button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};

export default List_Venue;