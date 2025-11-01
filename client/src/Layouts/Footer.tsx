const Footer = () => {
    return (
      <footer className="bg-gradient-to-br from-slate-900 via-gray-900 to-slate-800 text-white relative overflow-hidden">
        {/* --- Hiệu ứng nền --- */}
        <div className="absolute inset-0 bg-gradient-to-r from-[#348738]/10 via-transparent to-[#2d6a2d]/10"></div>
        <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#348738] via-blue-500 to-[#2d6a2d]"></div>
  
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
          {/* --- Lưới chính --- */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10 text-sm sm:text-base">
            {/* --- Logo & mô tả --- */}
            <div>
              <div className="flex items-center gap-3 mb-6">
                <div className="w-12 h-12 bg-gradient-to-br from-[#348738] to-[#2d6a2d] rounded-xl flex items-center justify-center">
                  <i className="fa-solid fa-futbol text-white text-xl"></i>
                </div>
                <h3 className="text-2xl sm:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-[#348738] to-blue-400 bg-clip-text text-transparent">
                  BCP Sports
                </h3>
              </div>
  
              <p className="text-gray-300 text-xs sm:text-sm lg:text-base leading-relaxed mb-6">
                Nền tảng đặt sân thể thao trực tuyến hàng đầu. Kết nối người chơi
                với các sân chất lượng, đặt sân dễ dàng chỉ trong vài giây.
              </p>
            </div>
  
            {/* --- Liên kết nhanh --- */}
            <div>
              <h4 className="text-lg sm:text-xl lg:text-2xl font-bold mb-6 flex items-center gap-2">
                <i className="fa-solid fa-link text-[#348738]"></i>
                Liên kết nhanh
              </h4>
              <ul className="space-y-3 text-xs sm:text-sm lg:text-base">
                <li>
                  <a className="flex items-center gap-2 text-gray-300 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                    <i className="fa-solid fa-home text-[#348738]"></i> Trang chủ
                  </a>
                </li>
                <li>
                  <a className="flex items-center gap-2 text-gray-300 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                    <i className="fa-solid fa-info-circle text-[#348738]"></i> Về chúng tôi
                  </a>
                </li>
                <li>
                  <a className="flex items-center gap-2 text-gray-300 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                    <i className="fa-solid fa-envelope text-[#348738]"></i> Liên hệ
                  </a>
                </li>
              </ul>
            </div>
  
            {/* --- Dịch vụ --- */}
            <div>
              <h4 className="text-lg sm:text-xl lg:text-2xl font-bold mb-6 flex items-center gap-2">
                <i className="fa-solid fa-futbol text-[#348738]"></i>
                Dịch vụ
              </h4>
              <ul className="space-y-3 text-xs sm:text-sm lg:text-base">
                <li>⚽ Đặt sân bóng đá</li>
                <li>🏸 Đặt sân cầu lông</li>
                <li>🎾 Đặt sân tennis</li>
                <li>🏀 Đặt sân bóng rổ</li>
                <li>🤝 Trở thành đối tác</li>
              </ul>
            </div>
  
            {/* --- Liên hệ --- */}
            <div>
              <h4 className="text-lg sm:text-xl lg:text-2xl font-bold mb-6 flex items-center gap-2">
                <i className="fa-solid fa-phone text-[#348738]"></i>
                Liên hệ
              </h4>
              <ul className="space-y-3 text-xs sm:text-sm lg:text-base text-gray-300">
                <li className="flex gap-2">
                  <i className="fa-solid fa-location-dot text-[#348738] mt-1"></i>
                  123 Đường ABC, Quận 1, TP. Hồ Chí Minh
                </li>
                <li className="flex gap-2">
                  <i className="fa-solid fa-phone text-[#348738] mt-1"></i> 1900 xxxx
                </li>
                <li className="flex gap-2">
                  <i className="fa-solid fa-envelope text-[#348738] mt-1"></i> contact@bcp.vn
                </li>
              </ul>
            </div>
          </div>
  
          {/* --- Divider --- */}
          <div className="border-t border-gray-700/50 pt-8">
            <div className="flex flex-col md:flex-row justify-between items-center gap-4 text-xs sm:text-sm lg:text-base">
              <p className="text-gray-400">
                © 2025 <span className="text-white font-semibold">BCP Sports</span>. All rights reserved.
              </p>
              <div className="flex gap-4">
                <a className="hover:text-white transition">Điều khoản</a>
                <span>|</span>
                <a className="hover:text-white transition">Chính sách bảo mật</a>
                <span>|</span>
                <a className="hover:text-white transition">Cookies</a>
              </div>
            </div>
          </div>
        </div>
      </footer>
    )
  }
  export default Footer
  