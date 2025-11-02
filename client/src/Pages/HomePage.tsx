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
                    <h1 className="text-4xl md:text-6xl font-extrabold leading-tight mb-4 md:mb-6 text-white drop-shadow-lg">
                        {banner.title}
                    </h1>
                    <p className="text-sm md:text-lg text-emerald-100 mb-6 md:mb-8 leading-relaxed max-w-2xl">
                        {banner.desc}
                    </p>
                </motion.div>

                <motion.div
                    initial={{ opacity: 0, y: 60 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.6, duration: 0.8 }}
                    className="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 w-full max-w-6xl px-6 z-20"
                >
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        {[
                            { name: "Pickleball", color: "from-green-100 to-green-200" },
                            { name: "Cầu lông", color: "from-green-100 to-green-200" },
                            { name: "Bóng đá", color: "from-green-100 to-green-200" },
                        ].map((item, i) => (
                            <motion.div
                                key={i}
                                whileHover={{ scale: 1.05, y: -6 }}
                                transition={{ type: "spring", stiffness: 200, damping: 15 }}
                                className={`bg-gradient-to-br ${item.color} rounded-xl py-10 text-center font-semibold text-2xl text-emerald-900 shadow-lg hover:brightness-110 transition-all duration-300 cursor-pointer`}
                            >
                                {item.name}
                            </motion.div>
                        ))}
                    </div>
                </motion.div>
            </section>

            {/* Gợi ý sân */}
            <section className="py-8 md:py-16 from-white to-gray-50 mt-10 md:mt-16">
                <div className="container max-w-7xl mx-auto px-4">
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h1 className="text-2xl md:text-3xl font-bold text-[#2d6a2d]">
                                Gợi ý cho bạn
                            </h1>
                            <p className="text-gray-600 text-sm md:text-base">
                                Những sân thể thao được yêu thích nhất
                            </p>
                        </div>
                        <Link to="/venues">
                            <button className="flex items-center gap-2 text-[#2d6a2d] hover:text-green-700 transition">
                                <span>Xem thêm</span>
                                <i className="fa-solid fa-arrow-right"></i>
                            </button>
                        </Link>
                    </div>

                    {/* Lưới sân hiển thị trực tiếp */}
                    {isError ? (
                        <p className="text-center text-red-500 py-10">
                            Đã xảy ra lỗi khi tải dữ liệu sân!
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            {isLoading
                                ? Array.from({ length: 4 }).map((_, index) => (
                                    <div
                                        key={index}
                                        className="bg-white rounded-3xl shadow-md animate-pulse overflow-hidden border border-gray-200 h-full flex flex-col"
                                    >
                                        <div className="w-full h-64 bg-gray-200"></div>
                                        <div className="p-5 space-y-3">
                                            <div className="h-5 bg-gray-300 w-3/4 rounded"></div>
                                            <div className="h-4 bg-gray-200 w-1/2 rounded"></div>
                                            <div className="h-4 bg-gray-200 w-2/3 rounded"></div>
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
                                                className="relative bg-white rounded-3xl shadow-lg hover:shadow-2xl overflow-hidden border border-gray-100 transition-all duration-500 group hover:-translate-y-2 h-full flex flex-col"
                                            >
                                                <div className="relative">
                                                    <img
                                                        onClick={() => navigate(`/venues/${venue.id}`)}
                                                        src={
                                                            primaryImage?.url ||
                                                            "https://via.placeholder.com/400x300?text=BCP+Sports"
                                                        }
                                                        alt={venue.name}
                                                        className="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer"
                                                    />
                                                    <div className="absolute top-3 right-3 bg-green-600 text-white text-sm px-3 py-1 rounded-full shadow-md">
                                                        Mở cửa
                                                    </div>
                                                    <div className="absolute bottom-0 left-0 bg-[#2d6a2d] text-white px-3 py-2 rounded-tr-2xl flex items-center gap-2">
                                                        <span className="bg-white text-[#2d6a2d] text-sm font-semibold px-2 py-0.5 rounded">
                                                            {Number(venue.reviews_avg_rating)?.toFixed(1) ?? "0.0"}
                                                        </span>
                                                        <div className="flex items-center gap-1">
                                                            {[...Array(5)].map((_, i) => (
                                                                <i
                                                                    key={i}
                                                                    className={`fa-star fa-solid text-sm ${i <
                                                                        Math.round(Number(venue.reviews_avg_rating) || 0)
                                                                        ? "text-yellow-400"
                                                                        : "text-gray-300"
                                                                        }`}
                                                                ></i>
                                                            ))}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="p-5 flex-1 flex flex-col">
                                                    <h3 className="text-xl font-semibold text-gray-900 mb-2 line-clamp-1">
                                                        {venue.name}
                                                    </h3>
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
                                                    {venue.start_time && venue.end_time ? (
                                                        <div className="flex items-center text-sm text-gray-500 mt-auto">
                                                            <i className="fa-regular fa-clock text-[#2d6a2d] mr-2"></i>
                                                            <span className="font-semibold text-gray-800">
                                                                Mở cửa:
                                                            </span>
                                                            &nbsp;
                                                            <span className="text-[#348738] font-medium">
                                                                {venue.start_time.slice(0, 5)} - {venue.end_time.slice(0, 5)}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <div className="flex items-center text-sm text-gray-400 italic mt-auto">
                                                            <i className="fa-regular fa-clock text-gray-400 mr-2"></i>
                                                            Chưa có giờ hoạt động
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

                                                <div className="border-t px-4 py-3 flex items-center justify-between bg-gray-50">
                                                    <div className="flex items-center gap-2">
                                                        <img
                                                            src={primaryImage?.url || "/images/default-venue.jpg"}
                                                            alt={venue.name}
                                                            className="w-8 h-8 rounded-full object-cover"
                                                        />
                                                        <span className="text-sm font-medium text-gray-700">
                                                            {venue.name}
                                                        </span>
                                                    </div>
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

            {/* Lý do chọn chúng tôi */}
            <section className="py-12 md:py-20 to-green-50">
                <div className="container mx-auto max-w-7xl px-4">
                    <div className="text-center mb-16">
                        {/* Tiêu đề xanh lá */}
                        <h1 className="md:text-5xl text-3xl font-bold text-[#2d6a2d] my-4">
                            Tại sao lại chọn chúng tôi
                        </h1>
                        <p className="text-lg text-gray-600">Những lý do khiến BCP trở thành lựa chọn hàng đầu</p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {/* Các card "Why Us" dùng màu xanh lá */}
                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fas fa-bolt text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Đặt sân nhanh chóng</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Chỉ mất 2 phút để hoàn tất đặt sân trực tuyến 24/7
                                </p>
                            </div>
                        </div>

                        {/* (Các card 2, 3, 4 tương tự) */}
                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fa-solid fa-hand-holding-dollar text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Giá cả hợp lý</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    So sánh giá từ nhiều sân, nhiều ưu đãi hấp dẫn
                                </p>
                            </div>
                        </div>

                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fa-solid fa-trophy text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Sân chất lượng</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Đối tác sân uy tín, cơ sở vật chất hiện đại
                                </p>
                            </div>
                        </div>

                        <div className="group relative bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl flex flex-col items-center justify-center text-center transition-all duration-500 hover:-translate-y-2 border border-white/20 min-h-[280px] p-8">
                            <div className="absolute inset-0 bg-gradient-to-br from-[#348738]/10 to-[#2d6a2d]/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <div className="relative z-10 flex flex-col items-center justify-center">
                                <div className="w-16 h-16 bg-gradient-to-br from-[#2d6a2d] to-[#348738] hover:from-[#348738] hover:to-[#2d6a2d] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                    <i className="fas fa-bullseye text-white text-2xl"></i>
                                </div>
                                <h2 className="text-xl font-bold text-gray-800 mb-3">Đa dạng lựa chọn</h2>
                                <p className="text-gray-600 text-sm leading-relaxed">
                                    Bóng đá, cầu lông, tennis, bóng rổ và nhiều hơn nữa
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
};

export default Content;
