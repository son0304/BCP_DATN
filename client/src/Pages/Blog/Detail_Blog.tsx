import React from 'react';
import { Link, useParams } from 'react-router-dom';

const blogPosts = [
    {
        id: 1,
        title: "Giải Pickleball BCP Sports Mở Rộng 2025 – Đăng Ký Ngay!",
        date: "28/10/2025",
        author: "BCP Sports",
        image: "https://images.pexels.com/photos/8639888/pexels-photo-8639888.jpeg?auto=compress&cs=tinysrgb&w=1200",
        content: `
      <p><strong>BCP Sports</strong> chính thức công bố <strong>Giải Pickleball Mở Rộng 2025</strong> – sự kiện lớn nhất trong năm dành cho cộng đồng người chơi pickleball tại Việt Nam!</p>

      <h3>Thông tin chi tiết:</h3>
      <ul>
        <li><strong>Thời gian:</strong> 15 – 17/11/2025</li>
        <li><strong>Địa điểm:</strong> Sân Prime , Quận 7, TP.HCM</li>
        <li><strong>Tổng giải thưởng:</strong> 500.000.000 VNĐ</li>
        <li><strong>Bảng đấu:</strong> Nam/Nữ đơn, đôi, đôi nam nữ</li>
      </ul>

      <p>Giải đấu được tài trợ bởi các thương hiệu thể thao hàng đầu và có sự tham gia của các vận động viên chuyên nghiệp từ Thái Lan, Singapore.</p>

      <h3>Lý do bạn nên tham gia:</h3>
      <ul>
        <li>Thi đấu trên sân đạt chuẩn quốc tế</li>
        <li>Cơ hội giao lưu với cộng đồng pickleball lớn nhất Việt Nam</li>
        <li>Nhận quà tặng độc quyền từ Court Prime</li>
      </ul>

      <p><strong>Đăng ký ngay hôm nay tại: <a href="/booking" className="text-[#348738] font-bold underline">courtprime.vn/booking</a></strong></p>
    `
    },
    {
        id: 2,
        title: "5 Mẹo Bảo Dưỡng Sân Pickleball Trong Mùa Mưa",
        date: "25/10/2025",
        author: "BCP Sports",
        image: "https://images.pexels.com/photos/5717459/pexels-photo-5717459.jpeg?auto=compress&cs=tinysrgb&w=1200",
        content: `
      <p>Mùa mưa đến, sân pickleball dễ bị ảnh hưởng bởi nước, rêu mốc và bụi bẩn. Dưới đây là <strong>5 mẹo vàng</strong> giúp chủ sân giữ sân luôn sạch đẹp và an toàn:</p>

      <ol>
        <li><strong>Dọn dẹp ngay sau mưa:</strong> Lau khô mặt sân bằng cây gạt nước chuyên dụng.</li>
        <li><strong>Sử dụng lưới che:</strong> Che phủ sân khi không sử dụng để tránh nước đọng.</li>
        <li><strong>Kiểm tra hệ thống thoát nước:</strong> Đảm bảo rãnh thoát hoạt động tốt.</li>
        <li><strong>Phun chống rêu định kỳ:</strong> Dùng dung dịch chuyên dụng mỗi 2 tuần/lần.</li>
        <li><strong>Sơn lại vạch kẻ:</strong> Khi vạch mờ do mưa xóa.</li>
      </ol>

      <p><em>“Một sân sạch = một trận đấu hay!” – Court Prime</em></p>
    `
    },
    {
        id: 3,
        title: "Court Prime Ra Mắt Tính Năng Đặt Sân Tự Động",
        date: "20/10/2025",
        author: "Product Team",
        image: "https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=1200",
        content: `
      <p>Chúng tôi tự hào giới thiệu <strong>tính năng Đặt Sân Tự Động</strong> – chỉ <strong>3 giây</strong> để có sân chơi!</p>

      <h3>Tính năng nổi bật:</h3>
      <ul>
        <li>Chọn sân → Chọn khung giờ → Thanh toán → Xong!</li>
        <li>Tự động gợi ý sân trống gần nhất</li>
        <li>Nhắc nhở trước 1 giờ qua Zalo/SMS</li>
        <li>Hỗ trợ hủy miễn phí trong 2 giờ đầu</li>
      </ul>

      <p><strong>Cập nhật ứng dụng Court Prime ngay hôm nay để trải nghiệm!</strong></p>
    `
    }
];

const Detail_Blog = () => {
    const { id } = useParams<{ id: string }>();
    const post = blogPosts.find(p => p.id === parseInt(id || ''));

    if (!post) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-800 mb-4">Không tìm thấy bài viết</h1>
                    <Link to="/blog" className="text-[#348738] font-medium hover:underline">
                        ← Quay lại Tin Tức
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-gray-50 min-h-screen">
            <div className="container mx-auto px-4 py-12 md:py-16 max-w-4xl">

                {/* Nút Quay lại */}
                <Link
                    to="/blog"
                    className="inline-flex items-center text-[#348738] font-medium hover:underline mb-8"
                >
                    ← Quay lại Tin Tức
                </Link>

                <div className="mb-8">
                    <img
                        src={post.image}
                        alt={post.title}
                        className="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg"
                        loading="lazy"
                    />
                </div>

                {/* Nội dung bài viết */}
                <div className="bg-white p-8 md:p-12 rounded-lg shadow-xl">
                    <div className="text-sm text-gray-500 mb-2">
                        {post.date} • bởi <span className="font-medium">{post.author}</span>
                    </div>

                    <h1 className="text-3xl md:text-4xl font-bold text-gray-800 mb-6">
                        {post.title}
                    </h1>

                    <div
                        className="prose prose-lg max-w-none text-gray-700 leading-relaxed"
                        dangerouslySetInnerHTML={{ __html: post.content }}
                    />

                    <hr className="my-10 border-gray-200" />
                </div>

                <div className="text-center mt-16 bg-white p-10 rounded-lg shadow-lg">
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">
                        Muốn đọc thêm tin tức?
                    </h2>
                    <Link to="/blog">
                        <button className="bg-[#348738] text-white font-bold py-3 px-8 rounded-lg text-lg hover:opacity-90 transition-all shadow-lg">
                            Xem Tất Cả Tin Tức
                        </button>
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default Detail_Blog;