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
            {/* 🎯 Banner */}
            <section className="bg-gradient-to-br from-[#D1FAE5] via-[#ECFDF5] to-[#CCFBF1] h-[260px] md:h-[400px] mt-2 relative overflow-hidden">
                <div className="absolute inset-0 flex flex-col md:flex-row items-center justify-between px-4 md:px-20 gap-4 md:gap-6">
                    <div className="text-center md:text-left z-10 max-w-xl">
                        <h1 className="text-[24px] md:text-[48px] font-extrabold text-[#11182C] mb-2 md:mb-5 leading-tight drop-shadow-sm">
                            Đặt sân thể thao <br />
                            <span className="text-[#10B981]">Nhanh chóng & Tiện lợi</span>
                        </h1>
                        <p className="text-[14px] md:text-[16px] text-[#4B5563] mb-4 md:mb-8 leading-relaxed">
                            Tìm và đặt sân bóng, cầu lông, tennis... chỉ trong vài cú nhấp!
                            Theo dõi lịch đặt và khuyến mãi dễ dàng trên mọi thiết bị.
                        </p>
                    </div>

                    <div className="relative flex justify-center md:justify-end w-full md:w-auto">
                        <img
                            src="/logo.png"
                            alt="Logo Booking Sân"
                            className="w-[120px] md:w-[300px] drop-shadow-2xl rounded-xl bg-white/70 backdrop-blur-sm p-3 hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                </div>
            </section>

            {/* 🔍 Form tìm kiếm */}
            <section className="container mx-auto max-w-5xl bg-[#FFFFFF] md:h-64 h-full md:-mt-20 md:relative md:z-10 rounded-2xl shadow-2xl border border-[#E5E7EB] p-4 md:p-6">
                <div className="w-full text-center mb-4">
                    <h1 className="text-[20px] md:text-[30px] font-bold text-[#11182C] mb-2">
                        Tìm sân nhanh
                    </h1>
                    <p className="text-[13px] md:text-[14px] text-[#6B7280]">
                        Tìm kiếm sân thể thao phù hợp với bạn
                    </p>
                </div>

                <form className="grid md:grid-cols-4 grid-cols-1 gap-3 md:gap-4">
                    <div className="relative flex items-center border-2 border-[#E5E7EB] hover:border-[#10B981] p-2 md:p-3 rounded-2xl transition-all duration-300">
                        <i className="fa-solid fa-futbol text-[#10B981] text-base md:text-lg mr-3"></i>
                        <select className="w-full border-none bg-transparent outline-none text-[#4B5563] text-sm md:text-base font-medium">
                            <option value="">Chọn môn thể thao</option>
                            <option value="football">⚽ Bóng đá</option>
                            <option value="badminton">🏸 Cầu lông</option>
                            <option value="tennis">🎾 Tennis</option>
                            <option value="basketball">🏀 Bóng rổ</option>
                        </select>
                    </div>

                    <div className="relative flex items-center border-2 border-[#E5E7EB] hover:border-[#10B981] p-2 md:p-3 rounded-2xl transition-all duration-300">
                        <i className="fa-solid fa-map-marker-alt text-[#10B981] text-base md:text-lg mr-3"></i>
                        <select className="w-full border-none bg-transparent outline-none text-[#4B5563] text-sm md:text-base font-medium">
                            <option value="">Chọn khu vực</option>
                            <option value="district1">Quận 1</option>
                            <option value="district2">Quận 2</option>
                            <option value="district7">Quận 7</option>
                        </select>
                    </div>

                    <div className="relative flex items-center border-2 border-[#E5E7EB] hover:border-[#10B981] p-2 md:p-3 rounded-2xl transition-all duration-300">
                        <i className="fa-solid fa-calendar text-[#10B981] text-base md:text-lg mr-3"></i>
                        <select className="w-full border-none bg-transparent outline-none text-[#4B5563] text-sm md:text-base font-medium">
                            <option value="">Chọn ngày</option>
                            <option value="today">Hôm nay</option>
                            <option value="tomorrow">Ngày mai</option>
                            <option value="weekend">Cuối tuần</option>
                        </select>
                    </div>

                    <button className="p-2 md:p-3 bg-[#10B981] hover:bg-[#0EA371] text-white text-sm md:text-base font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                        <i className="fa-solid fa-search"></i>
                        <span>Tìm kiếm</span>
                    </button>
                </form>
            </section>

            {/* 🏟️ Gợi ý sân */}
            <section className="py-8 md:py-16 bg-[#F9FAFB]">
                <div className="container max-w-7xl mx-auto px-4">
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h1 className="text-[22px] md:text-[30px] font-bold text-[#10B981]">
                                Gợi ý cho bạn
                            </h1>
                            <p className="text-[13px] md:text-[14px] text-[#6B7280]">
                                Những sân thể thao được yêu thích nhất
                            </p>
                        </div>
                        <Link to="/venues">
                            <button className="flex items-center gap-2 text-[#10B981] hover:text-[#0EA371] text-sm md:text-base transition font-semibold">
                                <span>Xem thêm</span>
                                <i className="fa-solid fa-arrow-right"></i>
                            </button>
                        </Link>
                    </div>

                    {isError ? (
                        <p className="text-center text-[#EF4444] py-10">Đã xảy ra lỗi khi tải dữ liệu sân!</p>
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
                                                    <div className="absolute top-0 right-0 bg-[#10B981] text-white px-2 py-1 rounded-bl-md flex items-center gap-1 shadow-md text-xs">
                                                        <i className="fa-solid fa-star text-yellow-400"></i>
                                                        <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                                                    </div>
                                                    <div className="absolute bottom-0 left-0 bg-[#10B981] text-white px-2 py-1 rounded-tr-md flex items-center gap-1 shadow-md text-xs">
                                                        <i className="fa-regular fa-clock text-white mr-1"></i>
                                                        <span>
                                                            {venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}
                                                        </span>
                                                    </div>
                                                </div>

                                                {/* Nội dung */}
                                                <div className="p-3 flex-1 flex flex-col">
                                                    <div className="flex flex-wrap gap-1 mb-1">
                                                        {venue.venue_types?.length ? (
                                                            venue.venue_types.map((type, i) => (
                                                                <span
                                                                    key={i}
                                                                    className="text-[10px] sm:text-[11px] bg-[#D1FAE5] text-[#065F46] px-1.5 py-0.5 rounded-full font-medium line-clamp-1"
                                                                >
                                                                    {type.name}
                                                                </span>
                                                            ))
                                                        ) : (
                                                            <span className="text-[11px] text-gray-500 italic">
                                                                Chưa có loại hình
                                                            </span>
                                                        )}
                                                    </div>

                                                    <h3 className="text-[13px] sm:text-[15px] font-semibold text-[#11182C] mb-1 line-clamp-1">
                                                        {venue.name}
                                                    </h3>

                                                    <div className="flex items-start text-[11px] sm:text-[13px] text-gray-600 mb-2">
                                                        <i className="fa-solid fa-location-dot text-[#10B981] mt-0.5 mr-1 flex-shrink-0"></i>
                                                        <span className="line-clamp-2">{venue.address_detail}</span>
                                                    </div>

                                                    <button
                                                        onClick={() => navigate(`/venues/${venue.id}`)}
                                                        className="mt-auto bg-[#F59E0B] hover:bg-[#D97706] text-white font-semibold text-[11px] sm:text-[13px] py-1.5 sm:py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
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
