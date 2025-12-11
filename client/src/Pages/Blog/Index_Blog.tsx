import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';

interface NewsItem {
    id: string;
    source: string;
    title: string;
    excerpt: string;
    date: string;
    image: string;
    link: string;
    category: string;
}

const Index_Blog = () => {
    const [news, setNews] = useState<NewsItem[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchNews = async () => {
            setLoading(true);
            try {
                // Sử dụng RSS2JSON để không cần Backend
                const RSS_URL = "https://vnexpress.net/rss/the-thao.rss";
                const API = `https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent(RSS_URL)}`;
                
                const response = await fetch(API);
                const data = await response.json();

                if (data.status === 'ok') {
                    // --- HÀM XỬ LÝ ẢNH THÔNG MINH (Không lo lỗi 403/401) ---
                    const getSafeImage = (htmlContent: string, enclosure: any) => {
                        let url = "";
                        // 1. Tìm ảnh trong thẻ enclosure (chuẩn RSS)
                        if (enclosure?.link) url = enclosure.link;
                        // 2. Nếu không có, quét thẻ <img> trong nội dung html
                        else {
                            const match = htmlContent.match(/src="([^"]+)"/);
                            if (match && match[1]) url = match[1];
                        }
                        
                        // 3. Nếu không tìm thấy ảnh nào -> Dùng ảnh mặc định
                        if (!url) return "https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg";

                        // 4. Dùng Proxy wsrv.nl để né chặn (Hotlink Protection) của báo
                        // Bỏ các tham số rác sau dấu ? để tránh lỗi
                        return `https://wsrv.nl/?url=${encodeURIComponent(url.split('?')[0])}&w=800&output=webp`;
                    };

                    const cleanExcerpt = (html: string) => {
                        return html.replace(/<[^>]+>/g, '').substring(0, 110) + "...";
                    };

                    const formattedNews = data.items.map((item: any, index: number) => ({
                        id: `rss-${index}`,
                        source: 'VnExpress',
                        title: item.title,
                        excerpt: cleanExcerpt(item.description),
                        date: item.pubDate.split(' ')[0], 
                        image: getSafeImage(item.description, item.enclosure), // Đã xử lý ảnh tại đây
                        link: item.link,
                        category: 'Tin Thể Thao',
                    }));

                    // --- TIN NỘI BỘ (Chèn thêm để Marketing) ---
                    const internalNews: NewsItem[] = [
                        {
                            id: 'int-1',
                            source: 'Sân Của Chúng Tôi',
                            title: "🏆 Giải Đấu Mùa Hè 2025: Đăng ký ngay để nhận quà khủng",
                            excerpt: "Giải đấu Pickleball phong trào lớn nhất năm. Tổng giải thưởng 50 triệu đồng.",
                            date: "Hôm nay",
                            // Ảnh nội bộ này không cần qua proxy vì nó không bị chặn
                            image: "https://images.pexels.com/photos/18395560/pexels-photo-18395560.jpeg", 
                            link: "/",
                            category: 'Sự kiện',
                        }
                    ];

                    setNews([...internalNews, ...formattedNews]);
                }
            } catch (error) {
                console.error("Lỗi:", error);
            } finally {
                setLoading(false);
            }
        };

        fetchNews();
    }, []);

    // --- UI (Copy phần return cũ, không cần sửa gì) ---
    return (
        <div className="bg-gray-50 min-h-screen font-sans text-gray-800">
             {/* Ticker chạy chữ */}
             <div className="bg-[#111827] text-white text-xs py-2 overflow-hidden relative z-20">
                <div className="container mx-auto px-4 flex items-center">
                    <span className="bg-red-600 px-2 py-0.5 font-bold uppercase tracking-wider mr-4 animate-pulse">Breaking</span>
                    <div className="whitespace-nowrap overflow-hidden flex-1">
                        <div className="inline-block animate-marquee pl-full">
                            {news.map((n, i) => (
                                <span key={i} className="mr-12 opacity-90 cursor-pointer hover:text-[#10B981]">
                                    🔥 {n.title}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Hero Section */}
            {!loading && news.length > 0 && (
                <section className="relative h-[450px] w-full group overflow-hidden">
                    <img 
                        src={news[0].image} 
                        alt="Hero" 
                        className="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                    <div className="absolute bottom-0 left-0 w-full p-6 md:p-12">
                        <div className="container mx-auto">
                            <span className="bg-[#10B981] text-white text-xs font-bold px-3 py-1 rounded uppercase mb-3 inline-block">
                                {news[0].category}
                            </span>
                            <h1 className="text-2xl md:text-4xl font-extrabold text-white mb-4 leading-tight max-w-4xl shadow-black drop-shadow-md">
                                {news[0].title}
                            </h1>
                            <Link to={news[0].link} className="bg-white text-black px-6 py-2 rounded-full font-bold hover:bg-[#10B981] hover:text-white transition">
                                Đọc ngay
                            </Link>
                        </div>
                    </div>
                </section>
            )}

            {/* Main Content */}
            <div className="container mx-auto px-4 py-12">
                <div className="flex flex-col lg:flex-row gap-10">
                    <div className="lg:w-2/3 space-y-8">
                        {loading ? (
                            <p className="text-center py-12 text-gray-400">Đang tải tin tức...</p>
                        ) : news.slice(1).map(item => (
                             <article key={item.id} className="flex flex-col md:flex-row gap-5 group border-b border-gray-100 pb-6 last:border-0">
                                <a href={item.link} target="_blank" rel="noreferrer" className="w-full md:w-5/12 h-48 overflow-hidden rounded-xl relative bg-gray-200 block">
                                    <img 
                                        src={item.image} 
                                        alt={item.title} 
                                        className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    />
                                </a>
                                <div className="flex-1 py-1">
                                    <div className="flex items-center gap-2 mb-2 text-xs text-gray-500">
                                        <span className="font-bold text-[#10B981] uppercase">{item.category}</span>
                                        <span>• {item.date}</span>
                                        <span>• {item.source}</span>
                                    </div>
                                    <h3 className="text-lg font-bold mb-3 group-hover:text-[#10B981] transition-colors line-clamp-2 leading-snug">
                                        <a href={item.link} target="_blank" rel="noreferrer">{item.title}</a>
                                    </h3>
                                    <p className="text-sm text-gray-500 line-clamp-2">{item.excerpt}</p>
                                </div>
                            </article>
                        ))}
                    </div>

                    <aside className="lg:w-1/3 space-y-6">
                        <div className="bg-[#111827] rounded-2xl p-6 text-white text-center shadow-lg relative overflow-hidden">
                             <div className="absolute top-0 right-0 w-24 h-24 bg-[#10B981] rounded-full blur-2xl opacity-20"></div>
                            <h3 className="text-xl font-bold mb-2 relative z-10">Bạn muốn ra sân?</h3>
                            <Link to="/" className="block w-full py-3 bg-[#10B981] hover:bg-emerald-500 rounded-xl font-bold transition mt-4 relative z-10">
                                Đặt Lịch Ngay
                            </Link>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    );
};

export default Index_Blog;