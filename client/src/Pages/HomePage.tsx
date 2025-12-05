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
      subtitle: "Đặt sân thể thao dễ dàng",
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
    }, 5000);
    return () => clearInterval(interval);
  }, []);

  const banner = banners[index];

  return (
    <div className="bg-white min-h-screen font-sans">
      {/* --- HERO SECTION --- */}
      {/* Mobile: h-400px, Desktop: h-600px (Thoáng hơn trên màn hình lớn) */}
      <section className="relative h-[400px] md:h-[600px] overflow-hidden group">
        <AnimatePresence mode="wait">
          <motion.div
            key={index}
            initial={{ opacity: 0, scale: 1.05 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 1 }}
            className="absolute inset-0 bg-cover bg-center"
            style={{ backgroundImage: `url(${banner.image})` }}
          >
            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/30"></div>
          </motion.div>
        </AnimatePresence>

        <div className="relative z-10 h-full flex flex-col justify-center items-center text-center px-4 max-w-5xl mx-auto pt-8">
          <motion.div
            key={index + "-text"}
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
          >
            {/* Title nhỏ ở trên */}
            <span className="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-white/90 text-[10px] md:text-sm font-bold tracking-widest uppercase mb-4 backdrop-blur-sm">
              {banner.title}
            </span>
            
            {/* Main Title: Mobile 3xl, Desktop 5xl hoặc 6xl nhưng nét chữ thanh thoát */}
            <h1 className="text-3xl md:text-6xl font-extrabold text-white mb-4 leading-tight drop-shadow-lg">
              {banner.subtitle}
            </h1>
            
            {/* Desc: Mobile text-sm, Desktop text-lg */}
            <p className="text-gray-200 text-sm md:text-lg max-w-2xl mx-auto leading-relaxed mb-8 font-light opacity-90">
              {banner.desc}
            </p>
            
            <Link to="/venues">
              <button className="px-6 py-3 md:px-8 md:py-3.5 bg-[#10B981] hover:bg-[#059669] text-white text-sm md:text-base font-bold rounded-full shadow-lg shadow-emerald-900/30 transition-all transform hover:-translate-y-1 hover:shadow-xl">
                Đặt sân ngay
              </button>
            </Link>
          </motion.div>
        </div>
      </section>

      {/* --- SEARCH BOX (Responsive Floating) --- */}
      <div className="relative z-20 px-4 -mt-10 md:-mt-16 mb-16">
        <div className="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-5 md:p-8 border border-gray-100">
          <div className="flex items-center gap-2 mb-5">
             <div className="p-2 bg-green-50 rounded-lg text-[#10B981]">
                <i className="fa-solid fa-filter text-sm md:text-base"></i>
             </div>
             <h2 className="text-sm md:text-lg font-bold text-gray-800 uppercase tracking-wide">Tìm kiếm nhanh</h2>
          </div>
          
          <form className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {[
              { icon: "fa-futbol", placeholder: "Môn thể thao...", type: "text" },
              { icon: "fa-map-location-dot", placeholder: "Khu vực...", type: "text" },
              { icon: "fa-calendar-days", placeholder: "Chọn ngày", type: "date" },
            ].map((field, i) => (
              <div key={i} className="relative group">
                 <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <i className={`fa-solid ${field.icon} text-sm text-gray-400 group-focus-within:text-[#10B981] transition-colors`}></i>
                 </div>
                 {/* Input: Mobile text-sm, Desktop text-base để dễ đọc hơn */}
                 <input 
                    type={field.type}
                    placeholder={field.placeholder}
                    className="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#10B981]/20 focus:border-[#10B981] outline-none transition-all text-sm md:text-base text-gray-700 font-medium placeholder:text-gray-400"
                 />
              </div>
            ))}

            <button className="h-full w-full bg-[#10B981] hover:bg-[#059669] text-white text-sm md:text-base font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 uppercase tracking-wide transform active:scale-95">
              <i className="fa-solid fa-magnifying-glass"></i> Tìm kiếm
            </button>
          </form>
        </div>
      </div>

      {/* --- FEATURED VENUES --- */}
      <section className="py-10 pb-20 max-w-7xl mx-auto px-4">
        <div className="flex items-end justify-between mb-8 border-b border-gray-100 pb-4">
          <div>
            <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Sân Nổi Bật</h2>
            <p className="text-sm md:text-base text-gray-500">Địa điểm được cộng đồng yêu thích nhất tuần qua</p>
          </div>
          <Link to="/venues" className="text-sm md:text-base font-semibold text-[#10B981] hover:text-[#059669] transition-colors flex items-center gap-2 group">
            Xem tất cả <i className="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
          </Link>
        </div>

        {isError ? (
          <div className="text-center py-12 bg-red-50 rounded-2xl text-red-500 border border-red-100 text-sm md:text-base">
            <i className="fa-solid fa-circle-exclamation text-2xl mb-2 block"></i>
            Không thể tải dữ liệu sân.
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            {isLoading
              ? Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="bg-white rounded-2xl overflow-hidden border border-gray-100">
                    <div className="h-44 md:h-52 bg-gray-100 animate-pulse"></div>
                    <div className="p-4 space-y-3">
                      <div className="h-5 bg-gray-100 rounded w-3/4 animate-pulse"></div>
                      <div className="h-4 bg-gray-100 rounded w-1/2 animate-pulse"></div>
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
                      className="group bg-white rounded-2xl hover:shadow-xl border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1"
                    >
                      {/* Image - Mobile: h-44, Desktop: h-52 (Rộng rãi hơn) */}
                      <div className="relative overflow-hidden h-44 md:h-52">
                        <img
                          src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                          alt={venue.name}
                          className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                        />
                        
                        {/* Rating Badge */}
                        <div className="absolute top-3 right-3 bg-white/95 backdrop-blur px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 text-xs font-bold text-gray-800">
                          <i className="fa-solid fa-star text-amber-400"></i>
                          <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                        </div>
                      </div>

                      {/* Content */}
                      <div className="p-4 flex-1 flex flex-col">
                        {/* Tags */}
                        <div className="flex flex-wrap gap-2 mb-3">
                            {venue.venue_types?.length ? (
                              venue.venue_types.slice(0, 2).map((type, i) => (
                                <span key={i} className="text-[10px] md:text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md">
                                  {type.name}
                                </span>
                              ))
                            ) : (
                              <span className="text-[10px] text-gray-400 italic">Đa năng</span>
                            )}
                        </div>

                        {/* Title - Mobile text-sm, Desktop text-lg */}
                        <h3 className="text-base md:text-lg font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-[#10B981] transition-colors">
                          {venue.name}
                        </h3>

                        {/* Address - Mobile text-xs, Desktop text-sm */}
                        <div className="flex items-start gap-2 text-xs md:text-sm text-gray-500 mb-4">
                          <i className="fa-solid fa-location-dot text-emerald-500 mt-0.5 flex-shrink-0"></i>
                          <span className="line-clamp-2 leading-snug">{venue.address_detail}</span>
                        </div>

                        {/* Footer Card */}
                        <div className="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
                           <div className="flex items-center gap-1.5 text-xs md:text-sm text-gray-500 font-medium">
                              <i className="fa-regular fa-clock text-[#10B981]"></i>
                              {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                           </div>
                           <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-[#10B981] group-hover:text-white transition-all">
                              <i className="fa-solid fa-arrow-right text-xs"></i>
                           </div>
                        </div>
                      </div>
                    </div>
                  );
                })
              : (
                <div className="col-span-full text-center text-gray-400 py-12">
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