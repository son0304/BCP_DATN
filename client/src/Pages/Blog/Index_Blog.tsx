import React from 'react';
import { Link } from 'react-router-dom';

const Index_Blog = () => {
    // Thêm trường 'category' giả lập để giao diện phong phú hơn
    const news = [
        {
            id: 1,
            category: "Giải đấu",
            title: "Giải Pickleball BCP Sports Mở Rộng 2025 – Đăng Ký Ngay!",
            excerpt: "Tham gia giải đấu lớn nhất năm với tổng giải thưởng lên đến 500 triệu đồng. Cơ hội giao lưu với các tay vợt hàng đầu.",
            date: "28/10/2025",
            image: "https://images.pexels.com/photos/8639888/pexels-photo-8639888.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 2,
            category: "Mẹo hay",
            title: "5 Mẹo Bảo Dưỡng Sân Pickleball Trong Mùa Mưa",
            excerpt: "Giữ sân luôn sạch và an toàn với những bí quyết từ chuyên gia. Cách xử lý thoát nước và chống trơn trượt hiệu quả.",
            date: "25/10/2025",
            image: "https://images.pexels.com/photos/5717459/pexels-photo-5717459.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 3,
            category: "Công nghệ",
            title: "BCP Sports Ra Mắt Tính Năng Đặt Sân Tự Động",
            excerpt: "Chỉ 3 giây để đặt sân – trải nghiệm công nghệ mới nhất từ BCP Sports. Tích hợp thanh toán ví điện tử siêu tốc.",
            date: "20/10/2025",
            image: "https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 4,
            category: "Cộng đồng",
            title: "Hành Trình Phát Triển Pickleball Tại Việt Nam",
            excerpt: "Từ một môn thể thao mới đến cộng đồng hơn 50.000 người chơi chỉ trong 2 năm. Những con số ấn tượng.",
            date: "18/10/2025",
            image: "https://images.pexels.com/photos/4056535/pexels-photo-4056535.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 5,
            category: "Review",
            title: "Top 10 Vợt Pickleball Được Ưa Chuộng Nhất 2025",
            excerpt: "Đánh giá chi tiết từ người chơi thực tế tại hệ thống sân Court Prime. So sánh ưu nhược điểm từng dòng vợt.",
            date: "15/10/2025",
            image: "https://images.pexels.com/photos/4498628/pexels-photo-4498628.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 6,
            category: "Sự kiện",
            title: "Lớp Học Pickleball Miễn Phí Cho Người Mới",
            excerpt: "Đăng ký ngay để nhận buổi học thử miễn phí với HLV chuyên nghiệp. Trang bị kiến thức cơ bản cho người mới bắt đầu.",
            date: "12/10/2025",
            image: "https://images.pexels.com/photos/4056688/pexels-photo-4056688.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        }
    ];

    return (
        <div className="bg-[#F8FAFC] min-h-screen font-sans">
            
            {/* --- HERO SECTION --- */}
            <section className="bg-white border-b border-gray-100 py-16 md:py-20">
                <div className="container mx-auto px-4 max-w-5xl text-center">
                    <span className="inline-block py-1 px-3 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-[10px] md:text-xs font-bold uppercase tracking-widest mb-4">
                        BCP Sports Blog
                    </span>
                    <h1 className="text-3xl md:text-5xl font-extrabold text-[#11182C] mb-4 tracking-tight">
                        Tin Tức & Sự Kiện
                    </h1>
                    <p className="text-gray-500 text-sm md:text-base max-w-2xl mx-auto leading-relaxed">
                        Cập nhật những thông tin nóng hổi nhất về giải đấu, bí quyết chơi thể thao và các tính năng mới từ hệ thống Court Prime.
                    </p>
                </div>
            </section>

            {/* --- MAIN CONTENT --- */}
            <div className="container mx-auto px-4 py-12 max-w-6xl">
                
                {/* Header Section */}
                <div className="flex items-center justify-between mb-8">
                    <h2 className="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <span className="w-2 h-6 bg-[#10B981] rounded-full"></span>
                        Bài viết nổi bật
                    </h2>
                    <div className="hidden md:flex gap-2">
                         {['Tất cả', 'Giải đấu', 'Mẹo hay', 'Công nghệ'].map((tab, i) => (
                             <button key={i} className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${i === 0 ? 'bg-[#10B981] text-white shadow-md shadow-emerald-200' : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200'}`}>
                                 {tab}
                             </button>
                         ))}
                    </div>
                </div>

                {/* Grid News */}
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    {news.map((item) => (
                        <Link
                            key={item.id}
                            to={`/blog/${item.id}`}
                            className="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full"
                        >
                            {/* Image Wrapper */}
                            <div className="relative overflow-hidden h-48 md:h-52">
                                <img
                                    src={item.image}
                                    alt={item.title}
                                    className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                    loading="lazy"
                                />
                                {/* Overlay Gradient */}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                                
                                {/* Category Badge */}
                                <div className="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide text-[#10B981] shadow-sm">
                                    {item.category}
                                </div>
                            </div>

                            {/* Content */}
                            <div className="p-5 flex-1 flex flex-col">
                                {/* Meta Data */}
                                <div className="flex items-center gap-3 text-xs text-gray-400 mb-3">
                                    <span className="flex items-center gap-1">
                                        <i className="fa-regular fa-calendar"></i> {item.date}
                                    </span>
                                    <span className="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span className="flex items-center gap-1">
                                        <i className="fa-regular fa-clock"></i> 5 phút đọc
                                    </span>
                                </div>

                                <h3 className="text-lg font-bold text-gray-800 mb-3 leading-snug group-hover:text-[#10B981] transition-colors line-clamp-2">
                                    {item.title}
                                </h3>
                                
                                <p className="text-sm text-gray-500 line-clamp-3 mb-4 flex-1">
                                    {item.excerpt}
                                </p>

                                {/* Footer Link */}
                                <div className="mt-auto flex items-center text-xs font-bold text-[#10B981] group-hover:underline decoration-2 underline-offset-4">
                                    Đọc chi tiết <i className="fa-solid fa-arrow-right-long ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>

                {/* Pagination (Mock) */}
                <div className="mt-12 flex justify-center gap-2">
                    <button className="w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-[#10B981] transition-colors"><i className="fa-solid fa-chevron-left"></i></button>
                    <button className="w-10 h-10 rounded-lg bg-[#10B981] text-white font-bold shadow-md shadow-emerald-200">1</button>
                    <button className="w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-[#10B981] transition-colors font-medium">2</button>
                    <button className="w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-[#10B981] transition-colors font-medium">3</button>
                    <span className="w-10 h-10 flex items-center justify-center text-gray-400">...</span>
                    <button className="w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-[#10B981] transition-colors"><i className="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

            {/* --- NEWSLETTER CTA --- */}
            <section className="py-16 px-4">
                <div className="container mx-auto max-w-4xl">
                    <div className="bg-gradient-to-r from-[#10B981] to-teal-600 rounded-3xl p-8 md:p-12 text-center text-white shadow-2xl shadow-emerald-600/30 relative overflow-hidden">
                        {/* Decor Circles */}
                        <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3"></div>
                        <div className="absolute bottom-0 left-0 w-48 h-48 bg-amber-400/20 rounded-full blur-2xl -translate-x-1/3 translate-y-1/3"></div>

                        <div className="relative z-10">
                            <i className="fa-regular fa-envelope-open text-4xl mb-4 opacity-90"></i>
                            <h2 className="text-2xl md:text-3xl font-bold mb-3">
                                Đăng ký nhận bản tin
                            </h2>
                            <p className="text-emerald-100 text-sm md:text-base mb-8 max-w-lg mx-auto">
                                Nhận thông báo về các giải đấu mới, mã giảm giá đặt sân và các mẹo chơi thể thao hữu ích hàng tuần.
                            </p>
                            
                            <form className="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                                <input 
                                    type="email" 
                                    placeholder="Nhập email của bạn..." 
                                    className="flex-1 px-5 py-3 rounded-full text-gray-800 text-sm outline-none focus:ring-2 focus:ring-amber-300 shadow-sm"
                                />
                                <button className="px-6 py-3 bg-[#F59E0B] hover:bg-amber-600 text-white font-bold text-sm rounded-full shadow-lg transition-all transform hover:-translate-y-0.5 whitespace-nowrap">
                                    Đăng Ký
                                </button>
                            </form>
                            <p className="text-xs text-emerald-200 mt-4 opacity-70">
                                Chúng tôi cam kết không spam. Hủy đăng ký bất cứ lúc nào.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    );
};

export default Index_Blog;