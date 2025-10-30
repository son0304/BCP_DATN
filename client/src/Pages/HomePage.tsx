import { Link, useNavigate } from "react-router-dom";
import { useFetchData } from "../Hooks/useApi";
import type { Venue } from "../Types/venue";
import type { Image } from "../Types/image";

const Content = () => {
    const navigate = useNavigate();
    const { data: venueData, isLoading, isError } = useFetchData<Venue[]>("venues");

    const venues: Venue[] = (venueData?.data as Venue[]) || [];
    const displayedVenues = venues.slice(0, 4);

    return (
        <>
            {/* Banner đầu trang */}
            <section className="bg-gradient-to-br from-green-100 via-emerald-50 to-teal-100 h-[200px] md:h-[400px] mt-2 relative overflow-hidden">
                <div className="absolute inset-0 flex flex-col md:flex-row items-center justify-between px-6 md:px-20 gap-6">
                    <div className="text-center md:text-left z-10 max-w-xl">
                        <h1 className="text-3xl md:text-5xl font-extrabold text-emerald-800 mb-3 md:mb-5 leading-tight drop-shadow-sm">
                            Đặt sân thể thao <br />
                            <span className="text-emerald-600">Nhanh chóng & Tiện lợi</span>
                        </h1>
                        <p className="text-emerald-700 text-sm md:text-base mb-5 md:mb-8 leading-relaxed">
                            Tìm và đặt sân bóng, cầu lông, pickleball... chỉ trong vài cú nhấp!
                            Theo dõi lịch đặt và khuyến mãi dễ dàng trên mọi thiết bị.
                        </p>
                    </div>

                    <div className="relative flex justify-center md:justify-end w-full md:w-auto">
                        <img
                            src="/logo.png"
                            alt="Logo Booking Sân"
                            className="w-[160px] md:w-[300px] drop-shadow-2xl rounded-xl bg-white/70 backdrop-blur-sm p-4 hover:scale-105 transition-transform duration-300"
                        />
                        <div className="absolute -bottom-4 -left-6 w-10 h-10 bg-green-500 rounded-full blur-md animate-bounce opacity-70"></div>
                    </div>
                </div>
                <div className="absolute -bottom-16 -left-10 w-72 h-72 bg-emerald-300 opacity-30 rounded-full blur-3xl animate-pulse"></div>
                <div className="absolute top-0 -right-16 w-80 h-80 bg-teal-200 opacity-40 rounded-full blur-3xl animate-pulse delay-200"></div>
            </section>

            {/* Gợi ý sân */}
            <section className="py-8 md:py-16 from-white to-gray-50">
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
