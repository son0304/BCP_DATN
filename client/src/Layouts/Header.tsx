import { useState, useEffect, useRef, useMemo } from "react";
import { Link, NavLink, useNavigate } from "react-router-dom";
import { useNotification } from "../Components/Notification";

// 1. Cập nhật Type linh hoạt hơn để tránh lỗi
type User = {
  id: number;
  name: string;
  email: string;
  // avt có thể là chuỗi (URL), mảng ảnh, hoặc null
  avt?: string | { url?: string; avt?: string }[] | null; 
};

// Hook detect click outside
const useClickOutside = (ref: React.RefObject<HTMLDivElement>, callback: () => void) => {
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (ref.current && !ref.current.contains(event.target as Node)) {
        callback();
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [ref, callback]);
};

const Header = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [isPopUser, setIsPopUser] = useState(false);
  const { showNotification } = useNotification();
  const popoverRef = useRef<HTMLDivElement>(null);
  const mobileMenuRef = useRef<HTMLDivElement>(null);
  const navigate = useNavigate();

  useClickOutside(popoverRef as React.RefObject<HTMLDivElement>, () => setIsPopUser(false));

  // Lấy user từ LocalStorage an toàn hơn
  const [user, setUser] = useState<User | null>(() => {
    try {
      const u = localStorage.getItem("user");
      return u ? JSON.parse(u) : null;
    } catch {
      localStorage.removeItem("user");
      return null;
    }
  });

  // --- LOGIC LẤY URL ẢNH ĐẠI DIỆN ---
  const avatarUrl = useMemo(() => {
    if (!user || !user.avt) return null;

    // Trường hợp 1: API trả về chuỗi URL trực tiếp (Ví dụ: "http://...")
    if (typeof user.avt === 'string') {
        return user.avt;
    }

    // Trường hợp 2: API trả về mảng quan hệ (Ví dụ: [{url: "..."}])
    if (Array.isArray(user.avt) && user.avt.length > 0) {
        // Lấy phần tử đầu tiên, ưu tiên key 'url' hoặc 'avt'
        const firstImg = user.avt[0];
        return firstImg.url || firstImg.avt || null;
    }

    return null;
  }, [user]);

  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setUser(null);
    setIsPopUser(false);
    setIsOpen(false);
    showNotification("Đã đăng xuất", "success");
    navigate("/");
  };

  const getNavLinkClass = ({ isActive }: { isActive: boolean }) =>
    `relative text-sm md:text-base font-medium transition-all duration-200 py-1 ${isActive
      ? "text-[#10B981] font-semibold after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#10B981]"
      : "text-gray-600 hover:text-[#10B981]"
    }`;

  const getMobileNavLinkClass = ({ isActive }: { isActive: boolean }) =>
    `flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-colors duration-200 ${isActive ? "bg-green-50 text-[#10B981] font-semibold" : "text-gray-600 hover:bg-gray-50"
    }`;

  // Component hiển thị Avatar (Tái sử dụng)
  const AvatarDisplay = ({ className = "w-9 h-9" }) => {
    if (avatarUrl) {
      return (
        <img
          src={avatarUrl}
          alt="Avatar"
          className={`${className} rounded-full object-cover border border-gray-200`}
          onError={(e) => {
            // Fallback nếu ảnh lỗi
            e.currentTarget.src = "https://ui-avatars.com/api/?name=" + user?.name + "&background=random";
          }}
        />
      );
    }
    // Fallback: Hiển thị chữ cái đầu tên nếu không có ảnh
    return (
      <div className={`${className} rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold border border-emerald-200`}>
        {user?.name?.charAt(0).toUpperCase() || "U"}
      </div>
    );
  };

  return (
    <header className="bg-white border-b border-gray-100 sticky top-0 z-40 h-16 md:h-20 transition-all duration-300">
      <div className="max-w-7xl mx-auto flex justify-between items-center px-4 md:px-6 h-full relative z-10">

        {/* LOGO */}
        <Link to="/" className="flex items-center gap-2 md:gap-3 group">
          <img
            src="/logo.png"
            alt="Logo"
            className="w-8 h-8 md:w-11 md:h-11 rounded-full object-cover shadow-sm group-hover:rotate-12 transition-transform duration-300"
          />
          <div className="hidden sm:block">
            <h1 className="text-lg md:text-2xl font-bold text-[#11182C] leading-none tracking-tight">BCP Sports</h1>
            <p className="text-[10px] md:text-xs text-[#10B981] font-bold tracking-widest uppercase mt-0.5">Đặt sân nhanh</p>
          </div>
        </Link>

        {/* DESKTOP NAV */}
        <nav className="hidden md:flex items-center gap-8 lg:gap-10">
          <NavLink to="/" className={getNavLinkClass}>Trang chủ</NavLink>
          <NavLink to="/partner" className={getNavLinkClass}>Đối tác</NavLink>
          <NavLink to="/map" className={getNavLinkClass}>Bản Đồ</NavLink>
          <NavLink to="/blog" className={getNavLinkClass}>Tin tức</NavLink>
          <NavLink to="/contacts" className={getNavLinkClass}>Liên hệ</NavLink>
          <NavLink to="/posts" className={getNavLinkClass}>Cộng đồng BCP</NavLink>
        </nav>

        {/* USER DROPDOWN */}
        <div className="hidden md:flex items-center gap-3 relative" ref={popoverRef}>
          {user ? (
            <div className="relative">
              <button
                onClick={() => setIsPopUser(!isPopUser)}
                className="flex items-center gap-2 hover:bg-gray-50 pl-1 pr-3 py-1 rounded-full transition border border-transparent hover:border-gray-200"
              >
                {/* Hiển thị Avatar đã xử lý */}
                <AvatarDisplay />

                <div className="text-left hidden lg:block">
                  <span className="block text-sm font-semibold text-gray-700 max-w-[120px] truncate">{user.name}</span>
                </div>
                <i className={`fa-solid fa-chevron-down text-xs text-gray-400 transition-transform ${isPopUser ? 'rotate-180' : ''}`}></i>
              </button>

              {isPopUser && (
                <div className="absolute top-full right-0 mt-3 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50 animate-fade-in-down">
                  <div className="px-5 py-4 border-b border-gray-50 bg-gray-50/50">
                    <p className="text-sm font-bold text-gray-800 truncate">{user.name}</p>
                    <p className="text-xs text-gray-500 truncate">{user.email}</p>
                  </div>
                  <Link
                    to="/profile"
                    onClick={() => setIsPopUser(false)}
                    className="flex items-center px-5 py-3 text-sm text-gray-600 hover:bg-green-50 hover:text-[#10B981] transition"
                  >
                    <i className="fa-solid fa-user mr-3 w-4" /> Tài khoản
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="w-full flex items-center text-left px-5 py-3 text-sm text-red-500 hover:bg-red-50 transition"
                  >
                    <i className="fa-solid fa-right-from-bracket mr-3 w-4" /> Đăng xuất
                  </button>
                </div>
              )}
            </div>
          ) : (
            <div className="flex gap-3">
              <Link to="/login" className="px-5 py-2 rounded-full text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">Đăng nhập</Link>
              <Link to="/register" className="px-5 py-2 bg-[#10B981] text-white text-sm font-semibold rounded-full hover:bg-[#059669] shadow-lg shadow-green-200 transition transform active:scale-95">Đăng ký</Link>
            </div>
          )}
        </div>

        {/* MOBILE TOGGLE */}
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="md:hidden text-gray-600 hover:text-[#10B981] p-2 -mr-2 mobile-menu-button active:scale-90 transition-transform"
        >
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"} text-2xl`}></i>
        </button>
      </div>

      {/* MOBILE DRAWER OVERLAY */}
      <div
        onClick={() => setIsOpen(false)}
        className={`fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300 md:hidden h-screen z-40 ${isOpen ? "opacity-100" : "opacity-0 pointer-events-none"}`}
      />

      {/* MOBILE DRAWER CONTENT */}
      <div
        ref={mobileMenuRef}
        className={`fixed top-0 right-0 z-50 w-[80%] max-w-[300px] h-full bg-white shadow-2xl transition-transform duration-300 md:hidden ${isOpen ? "translate-x-0" : "translate-x-full"}`}
      >
        <div className="flex flex-col h-full">
          <div className="p-5 border-b border-gray-100 bg-gray-50/80">
            {user ? (
              <div className="flex items-center gap-3">
                <AvatarDisplay className="w-10 h-10" />
                <div className="overflow-hidden">
                  <p className="text-sm font-bold text-gray-800 truncate">{user.name}</p>
                  <p className="text-xs text-gray-500 truncate">{user.email}</p>
                </div>
              </div>
            ) : (
              <div className="flex flex-col gap-3">
                <p className="text-sm font-medium text-gray-500 text-center">Chào mừng đến với BCP Sports</p>
                <div className="grid grid-cols-2 gap-3">
                  <Link to="/login" onClick={() => setIsOpen(false)} className="px-3 py-2.5 text-xs font-bold border border-gray-200 text-gray-600 rounded-lg text-center hover:bg-white transition">Đăng nhập</Link>
                  <Link to="/register" onClick={() => setIsOpen(false)} className="px-3 py-2.5 text-xs font-bold bg-[#10B981] text-white rounded-lg text-center hover:bg-[#059669] transition">Đăng ký</Link>
                </div>
              </div>
            )}
          </div>

          <nav className="flex-grow p-4 space-y-1 overflow-y-auto">
             {/* ... Các Link giữ nguyên ... */}
             <NavLink to="/" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-home w-6 text-center text-[#10B981]"></i> Trang chủ
            </NavLink>
            <NavLink to="/partner" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-handshake w-6 text-center text-[#10B981]"></i> Đối tác
            </NavLink>
            <NavLink to="/blog" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-newspaper w-6 text-center text-[#10B981]"></i> Tin tức
            </NavLink>
            <NavLink to="/contacts" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-envelope w-6 text-center text-[#10B981]"></i> Liên hệ
            </NavLink>
            <NavLink to="/tournaments" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-trophy w-6 text-center text-[#F59E0B]"></i> Giải đấu
            </NavLink>
            {user && (
              <div className="border-t border-gray-100 my-3 pt-3">
                 <NavLink to="/profile" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
                    <i className="fa-solid fa-user-gear w-6 text-center text-gray-400"></i> Quản lý tài khoản
                  </NavLink>
              </div>
            )}
          </nav>

          {user && (
            <div className="p-4 border-t border-gray-100">
              <button onClick={handleLogout} className="w-full flex items-center justify-center gap-2 p-3 rounded-lg text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 transition">
                <i className="fa-solid fa-power-off"></i> Đăng xuất
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

export default Header;