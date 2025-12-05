const Footer = () => {
  return (
    <footer className="bg-[#111827] text-gray-400 relative overflow-hidden border-t border-[#1F2937]">
      {/* --- Hiệu ứng nền --- */}
      <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-5"></div>
      
      {/* Padding: Mobile py-10, Desktop py-16 */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16 relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12 mb-10">
          
          {/* --- Cột 1: Brand --- */}
          <div className="space-y-4 md:space-y-6">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 md:w-10 md:h-10 bg-[#10B981] rounded-xl flex items-center justify-center text-white shadow-lg shadow-green-900/20">
                <i className="fa-solid fa-futbol text-base md:text-lg"></i>
              </div>
              <h3 className="text-xl md:text-2xl font-bold text-white tracking-tight">
                BCP Sports
              </h3>
            </div>
            {/* Mobile text-xs, Desktop text-sm */}
            <p className="text-xs md:text-sm leading-relaxed text-gray-500">
              Nền tảng đặt sân thể thao thông minh số 1 Việt Nam. Kết nối đam mê, nâng tầm trải nghiệm thể thao của bạn.
            </p>
            <div className="flex gap-3">
               {['facebook', 'instagram', 'tiktok', 'youtube'].map(icon => (
                 <a key={icon} href="#" className="w-8 h-8 md:w-9 md:h-9 rounded bg-gray-800 flex items-center justify-center hover:bg-[#10B981] hover:text-white transition-all duration-300 group">
                    <i className={`fa-brands fa-${icon} text-xs md:text-sm group-hover:scale-110 transition-transform`}></i>
                 </a>
               ))}
            </div>
          </div>

          {/* --- Cột 2: Quick Links --- */}
          <div>
            {/* Header: Desktop text-lg */}
            <h4 className="text-white font-bold mb-4 md:mb-6 text-sm md:text-base uppercase tracking-wider">Khám phá</h4>
            <ul className="space-y-2 md:space-y-3 text-xs md:text-sm">
              <li><a href="#" className="hover:text-[#10B981] transition flex items-center gap-2"><i className="fa-solid fa-angle-right text-[10px] opacity-50"></i> Trang chủ</a></li>
              <li><a href="#" className="hover:text-[#10B981] transition flex items-center gap-2"><i className="fa-solid fa-angle-right text-[10px] opacity-50"></i> Về chúng tôi</a></li>
              <li><a href="#" className="hover:text-[#10B981] transition flex items-center gap-2"><i className="fa-solid fa-angle-right text-[10px] opacity-50"></i> Tìm sân nhanh</a></li>
              <li><a href="#" className="hover:text-[#10B981] transition flex items-center gap-2"><i className="fa-solid fa-angle-right text-[10px] opacity-50"></i> Tin tức thể thao</a></li>
            </ul>
          </div>

          {/* --- Cột 3: Services --- */}
          <div>
            <h4 className="text-white font-bold mb-4 md:mb-6 text-sm md:text-base uppercase tracking-wider">Dịch vụ</h4>
            <ul className="space-y-2 md:space-y-3 text-xs md:text-sm">
              <li className="flex items-center gap-2.5 hover:text-white transition cursor-pointer"><span className="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Đặt sân Bóng đá</li>
              <li className="flex items-center gap-2.5 hover:text-white transition cursor-pointer"><span className="w-1.5 h-1.5 rounded-full bg-green-500"></span> Đặt sân Cầu lông</li>
              <li className="flex items-center gap-2.5 hover:text-white transition cursor-pointer"><span className="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Đặt sân Tennis</li>
              <li className="flex items-center gap-2.5 hover:text-white transition cursor-pointer"><span className="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Đối tác sân bãi</li>
            </ul>
          </div>

          {/* --- Cột 4: Contact --- */}
          <div>
            <h4 className="text-white font-bold mb-4 md:mb-6 text-sm md:text-base uppercase tracking-wider">Liên hệ</h4>
            <ul className="space-y-3 md:space-y-4 text-xs md:text-sm">
              <li className="flex gap-3 items-start">
                <i className="fa-solid fa-location-dot text-[#10B981] mt-1 text-sm"></i>
                <span className="leading-snug">123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
              </li>
              <li className="flex gap-3 items-center">
                <i className="fa-solid fa-phone text-[#10B981] text-sm"></i>
                <span className="font-mono text-gray-300 text-sm md:text-base">1900 123 456</span>
              </li>
              <li className="flex gap-3 items-center">
                <i className="fa-solid fa-envelope text-[#10B981] text-sm"></i>
                <span>support@bcp.vn</span>
              </li>
            </ul>
          </div>
        </div>

        {/* --- Divider & Copyright --- */}
        <div className="border-t border-gray-800/80 pt-6 md:pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs md:text-sm">
          <p className="text-gray-500 text-center md:text-left">
            © 2025 BCP Sports. Bản quyền thuộc về BCP Team.
          </p>
          <div className="flex gap-6 text-gray-500">
            <a href="#" className="hover:text-white transition">Điều khoản</a>
            <a href="#" className="hover:text-white transition">Bảo mật</a>
            <a href="#" className="hover:text-white transition">Cookies</a>
          </div>
        </div>
      </div>
    </footer>
  );
};
export default Footer;