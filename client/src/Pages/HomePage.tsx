import { Link, useNavigate } from "react-router-dom";
import { useFetchData } from "../Hooks/useApi";
import type { Venue } from "../Types/venue";
import type { Image } from "../Types/image";
import { AnimatePresence, motion } from "framer-motion";
import { useEffect, useState } from "react";

const Content = () => {
  const navigate = useNavigate();
  const { data: venueData, isLoading, isError } =
    useFetchData<Venue[]>("venues");

  const venues: Venue[] = (venueData?.data as Venue[]) || [];
  const displayedVenues = venues.slice(0, 4);

  const banners = [
    {
      image:
        "https://cdn.prod.website-files.com/6390c2d9fbb8357ffc404b63/6612f97cff3fd2e80bcb5b1c_What%20is%20Pickleball.png",
      title: (
        <>
          Đặt sân thể thao dễ dàng <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-green-200 to-teal-100">
            Mọi lúc – Mọi nơi
          </span>
        </>
      ),
      desc: "Khám phá hàng trăm sân bóng, cầu lông, pickleball… Đặt lịch nhanh chóng và nhận ưu đãi cực hấp dẫn chỉ với vài cú nhấp.",
    },
    {
      image:
        "https://images.unsplash.com/photo-1551958219-acbc608c6377?q=80&w=1470&auto=format&fit=crop",
      title: (
        <>
          Sân bóng chất lượng <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-green-200 to-teal-100">
            Gần bạn nhất
          </span>
        </>
      ),
      desc: "Đặt sân bóng đá nhanh chóng, dễ dàng, với hệ thống tiện ích và ưu đãi hấp dẫn mỗi ngày.",
    },
    {
      image:
        "https://lh7-rt.googleusercontent.com/docsz/AD_4nXdilipWIRDONHYvGLHnlQgJ8AlNWegmZQL6JyUH-aZOnk5YrXILOeHEFwgYEOhegCxtPhk_ZOVMKrqwy4IS2v3OpM91ZSD8Z7QlGi5rNvFMbw-XY1I78SydXAGlVkp2uNtKw5bA?key=arrkdHtwmhcmPHr4YSqemok2",
      title: (
        <>
          Trải nghiệm thể thao <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-green-200 to-teal-100">
            Cùng bạn bè
          </span>
        </>
      ),
      desc: "Đặt sân cầu lông, pickleball và nhiều môn khác — chỉ trong vài giây.",
    },
  ];

  const [index, setIndex] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setIndex((prev) => (prev + 1) % banners.length);
    }, 3000);
    return () => clearInterval(interval);
  }, []);

  const banner = banners[index];

  return (
    <>
      {/* Banner đầu trang */}
      <section className="relative h-[300px] md:h-[480px] flex items-center justify-center mt-2 pb-32 md:pb-40">
        <AnimatePresence mode="wait">
          <motion.div
            key={index}
            initial={{ opacity: 0, scale: 1.1 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.95 }}
            transition={{ duration: 1 }}
            className="absolute inset-0 bg-cover bg-center"
            style={{ backgroundImage: `url(${banner.image})` }}
          />
        </AnimatePresence>

        <div className="absolute inset-0 bg-gradient-to-br from-emerald-900/70 via-emerald-800/50 to-teal-600/40"></div>

        <motion.div
          key={index + "-content"}
          initial={{ opacity: 0, y: 30 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          className="relative z-10 flex flex-col items-center text-center w-full max-w-4xl px-6"
        >
          <h1 className="text-3xl md:text-5xl font-extrabold leading-tight mb-4 md:mb-6 text-white drop-shadow-lg">
            {banner.title}
          </h1>
          <p className="text-base md:text-lg text-emerald-100 mb-6 md:mb-8 leading-relaxed max-w-2xl">
            {banner.desc}
          </p>
        </motion.div>
      </section>

      {/* 🔍 Form tìm kiếm */}
      <section className="container mx-auto max-w-5xl bg-white md:h-64 h-full md:-mt-20 md:relative md:z-10 rounded-2xl shadow-2xl border border-gray-200 p-4 md:p-6">
        <div className="w-full text-center mb-4">
          <h1 className="text-xl md:text-3xl font-bold text-gray-900 mb-2">
            Tìm sân nhanh
          </h1>
          <p className="text-sm md:text-base text-gray-500">
            Tìm kiếm sân thể thao phù hợp với bạn
          </p>
        </div>

        <form className="grid md:grid-cols-4 grid-cols-1 gap-3 md:gap-4">
          {[
            { icon: "fa-futbol", label: "Chọn môn thể thao" },
            { icon: "fa-map-marker-alt", label: "Chọn khu vực" },
            { icon: "fa-calendar", label: "Chọn ngày" },
          ].map((f, i) => (
            <div
              key={i}
              className="relative flex items-center border-2 border-gray-200 hover:border-emerald-500 p-2 md:p-3 rounded-2xl transition-all duration-300"
            >
              <i
                className={`fa-solid ${f.icon} text-emerald-500 text-base md:text-lg mr-3`}
              ></i>
              <select className="w-full border-none bg-transparent outline-none text-gray-700 text-sm md:text-base font-medium">
                <option value="">{f.label}</option>
              </select>
            </div>
          ))}

          <button className="p-2 md:p-3 bg-emerald-500 hover:bg-emerald-600 text-white text-sm md:text-base font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
            <i className="fa-solid fa-search"></i>
            <span>Tìm kiếm</span>
          </button>
        </form>
      </section>

      {/* 🏟️ Gợi ý sân */}
      <section className="py-10 md:py-16 mt-10 md:mt-16 bg-gradient-to-b from-white to-gray-50">
        <div className="container max-w-7xl mx-auto px-4">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h2 className="text-xl md:text-2xl font-bold text-emerald-600">
                Gợi ý cho bạn
              </h2>
              <p className="text-sm md:text-base text-gray-500">
                Những sân thể thao được yêu thích nhất
              </p>
            </div>
            <Link to="/venues">
              <button className="flex items-center gap-2 text-emerald-500 hover:text-emerald-600 text-sm md:text-base transition font-semibold">
                <span>Xem thêm</span>
                <i className="fa-solid fa-arrow-right"></i>
              </button>
            </Link>
          </div>

          {isError ? (
            <p className="text-center text-red-500 py-10">
              Đã xảy ra lỗi khi tải dữ liệu sân!
            </p>
          ) : (
            <div className="flex gap-4 overflow-x-auto sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-x-hidden scrollbar-hide">
              {isLoading
                ? Array.from({ length: 4 }).map((_, i) => (
                    <div
                      key={i}
                      className="bg-white rounded-2xl shadow-md animate-pulse border border-gray-200 min-w-[220px] sm:min-w-0"
                    >
                      <div className="w-full h-40 bg-gray-200"></div>
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
                        className="bg-white rounded-2xl border border-gray-200 overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-lg flex flex-col min-w-[220px] sm:min-w-0"
                      >
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
                          <div className="absolute top-0 right-0 bg-emerald-500 text-white px-2 py-1 rounded-bl-md flex items-center gap-1 shadow-md text-xs">
                            <i className="fa-solid fa-star text-yellow-400"></i>
                            <span>
                              {Number(venue.reviews_avg_rating)?.toFixed(1) ||
                                "0.0"}
                            </span>
                          </div>
                          <div className="absolute bottom-0 left-0 bg-emerald-500 text-white px-2 py-1 rounded-tr-md flex items-center gap-1 shadow-md text-xs">
                            <i className="fa-regular fa-clock text-white mr-1"></i>
                            <span>
                              {venue.start_time?.slice(0, 5)} -{" "}
                              {venue.end_time?.slice(0, 5)}
                            </span>
                          </div>
                        </div>

                        <div className="p-3 flex-1 flex flex-col">
                          <div className="flex flex-wrap gap-1 mb-1">
                            {venue.venue_types?.length ? (
                              venue.venue_types.map((type, i) => (
                                <span
                                  key={i}
                                  className="text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium line-clamp-1"
                                >
                                  {type.name}
                                </span>
                              ))
                            ) : (
                              <span className="text-xs text-gray-500 italic">
                                Chưa có loại hình
                              </span>
                            )}
                          </div>

                          <h3 className="text-sm md:text-base font-semibold text-gray-900 mb-1 line-clamp-1">
                            {venue.name}
                          </h3>

                          <div className="flex items-start text-xs md:text-sm text-gray-600 mb-2">
                            <i className="fa-solid fa-location-dot text-emerald-500 mt-0.5 mr-1 flex-shrink-0"></i>
                            <span className="line-clamp-2">
                              {venue.address_detail}
                            </span>
                          </div>

                          <button
                            onClick={() => navigate(`/venues/${venue.id}`)}
                            className="mt-auto bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
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
          )}
        </div>
      </section>
    </>
  );
};

export default Content;
