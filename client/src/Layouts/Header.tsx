import { useState } from "react";
import { Link, NavLink } from "react-router-dom"; // BƯỚC 1: Import NavLink
import logo from "../../public/logo.png";
import UserPopover from "../Pages/User/UserPopover";

const Header = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [isPopUser, setIsPopUser] = useState(false)

  const getNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses = "relative group px-4 py-2 rounded-lg hover:bg-white/10 transition-all duration-300 font-medium focus:outline-none";

    // Nếu link đang active, thêm class viền cam vào
    if (isActive) {
      return `${baseClasses} border-b-2 border-orange-500`;
    }

    // Nếu không, trả về class cơ bản
    return baseClasses;
  };

  const getMobileNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses = "flex items-center gap-3 hover:text-green-200 transition-colors duration-300 p-2 rounded-lg hover:bg-white/10";
    if (isActive) {
      // Style khác cho mobile khi active, ví dụ nền đậm hơn
      return `${baseClasses} bg-white/20 font-semibold`;
    }
    return baseClasses;
  }

  return (
    <header className="bg-gradient-to-r from-[#2d6a2d] to-[#348738] text-white shadow-xl relative ">
      {/* ... Các element trang trí giữ nguyên ... */}
      <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
      <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400"></div>

      <div className="container max-w-7xl mx-auto flex justify-between items-center px-4 py-4 relative z-10">
        {/* Logo */}
        <div className="flex items-center space-x-3">
          <img src={logo} alt="Logo" className="w-16 h-16 rounded-full shadow-lg hover:scale-105 transition-transform duration-300" />
          <div className="hidden sm:block">
            <h1 className="text-xl font-bold bg-gradient-to-r from-white to-green-200 bg-clip-text text-transparent">BCP Sports</h1>
            <p className="text-xs text-green-200">Đặt sân thể thao</p>
          </div>
        </div>

        {/* Navigation - Desktop */}
        <nav className="hidden md:flex gap-8">
          {/* BƯỚC 3: Thay <a> bằng <NavLink> và dùng hàm getNavLinkClass */}
          <NavLink to="/" className={getNavLinkClass}>
            <span className="relative z-10">Trang Chủ</span>
            <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
          </NavLink>

          <NavLink to="/partner" className={getNavLinkClass}>
            <span className="relative z-10">Dành cho đối tác</span>
            <div className="absolute inset-0 bg-gradient-to-r from-[#348738] to-[#2d6a2d] rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
          </NavLink>
        </nav>

        {/* ... Search và Account giữ nguyên ... */}
        <div className="hidden md:block">
          <div className="relative">
            <input type="text" placeholder="Tìm kiếm sân..." className="w-64 px-4 py-3 pl-10 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-300 focus:shadow-lg transition-all duration-300 bg-white/95 backdrop-blur-sm" />
            <i className="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>
        </div>
        <div className="hidden md:block relative">
          <button
            onClick={() => setIsPopUser(!isPopUser)}
            className="relative group rounded-full hover:opacity-80 transition-opacity duration-300"
          >
            <img src="https://picsum.photos/400?random=9852" alt="Avatar" className="w-10 h-10 rounded-full object-cover" />
          </button>

          {isPopUser && (
            <div className="absolute right-0 mt-2 w-48 bg-gray-900 text-white shadow-lg rounded border border-gray-700 z-50">
              <div className="px-4 py-2 font-medium border-b border-gray-700">
                Username
              </div>
              <Link to={'edit_profile'}>
                <button
                  className="w-full text-left px-4 py-2 hover:bg-gray-800 transition"
                  onClick={() => console.log('Edit Profile')}
                >
                  Edit Profile
                </button>
              </Link>
              <button
                className="w-full text-left px-4 py-2 hover:bg-gray-800 transition text-red-400"
                onClick={() => console.log('Logout')}
              >
                Logout
              </button>
            </div>
          )}
        </div>

        {/* Mobile Menu Button */}
        <button onClick={() => setIsOpen(!isOpen)} className="md:hidden text-3xl focus:outline-none p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"} transition-all duration-300`}></i>
        </button>
      </div>

      {/* Mobile Dropdown Menu */}
      {isOpen && (
        <div className="md:hidden bg-gradient-to-b from-[#2d6a2d] to-[#348738] px-6 py-6 space-y-6 border-t border-white/20">
          <div className="space-y-4">
            <div className="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
              <h3 className="font-bold text-lg mb-3 text-white border-b border-white/30 pb-2">Danh mục</h3>
              <ul className="space-y-3">
                <li>
                  {/* Áp dụng NavLink cho mobile */}
                  <NavLink to="/" className={getMobileNavLinkClass}>
                    <i className="fa-solid fa-home w-5"></i>
                    <span>Trang chủ</span>
                  </NavLink>
                </li>
                <li>
                  <NavLink to="/partner" className={getMobileNavLinkClass}>
                    <i className="fa-solid fa-handshake"></i>
                    <span>Dành cho đối tác</span>
                  </NavLink>
                </li>
              </ul>
            </div>
            {/* ... Phần tài khoản giữ nguyên ... */}
            <div className="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
              <h3 className="font-bold text-lg mb-3 text-white border-b border-white/30 pb-2">Tài khoản</h3>
              <ul className="space-y-3">
                <li className="flex items-center gap-3 hover:text-green-200 cursor-pointer p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
                  <img src="https://picsum.photos/400?random=9852" alt="Avatar" className="w-10 h-10 rounded-full object-cover" />
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
