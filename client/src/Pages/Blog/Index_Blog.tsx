import React from 'react';
import { Link } from 'react-router-dom';

const Index_Blog = () => {
    const news = [
        {
            id: 1,
            title: "Giải Pickleball BCP Sports Mở Rộng 2025 – Đăng Ký Ngay!",
            excerpt: "Tham gia giải đấu lớn nhất năm với tổng giải thưởng lên đến 500 triệu đồng...",
            date: "28/10/2025",
            image: "https://images.pexels.com/photos/8639888/pexels-photo-8639888.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 2,
            title: "5 Mẹo Bảo Dưỡng Sân Pickleball Trong Mùa Mưa",
            excerpt: "Giữ sân luôn sạch và an toàn với những bí quyết từ chuyên gia...",
            date: "25/10/2025",
            image: "https://images.pexels.com/photos/5717459/pexels-photo-5717459.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 3,
            title: "BCP Sports Ra Mắt Tính Năng Đặt Sân Tự Động",
            excerpt: "Chỉ 3 giây để đặt sân – trải nghiệm công nghệ mới nhất từ BCP Sports...",
            date: "20/10/2025",
            image: "https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 4,
            title: "Hành Trình Phát Triển Pickleball Tại Việt Nam",
            excerpt: "Từ một môn thể thao mới đến cộng đồng hơn 50.000 người chơi chỉ trong 2 năm...",
            date: "18/10/2025",
            image: "https://images.pexels.com/photos/4056535/pexels-photo-4056535.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 5,
            title: "Top 10 Vợt Pickleball Được Ưa Chuộng Nhất 2025",
            excerpt: "Đánh giá chi tiết từ người chơi thực tế tại hệ thống sân Court Prime...",
            date: "15/10/2025",
            image: "https://images.pexels.com/photos/4498628/pexels-photo-4498628.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        },
        {
            id: 6,
            title: "Lớp Học Pickleball Miễn Phí Cho Người Mới",
            excerpt: "Đăng ký ngay để nhận buổi học thử miễn phí với HLV chuyên nghiệp...",
            date: "12/10/2025",
            image: "https://images.pexels.com/photos/4056688/pexels-photo-4056688.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&fit=crop"
        }
    ];

    return (
        <div className="bg-gray-50 min-h-screen">
            <div className="container mx-auto px-4 py-12 md:py-16 max-w-5xl">

                {/* --- Phần Hero --- */}
                <section className="text-center mb-16">
                    <h1 className="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                        Tin Tức & Sự Kiện từ
                    </h1>
                    <p className="text-xl text-gray-600 mb-8">
                        Cập nhật thông tin mới nhất về giải đấu, mẹo chơi, và cập nhật từ hệ thống.
                    </p>
                </section>

                {/* --- Danh sách Tin tức --- */}
                <section className="mb-16">
                    <h2 className="text-3xl font-bold text-center mb-10">Tin Tức Nổi Bật</h2>
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {news.map((item) => (
                            <Link
                                key={item.id}
                                to={`/blog/${item.id}`}
                                className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 block group"
                            >
                                <div className="relative overflow-hidden">
                                    <img
                                        src={item.image}
                                        alt={item.title}
                                        className="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                                <div className="p-5">
                                    <div className="text-sm text-gray-500 mb-2">{item.date}</div>
                                    <h3 className="text-xl font-semibold text-gray-800 mb-2 line-clamp-2 group-hover:text-[#348738] transition-colors">
                                        {item.title}
                                    </h3>
                                    <p className="text-gray-600 line-clamp-3 mb-3">{item.excerpt}</p>
                                    <span className="text-[#348738] font-medium inline-flex items-center group-hover:underline">
                                        Đọc thêm
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                </section>

                {/* --- CTA Cuối --- */}
                <section className="text-center mt-16 bg-white p-10 rounded-lg shadow-lg">
                    <h2 className="text-3xl font-bold text-gray-800 mb-4">
                        Bạn muốn cập nhật tin tức thường xuyên?
                    </h2>
                    <p className="text-lg text-gray-600 mb-8">
                        Theo dõi BCP Sports để không bỏ lỡ bất kỳ thông tin nào!
                    </p>
                    <button className="bg-[#348738] text-white font-bold py-3 px-8 rounded-lg text-lg hover:opacity-90 transition-all shadow-lg transform hover:scale-105">
                        Theo Dõi Ngay
                    </button>
                </section>

            </div>
        </div>
    );
};

export default Index_Blog;