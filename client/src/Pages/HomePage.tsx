import { Link, useNavigate } from "react-router-dom";
import { useFetchData } from "../Hooks/useApi";
import type { Venue } from "../Types/venue";
import type { Image } from "../Types/image";
import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useState } from "react";

const Content = () => {
  const navigate = useNavigate();
  const { data: venueData, isLoading, isError } = useFetchData<Venue[]>("venues");

  const venues: Venue[] = (venueData?.data as Venue[]) || [];
  const displayedVenues = venues.slice(0, 4);

  const banners = [
    {
      image: "https://images.unsplash.com/photo-1626248921347-74a8166f4536?q=80&w=2070&auto=format&fit=crop",
      title: "Bùng Nổ Đam Mê",
      subtitle: "Đặt sân thể thao dễ dàng - Mọi lúc, Mọi nơi",
      desc: "Kết nối đam mê với hàng trăm sân bóng, cầu lông, pickleball chất lượng cao.",
    },
    {
      image: "https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1936&auto=format&fit=crop",
      title: "Sân Cỏ Đẳng Cấp",
      subtitle: "Trải nghiệm thi đấu đỉnh cao",
      desc: "Hệ thống sân cỏ nhân tạo tiêu chuẩn, dịch vụ tiện ích đầy đủ cho trận đấu của bạn.",
    },
    {
      image: "https://images.unsplash.com/photo-1554068865-24cecd4e34b8?q=80&w=2070&auto=format&fit=crop",
      title: "Kết Nối Đồng Đội",
      subtitle: "Thể thao là không khoảng cách",
      desc: "Tìm kiếm đối thủ, đặt sân nhanh chóng và xây dựng cộng đồng thể thao vững mạnh.",
    },
  ];

  const [index, setIndex] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setIndex((prev) => (prev + 1) % banners.length);
    }, 5000); // Tăng thời gian lên 5s để người dùng kịp đọc
    return () => clearInterval(interval);
  }, []);

  const banner = banners[index];

  return (
    <div className="bg-gray-50 min-h-screen font-sans">
      {/* --- HERO SECTION --- */}
      <section className="relative h-[500px] md:h-[600px] overflow-hidden">
        <AnimatePresence mode="wait">
          <motion.div
            key={index}
            initial={{ opacity: 0, scale: 1.1 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1.2 }}
            className="absolute inset-0 bg-cover bg-center"
            style={{ backgroundImage: `url(${banner.image})` }}
          >
            {/* Overlay Gradient giúp text nổi bật hơn */}
            <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/30"></div>
          </motion.div>
        </AnimatePresence>

        <div className="relative z-10 h-full flex flex-col justify-center items-center text-center px-4 max-w-5xl mx-auto pt-10">
          <motion.div
            key={index + "-text"}
            initial={{ opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.3 }}
          >
            <span className="inline-block py-1 px-3 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-sm md:text-base font-semibold tracking-wider mb-4 backdrop-blur-md">
              {banner.title}
            </span>
            <h1 className="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight drop-shadow-2xl">
              {banner.subtitle}
            </h1>
            <p className="text-gray-200 text-base md:text-lg max-w-2xl mx-auto leading-relaxed mb-8">
              {banner.desc}
            </p>
            
            <Link to="/venues">
              <button className="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-full shadow-lg shadow-emerald-600/30 transition-all transform hover:-translate-y-1">
                Đặt sân ngay
              </button>
            </Link>
          </motion.div>
        </div>
      </section>

      {/* --- SEARCH BOX (FLOATING) --- */}
      <div className="relative z-20 px-4 -mt-16 md:-mt-24 mb-16">
        <div className="max-w-5xl mx-auto bg-white rounded-3xl shadow-2xl p-6 md:p-8 border border-gray-100">
          <h2 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i className="fa-solid fa-filter text-emerald-600"></i>
            Tìm kiếm nhanh
          </h2>
          
          <form className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {[
              { icon: "fa-futbol", label: "Môn thể thao", placeholder: "Bóng đá, Cầu lông..." },
              { icon: "fa-map-location-dot", label: "Khu vực", placeholder: "Quận, Huyện..." },
              { icon: "fa-calendar-days", label: "Ngày", placeholder: "Chọn ngày", type: "date" },
            ].map((field, i) => (
              <div key={i} className="group relative">
                 <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i className={`fa-solid ${field.icon} text-gray-400 group-focus-within:text-emerald-600 transition-colors`}></i>
                 </div>
                 <input 
                    type={field.type || "text"}
                    placeholder={field.placeholder}
                    className="w-full pl-10 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition-all text-gray-700 text-sm font-medium"
                 />
              </div>
            ))}

            <button className="h-full w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
              <i className="fa-solid fa-magnifying-glass"></i>
              Tìm kiếm
            </button>
          </form>
        </div>
      </div>

      {/* --- FEATURED VENUES --- */}
      <section className="py-10 pb-20 max-w-7xl mx-auto px-4">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
          <div>
            <h2 className="text-3xl font-extrabold text-gray-800 mb-2">Sân Nổi Bật</h2>
            <p className="text-gray-500">Những địa điểm được cộng đồng yêu thích nhất tuần qua</p>
          </div>
          <Link to="/venues" className="group flex items-center gap-2 text-emerald-600 font-bold hover:text-emerald-700 transition-colors">
            Xem tất cả
            <i className="fa-solid fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
          </Link>
        </div>

        {isError ? (
          <div className="text-center py-12 bg-red-50 rounded-2xl text-red-600 border border-red-100">
            <i className="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
            <p>Không thể tải dữ liệu sân. Vui lòng thử lại sau.</p>
          </div>
        ) : (
          // Grid layout cho Desktop, Horizontal Scroll cho Mobile
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            {isLoading
              ? Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                    <div className="h-48 bg-gray-200 animate-pulse"></div>
                    <div className="p-4 space-y-3">
                      <div className="h-5 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                      <div className="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                    </div>
                  </div>
                ))
              : displayedVenues.length > 0
              ? displayedVenues.map((venue) => {
                  const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);
                  
                  return (
                    <div
                      key={venue.id}
                      onClick={() => navigate(`/venues/${venue.id}`)}
                      className="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full"
                    >
                      {/* Image Container */}
                      <div className="relative overflow-hidden h-52">
                        <img
                          src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                          alt={venue.name}
                          className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                        />
                        
                        {/* Badges */}
                        <div className="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 text-xs font-bold text-gray-800">
                          <i className="fa-solid fa-star text-amber-400"></i>
                          <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                        </div>
                        
                        <div className="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm text-white px-2.5 py-1 rounded-md flex items-center gap-1.5 text-xs font-medium">
                          <i className="fa-regular fa-clock text-emerald-400"></i>
                          <span>{venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}</span>
                        </div>
                      </div>

                      {/* Content */}
                      <div className="p-4 flex-1 flex flex-col">
                        {/* Type Tags */}
                        <div className="flex flex-wrap gap-2 mb-2">
                            {venue.venue_types?.length ? (
                              venue.venue_types.slice(0, 2).map((type, i) => (
                                <span key={i} className="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md">
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
                          <i className="fa-solid fa-location-dot text-emerald-500 mt-1 flex-shrink-0"></i>
                          <span className="line-clamp-2 leading-snug">{venue.address_detail}</span>
                        </div>

                        {/* Footer Action */}
                        <div className="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                           <span className="text-xs text-gray-400 font-medium group-hover:text-emerald-600 transition-colors">Xem chi tiết</span>
                           <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                              <i className="fa-solid fa-arrow-right text-sm"></i>
                           </div>
                        </div>
                      </div>
                    </div>
                  );
                })
              : (
                <div className="col-span-full text-center text-gray-500 italic py-10">
                  Chưa có sân nào được đề xuất.
                </div>
              )}
          </div>
        )}
      </section>
    </div>
  );
};

export default Content;