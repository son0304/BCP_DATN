import { Link, useNavigate } from "react-router-dom";
import { useFetchData } from "../Hooks/useApi";
import type { Venue } from "../Types/venue";
import type { Image } from "../Types/image";
import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useState, useMemo } from "react";
// Import dayjs để xử lý thời gian bài viết
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import 'dayjs/locale/vi';

dayjs.extend(relativeTime);
dayjs.locale('vi');

interface VenueEnhanced extends Venue {
  is_featured?: boolean;
  is_promoted?: boolean;
  is_on_sale?: boolean;
}

const HomePage = () => {
  const navigate = useNavigate();
  const { data: bannerRes } = useFetchData<any[]>("banners");
  const { data: venueRes, isLoading: venueLoading } = useFetchData<any>("venues");
  const { data: typesRes } = useFetchData<any[]>("venueType");
  const { data: provincesRes } = useFetchData<any[]>("provinces");
  // Fetch bài viết cộng đồng
  const { data: postsRes, isLoading: postsLoading } = useFetchData<any>("posts");

  const banners = useMemo(() => bannerRes?.data || [], [bannerRes]);
  const displayedVenues = useMemo(() => (venueRes?.data?.data as VenueEnhanced[]) || [], [venueRes]);
  const venueTypes = useMemo(() => typesRes?.data || [], [typesRes]);
  const provinces = useMemo(() => provincesRes?.data || [], [provincesRes]);
  // Lấy 3 bài viết mới nhất
  const latestPosts = useMemo(() => (postsRes?.data?.data || []).slice(0, 3), [postsRes]);

  const [selectedType, setSelectedType] = useState("");
  const [selectedProvince, setSelectedProvince] = useState("");
  const [bannerIndex, setBannerIndex] = useState(0);

  // Auto-play Slider
  useEffect(() => {
    if (banners.length <= 1) return;
    const interval = setInterval(() => setBannerIndex((p) => (p + 1) % banners.length), 5000);
    return () => clearInterval(interval);
  }, [banners.length]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const params = new URLSearchParams();
    if (selectedType) params.append("type_id", selectedType);
    if (selectedProvince) params.append("province_id", selectedProvince);
    navigate(`/venues?${params.toString()}`);
  };

  return (
    <div className="bg-white min-h-screen font-sans">
      {/* ========================================================= */}
      {/* 1. HERO SLIDER - GIỮ NGUYÊN */}
      {/* ========================================================= */}
      <section className="relative h-[600px] overflow-hidden bg-[#0f172a]">
        <div className="absolute inset-0">
          <div className="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-800/80 z-10"></div>
          <img
            src="https://images.unsplash.com/photo-1522778119026-d647f0565c6a?q=80&w=2070&auto=format&fit=crop"
            alt="Background Texture"
            className="w-full h-full object-cover opacity-40 grayscale"
          />
        </div>

        <div className="relative z-20 container mx-auto px-4 h-full">
          <AnimatePresence mode="wait">
            {banners.length > 0 ? (
              <div key={bannerIndex} className="h-full flex flex-col md:flex-row items-center justify-between gap-8 md:gap-16">
                <motion.div
                  initial={{ x: -50, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  exit={{ x: -50, opacity: 0 }}
                  transition={{ duration: 0.5 }}
                  className="flex-1 text-center md:text-left pt-10 md:pt-0"
                >
                  {banners[bannerIndex].type === 'sponsored' && (
                    <span className="inline-block bg-[#10B981] text-white text-[10px] md:text-xs font-black px-3 py-1.5 rounded-sm uppercase tracking-[0.2em] mb-4 md:mb-6">
                      Đối tác chiến lược
                    </span>
                  )}
                  <h1 className="text-4xl md:text-6xl lg:text-7xl font-black text-white leading-tight uppercase italic mb-6">
                    <span className="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">
                      {banners[bannerIndex].title || "BCP Sports"}
                    </span>
                    <br />
                    <span className="text-[#10B981] text-2xl md:text-4xl not-italic font-bold tracking-normal block mt-2">
                      Đặt sân nhanh - Chơi cực đã
                    </span>
                  </h1>
                  <div className="flex flex-col md:flex-row gap-4 justify-center md:justify-start">
                    <button
                      onClick={() => {
                        const url = banners[bannerIndex].target_url;
                        if (url) url.startsWith('http') ? window.open(url, '_blank') : navigate(url);
                      }}
                      className="bg-[#10B981] text-white px-8 py-4 rounded-xl font-bold uppercase tracking-wider hover:bg-[#059669] hover:shadow-lg hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-1"
                    >
                      Đặt sân ngay
                    </button>
                    <button onClick={() => navigate('/venues')} className="border border-white/20 text-white px-8 py-4 rounded-xl font-bold uppercase tracking-wider hover:bg-white/10 transition-all">
                      Xem chi tiết
                    </button>
                  </div>
                </motion.div>

                <motion.div
                  initial={{ x: 50, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  exit={{ x: 50, opacity: 0 }}
                  transition={{ duration: 0.5, delay: 0.1 }}
                  className="flex-1 w-full max-w-lg flex justify-center md:justify-end pb-10 md:pb-0 relative"
                >
                  <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-[#10B981]/20 blur-[60px] rounded-full pointer-events-none"></div>
                  <div className="relative bg-white/5 backdrop-blur-sm border border-white/10 p-3 rounded-3xl shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    <div className="relative overflow-hidden rounded-2xl aspect-[4/3] w-full md:w-[450px]">
                      <img src={banners[bannerIndex].image} alt="Banner Hero" className="w-full h-full object-cover" />
                      <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                      <div className="absolute bottom-4 left-4 text-white">
                        <p className="text-xs font-bold opacity-80 uppercase tracking-widest">Featured Venue</p>
                        <p className="text-lg font-bold">{banners[bannerIndex].title}</p>
                      </div>
                    </div>
                  </div>
                </motion.div>
              </div>
            ) : (
              <div className="h-full w-full flex items-center justify-center text-white/20">
                <i className="fa-solid fa-circle-notch animate-spin text-4xl"></i>
              </div>
            )}
          </AnimatePresence>

          {banners.length > 1 && (
            <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex gap-3 z-30">
              {banners.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setBannerIndex(i)}
                  className={`h-2 transition-all duration-300 rounded-full ${i === bannerIndex ? "w-8 bg-[#10B981]" : "w-2 bg-white/20 hover:bg-white/40"}`}
                />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* 2. SEARCH BOX - GIỮ NGUYÊN */}
      <div className="max-w-6xl mx-auto px-4 -mt-8 relative z-30">
        <div className="bg-white p-5 rounded-2xl shadow-xl shadow-slate-200/50 flex flex-wrap md:flex-nowrap gap-4 border border-gray-100">
          <select value={selectedType} onChange={e => setSelectedType(e.target.value)} className="flex-1 p-4 bg-gray-50 rounded-xl outline-none font-medium text-sm border-none focus:ring-2 focus:ring-[#10B981]">
            <option value="">Tất cả môn thể thao</option>
            {venueTypes.map((t: any) => <option key={t.id} value={t.id}>{t.name}</option>)}
          </select>
          <select value={selectedProvince} onChange={e => setSelectedProvince(e.target.value)} className="flex-1 p-4 bg-gray-50 rounded-xl outline-none font-medium text-sm border-none focus:ring-2 focus:ring-[#10B981]">
            <option value="">Toàn quốc</option>
            {provinces.map((p: any) => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
          <button onClick={handleSearch} className="bg-[#10B9a81] text-white px-12 py-4 rounded-xl font-bold uppercase text-xs tracking-widest hover:bg-[#059669] transition-all shadow-lg hover:shadow-emerald-200">
            TÌM KIẾM
          </button>
        </div>
      </div>

      {/* 3. VENUE LISTING - GIỮ NGUYÊN */}
      <section className="py-16 max-w-7xl mx-auto px-4">
        <div className="flex items-end justify-between mb-10">
          <div>
            <h2 className="text-2xl md:text-3xl font-bold text-[#11182C]">Sân Bóng nổi bật</h2>
            <div className="h-1 w-12 bg-[#10B981] mt-2 rounded-full"></div>
          </div>
          <Link to="/venues" className="text-sm font-bold text-[#10B981] hover:underline uppercase tracking-tight">
            Xem tất cả <i className="fa-solid fa-arrow-right ml-1"></i>
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {venueLoading ? (
            Array.from({ length: 4 }).map((_, i) => <div key={i} className="h-80 bg-gray-100 rounded-2xl animate-pulse" />)
          ) : (
            displayedVenues.slice(0, 4).map((venue) => {
              const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1) || venue.images?.[0];
              const imageUrl = primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports";

              return (
                <div
                  key={venue.id}
                  onClick={() => navigate(`/venues/${venue.id}`)}
                  className="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden cursor-pointer transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1"
                >
                  <div className="relative overflow-hidden h-44 bg-gray-100">
                    <div
                      className="absolute inset-0 bg-cover bg-center blur-md scale-125 opacity-50 transition-transform duration-700 group-hover:scale-150"
                      style={{ backgroundImage: `url(${imageUrl})` }}
                    ></div>
                    <img
                      src={imageUrl}
                      alt={venue.name}
                      className="absolute inset-0 w-full h-full object-contain z-10 transition-transform duration-500 group-hover:scale-105 drop-shadow-md"
                      loading="lazy"
                    />
                    <div className="absolute top-2 left-2 flex flex-col gap-1.5 z-20">
                      {venue.is_on_sale && (
                        <div className="bg-amber-400 text-white text-[9px] font-black px-2 py-1 rounded shadow-sm uppercase flex items-center gap-1">
                          <i className="fa-solid fa-bolt text-[8px]"></i> Sale
                        </div>
                      )}
                      {(venue.is_featured || venue.is_promoted) && (
                        <div className="bg-emerald-500 text-white text-[9px] font-black px-2 py-1 rounded shadow-sm uppercase flex items-center gap-1">
                          <i className="fa-solid fa-crown text-[8px]"></i> Nổi bật
                        </div>
                      )}
                    </div>
                    <div className="absolute bottom-3 right-3 z-20 bg-white/95 backdrop-blur px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 text-[10px] font-bold text-gray-800">
                      <i className="fa-solid fa-star text-amber-400"></i>
                      <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                    </div>
                  </div>

                  <div className="p-4 flex-1 flex flex-col">
                    <div className="flex flex-wrap gap-2 mb-2">
                      {venue.venue_types?.map((type: any, i: number) => (
                        <span key={i} className="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-md">
                          {type.name}
                        </span>
                      ))}
                    </div>
                    <h3 className="text-base font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-[#10B981] transition-colors uppercase">
                      {venue.name}
                    </h3>
                    <div className="flex items-start gap-2 text-[11px] text-gray-500 mb-4 min-h-[32px]">
                      <i className="fa-solid fa-location-dot text-[#10B981] mt-0.5 flex-shrink-0"></i>
                      <span className="line-clamp-2 leading-relaxed">{venue.address_detail}</span>
                    </div>
                    <div className="mt-auto pt-3 border-t border-gray-50 flex items-center justify-between">
                      <div className="flex items-center gap-1.5 text-[10px] text-gray-500 font-bold">
                        <i className="fa-regular fa-clock text-[#10B981]"></i>
                        {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                      </div>
                      <button className="text-[10px] font-bold text-[#10B981] bg-emerald-50 px-3 py-1.5 rounded-full group-hover:bg-[#10B981] group-hover:text-white transition-all uppercase tracking-tighter">
                        Đặt sân
                      </button>
                    </div>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </section>

      {/* ========================================================= */}
      {/* 4. MỚI: BẢN TIN CỘNG ĐỒNG */}
      {/* ========================================================= */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex items-end justify-between mb-10">
            <div>
              <h2 className="text-2xl md:text-3xl font-bold text-[#11182C] uppercase italic">Bảng tin cộng đồng</h2>
              <div className="h-1 w-12 bg-[#10B981] mt-2 rounded-full"></div>
            </div>
            <Link to="/posts" className="text-sm font-bold text-[#10B981] hover:underline uppercase tracking-tight">
              Vào bảng tin <i className="fa-solid fa-users ml-1"></i>
            </Link>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {postsLoading ? (
              Array.from({ length: 3 }).map((_, i) => <div key={i} className="h-64 bg-white rounded-2xl animate-pulse shadow-sm" />)
            ) : (
              latestPosts.map((post: any) => (
                <div key={post.id} className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                  <div className="flex items-center gap-3 mb-4">
                    <img
                      src={post.author?.avt || `https://ui-avatars.com/api/?name=${post.author?.name}`}
                      className="w-10 h-10 rounded-full object-cover border border-gray-100"
                      alt="avt"
                    />
                    <div>
                      <h4 className="text-sm font-bold text-gray-800 line-clamp-1">{post.author?.name}</h4>
                      <p className="text-[10px] text-gray-400 font-bold uppercase">
                        <i className="fa-regular fa-clock mr-1"></i>
                        {dayjs(post.created_at).fromNow()}
                      </p>
                    </div>
                  </div>

                  <p className="text-sm text-gray-600 mb-4 line-clamp-3 leading-relaxed italic">
                    "{post.content}"
                  </p>

                  {post.images && post.images.length > 0 && (
                    <div className="h-40 rounded-xl overflow-hidden mb-4">
                      <img src={post.images[0].url} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="post" />
                    </div>
                  )}

                  <div className="pt-4 border-t border-gray-50 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      {post.phone_contact && (
                        <span className="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">
                          <i className="fa-solid fa-phone mr-1"></i> {post.phone_contact}
                        </span>
                      )}
                    </div>
                    <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                      <i className="fa-solid fa-location-dot mr-1 text-[#10B981]"></i>
                      {post.venue?.name || 'Toàn quốc'}
                    </span>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      </section>

      {/* 5. FOOTER - GIỮ NGUYÊN */}
      <footer className="py-12 bg-gray-900 text-white text-center">
        <p className="text-[10px] font-bold opacity-40 tracking-[0.4em]">© 2024 BCP SPORTS GLOBAL</p>
      </footer>
    </div>
  );
};

export default HomePage;