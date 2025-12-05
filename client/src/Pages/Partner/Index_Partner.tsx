import React from 'react';
import { Link } from 'react-router-dom';

const Index_Partner = () => {
  return (
    <div className="min-h-screen bg-white font-sans selection:bg-emerald-100 selection:text-emerald-700">
      
      {/* --- HERO SECTION --- */}
      <section className="relative pt-20 pb-16 md:pt-28 md:pb-24 overflow-hidden">
        {/* Background Decor */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full z-0 pointer-events-none">
           <div className="absolute top-20 left-10 w-72 h-72 bg-emerald-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
           <div className="absolute top-20 right-10 w-72 h-72 bg-amber-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div className="container mx-auto px-4 max-w-4xl text-center relative z-10">
          <span className="inline-block py-1 px-3 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-xs font-bold uppercase tracking-wider mb-6">
            Dành cho chủ sân
          </span>
          <h1 className="text-3xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
            Trở thành Đối tác của <span className="text-emerald-600">Court Prime</span>
          </h1>
          <p className="text-base md:text-lg text-gray-500 mb-8 max-w-2xl mx-auto leading-relaxed">
            Nền tảng quản lý sân thể thao toàn diện. Tối ưu hóa vận hành, lấp đầy khung giờ trống và gia tăng doanh thu của bạn ngay hôm nay.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link to="create_venue">
              <button className="px-8 py-3.5 rounded-full bg-emerald-600 text-white font-bold text-sm md:text-base shadow-lg shadow-emerald-600/30 hover:bg-emerald-700 hover:-translate-y-1 transition-all duration-300">
                Đăng Ký Ngay
              </button>
            </Link>
            <button className="px-8 py-3.5 rounded-full bg-white text-gray-600 border border-gray-200 font-bold text-sm md:text-base hover:bg-gray-50 transition-all duration-300 flex items-center gap-2">
              <i className="fa-solid fa-play-circle text-emerald-600"></i> Xem Demo
            </button>
          </div>
        </div>
      </section>

      {/* --- LỢI ÍCH (FEATURES) --- */}
      <section className="py-16 bg-gray-50/50 border-y border-gray-100">
        <div className="container mx-auto px-4 max-w-6xl">
          <div className="text-center mb-12">
            <h2 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Tại sao chọn Court Prime?</h2>
            <p className="text-sm text-gray-500">Giải pháp công nghệ giúp bạn đi trước đối thủ.</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                icon: "fa-chart-line",
                color: "text-blue-600 bg-blue-50",
                title: "Tăng Trưởng Doanh Thu",
                desc: "Tiếp cận hàng ngàn người chơi mới mỗi ngày. Tối ưu hóa lịch đặt sân, giảm thiểu tối đa thời gian chết."
              },
              {
                icon: "fa-laptop-code",
                color: "text-emerald-600 bg-emerald-50",
                title: "Quản Lý Thông Minh",
                desc: "Hệ thống quản lý lịch đặt, doanh thu và nhân viên trực quan. Theo dõi số liệu kinh doanh mọi lúc, mọi nơi."
              },
              {
                icon: "fa-headset",
                color: "text-amber-600 bg-amber-50",
                title: "Marketing & Hỗ Trợ",
                desc: "Hỗ trợ quảng bá hình ảnh sân bãi chuyên nghiệp. Đội ngũ kỹ thuật hỗ trợ 24/7 giúp bạn yên tâm vận hành."
              }
            ].map((item, index) => (
              <div key={index} className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group">
                <div className={`w-12 h-12 rounded-xl flex items-center justify-center text-xl mb-4 ${item.color} group-hover:scale-110 transition-transform`}>
                  <i className={`fa-solid ${item.icon}`}></i>
                </div>
                <h3 className="text-lg font-bold text-gray-900 mb-2">{item.title}</h3>
                <p className="text-sm text-gray-500 leading-relaxed">
                  {item.desc}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* --- CHÍNH SÁCH & YÊU CẦU --- */}
      <section className="py-16">
        <div className="container mx-auto px-4 max-w-6xl">
          <div className="grid md:grid-cols-2 gap-12 items-start">
            
            {/* Cột 1: Yêu cầu */}
            <div className="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50">
              <h3 className="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span className="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm"><i className="fa-solid fa-list-check"></i></span>
                Yêu cầu đối tác
              </h3>
              <ul className="space-y-4">
                {[
                  "Giấy phép kinh doanh / Hộ kinh doanh hợp lệ.",
                  "Sân đạt tiêu chuẩn thi đấu & an toàn.",
                  "Hình ảnh/Video sân rõ ràng, trung thực.",
                  "Có tiện ích cơ bản (Wifi, nước, WC, gửi xe).",
                  "Cam kết cập nhật lịch trống đúng thực tế."
                ].map((req, i) => (
                  <li key={i} className="flex items-start gap-3 text-sm text-gray-600">
                    <i className="fa-solid fa-check text-emerald-500 mt-1"></i>
                    <span>{req}</span>
                  </li>
                ))}
              </ul>
            </div>

            {/* Cột 2: Chính sách */}
            <div className="bg-gradient-to-br from-gray-900 to-gray-800 p-8 rounded-3xl text-white shadow-2xl">
              <h3 className="text-xl font-bold mb-6 flex items-center gap-3">
                 <span className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-sm"><i className="fa-solid fa-handshake"></i></span>
                 Quyền lợi & Chính sách
              </h3>
              <ul className="space-y-5">
                {[
                  { title: "Phí hoa hồng thấp", desc: "Chỉ từ 5-10% trên mỗi lượt đặt thành công." },
                  { title: "Thanh toán tự động", desc: "Đối soát và chuyển khoản định kỳ (Ngày 5 hàng tháng)." },
                  { title: "Minh bạch tuyệt đối", desc: "Báo cáo doanh thu chi tiết, rõ ràng từng giao dịch." },
                  { title: "Hỗ trợ 24/7", desc: "Kênh hỗ trợ riêng dành cho đối tác VIP." }
                ].map((policy, i) => (
                  <li key={i} className="flex gap-3">
                    <div className="mt-1 w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></div>
                    <div>
                      <strong className="block text-sm font-bold text-white mb-0.5">{policy.title}</strong>
                      <span className="text-xs text-gray-300 leading-relaxed">{policy.desc}</span>
                    </div>
                  </li>
                ))}
              </ul>
            </div>

          </div>
        </div>
      </section>

      {/* --- QUY TRÌNH (STEPS) --- */}
      <section className="py-16 bg-white">
        <div className="container mx-auto px-4 max-w-5xl">
          <div className="text-center mb-12">
            <h2 className="text-2xl md:text-3xl font-bold text-gray-900">Quy trình Hợp tác</h2>
            <p className="text-sm text-gray-500 mt-2">Đơn giản hóa thủ tục để bạn bắt đầu kinh doanh nhanh nhất.</p>
          </div>

          <div className="grid md:grid-cols-3 gap-6 relative">
            {/* Connector Line (Desktop) */}
            <div className="hidden md:block absolute top-12 left-0 w-full h-0.5 bg-gray-100 -z-10"></div>

            {[
              { step: "01", title: "Đăng ký thông tin", desc: "Điền form đăng ký trực tuyến với thông tin cơ bản về sân." },
              { step: "02", title: "Xác thực & Ký HĐ", desc: "Đội ngũ Court Prime liên hệ xác minh và ký kết hợp đồng." },
              { step: "03", title: "Bắt đầu kinh doanh", desc: "Sân được niêm yết lên hệ thống và sẵn sàng đón khách." }
            ].map((item, index) => (
              <div key={index} className="bg-white p-6 rounded-xl border border-gray-100 text-center shadow-sm hover:shadow-md transition-all">
                <div className="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-lg">
                  {item.step}
                </div>
                <h4 className="text-lg font-bold text-gray-900 mb-2">{item.title}</h4>
                <p className="text-sm text-gray-500 leading-relaxed">
                  {item.desc}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* --- CTA FOOTER --- */}
      <section className="py-16">
        <div className="container mx-auto px-4 max-w-4xl">
          <div className="bg-emerald-600 rounded-3xl p-8 md:p-12 text-center text-white shadow-2xl shadow-emerald-600/40 relative overflow-hidden">
             {/* Pattern BG */}
             <div className="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
             
             <div className="relative z-10">
                <h2 className="text-2xl md:text-4xl font-bold mb-4">Sẵn sàng bùng nổ doanh số?</h2>
                <p className="text-emerald-100 mb-8 text-sm md:text-base max-w-xl mx-auto">
                  Tham gia cộng đồng hơn 500+ chủ sân đang sử dụng Court Prime để quản lý và phát triển kinh doanh.
                </p>
                <Link to="create_venue">
                  <button className="px-8 py-3 bg-white text-emerald-700 font-bold rounded-full shadow-lg hover:bg-emerald-50 transition-all transform hover:-translate-y-1">
                    Đăng Ký Đối Tác Ngay <i className="fa-solid fa-arrow-right ml-2"></i>
                  </button>
                </Link>
             </div>
          </div>
        </div>
      </section>

    </div>
  );
}

export default Index_Partner;