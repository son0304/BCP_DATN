import { useState } from "react";
import logo from "../assets/logo.png"
const Header = () => {
  const [isOpen, setIsOpen] = useState(false);

  return (
    // Nền xanh lá chủ đạo
    <header className="bg-gradient-to-r from-[#2d6a2d] to-[#348738] text-white shadow-xl relative overflow-hidden">
      {/* Decorative elements */}
      <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
      <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400"></div>

      <div className="container max-w-7xl mx-auto flex justify-between items-center px-4 py-4 relative z-10">
        {/* Logo */}
        <div className="flex items-center space-x-3">
          <img src={logo} alt="Logo" className="w-16 h-16 rounded-full shadow-lg hover:scale-105 transition-transform duration-300" />
          <div className="hidden sm:block">
            <h1 className="text-xl font-bold bg-gradient-to-r from-white to-green-200 bg-clip-text text-transparent">
              BCP Sports
            </h1>
            <p className="text-xs text-green-200">Đặt sân thể thao</p>
          </div>
        </div>

        {/* Navigation - Desktop */}
        <nav className="hidden md:flex gap-8">
          <a href="/" className="relative group px-4 py-2 rounded-lg hover:bg-white/10 transition-all duration-300 font-medium">
            <span className="relative z-10">Trang Chủ</span>
            {/* Gradient xanh lá */}
            <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
          </a>
          <a href="#" className="relative group px-4 py-2 rounded-lg hover:bg-white/10 transition-all duration-300 font-medium">
            <span className="relative z-10">Dành cho đối tác</span>
            {/* Gradient xanh lá */}
            <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
          </a>
        </nav>

        {/* Search - Desktop */}
        <div className="hidden md:block">
          <div className="relative">
            <input
              type="text"
              placeholder="Tìm kiếm sân..."
              className="w-64 px-4 py-3 pl-10 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-300 focus:shadow-lg transition-all duration-300 bg-white/95 backdrop-blur-sm"
            />
            <i className="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>
        </div>

        {/* Account - Desktop */}
        <div className="hidden md:block">
          <div className="flex items-center space-x-4">
            <button className="relative group p-3 rounded-full hover:bg-white/10 transition-all duration-300">
              <i className="fa-regular fa-circle-user text-2xl group-hover:scale-110 transition-transform duration-300"></i>
              {/* Pulse màu xanh lá */}
              <div className="absolute -top-1 -right-1 w-3 h-3 bg-[#348738] rounded-full animate-pulse"></div>
            </button>
          </div>
        </div>

        {/* Mobile Menu Button */}
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="md:hidden text-3xl focus:outline-none p-2 rounded-lg hover:bg-white/10 transition-all duration-300"
        >
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"} transition-all duration-300`}></i>
        </button>
      </div>

      {/* Mobile Dropdown Menu */}
      {isOpen && (
        // Nền xanh lá
        <div className="md:hidden bg-gradient-to-b from-[#2d6a2d] to-[#348738] px-6 py-6 space-y-6 border-t border-white/20">
          <div className="space-y-4">
            <div className="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
              <h3 className="font-bold text-lg mb-3 text-white border-b border-white/30 pb-2">
                Danh mục
              </h3>
              <ul className="space-y-3">
                <li>
                  <a href="#" className="flex items-center gap-3 hover:text-green-200 transition-colors duration-300 p-2 rounded-lg hover:bg-white/10">
                    <i className="fa-solid fa-home w-5"></i>
                    <span>Trang chủ</span>
                  </a>
                </li>
                <li>
                  <a href="#" className="flex items-center gap-3 hover:text-green-200 transition-colors duration-300 p-2 rounded-lg hover:bg-white/10">
                    <i className="fa-solid fa-info-circle w-5"></i>
                    <span>Giới thiệu</span>
                  </a>
                </li>
              </ul>
            </div>

            <div className="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
              <h3 className="font-bold text-lg mb-3 text-white border-b border-white/30 pb-2">
                Tài khoản
              </h3>
              <ul className="space-y-3">
                <li className="flex items-center gap-3 hover:text-green-200 cursor-pointer p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                  <i className="fa-regular fa-circle-user w-5"></i>
                  <span>Username</span>
                </li>
                <li className="flex items-center gap-3 hover:text-green-200 cursor-pointer p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                  <i className="fa-solid fa-right-from-bracket w-5"></i>
                  <span>Đăng xuất</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      )}
    </header>
  );
};

export default Header;