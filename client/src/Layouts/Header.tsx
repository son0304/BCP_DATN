import { useState, useEffect, useRef } from "react";
import { Link, NavLink, useNavigate } from "react-router-dom";
import { useNotification } from "../Components/Notification";

type User = {
  id: number;
  name: string;
  email: string;
  avt?: string;
};

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

  useClickOutside(popoverRef as React.RefObject<HTMLDivElement>, () => setIsPopUser(false));
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      const target = event.target as Node;
      if (
        mobileMenuRef.current &&
        !mobileMenuRef.current.contains(target) &&
        !target.parentElement?.closest(".mobile-menu-button")
      ) {
        setIsOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [mobileMenuRef]);

  const navigate = useNavigate();

  const [user, setUser] = useState<User | null>(() => {
    try {
      const userStr = localStorage.getItem("user");
      return userStr ? JSON.parse(userStr) : null;
    } catch {
      localStorage.removeItem("user");
      return null;
    }
  });

  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setUser(null);
    setIsPopUser(false);
    setIsOpen(false);
    showNotification("Đã đăng xuất", "success");
    navigate("/");
  };

  const getNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses =
      "relative group px-4 py-2 rounded-lg hover:bg-green-50 transition-all duration-300 font-medium text-[#348738]";
    return isActive ? `${baseClasses} focus:bg-[#10B981] focus:text-white border-b-2 border-[#F59E0B]` : baseClasses;
  };

  const getMobileNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses =
      "flex items-center gap-3 hover:text-green-700 transition-colors duration-300 p-2 rounded-lg hover:bg-green-50 text-[#11182C]";
    return isActive ? `${baseClasses} bg-green-100 font-semibold` : baseClasses;
  };

  return (
    <header className="bg-[#FFFFFF] shadow-md sticky top-0 z-40">
      <div className="max-w-7xl mx-auto flex justify-between items-center px-4 py-3 relative z-10">
        {/* Logo */}
        <Link to="/" className="flex items-center space-x-3">
          <img src="/logo.png" alt="Logo" className="w-14 h-14 rounded-full shadow p-1 bg-white" />
          <div className="hidden sm:block">
            <h1 className="text-xl font-bold text-[#11182C]">BCP Sports</h1>
            <p className="text-xs text-[#10B981]">Đặt sân thể thao</p>
          </div>
        </Link>

        {/* Desktop Menu */}
        <nav className="hidden md:flex gap-8">
          <NavLink to="/" className={getNavLinkClass}>Trang chủ</NavLink>
          <NavLink to="/partner" className={getNavLinkClass}>Đối tác</NavLink>
          <NavLink to="/posts" className={getNavLinkClass}>Tin tức</NavLink>
          <NavLink to="/contacts" className={getNavLinkClass}>Liên hệ</NavLink>
          <NavLink to="/tournaments" className={getNavLinkClass}>Giải đấu</NavLink>
        </nav>

        {/* User or Auth */}
        <div className="hidden md:flex items-center gap-4" ref={popoverRef}>
          {user ? (
            <>
              <button
                onClick={() => setIsPopUser(!isPopUser)}
                className="w-11 h-11 rounded-full bg-[#10B981] hover:bg-[#059669] transition flex items-center justify-center"
              >
                <img
                  src={user.avt || `https://ui-avatars.com/api/?name=${user.name}&background=10B981&color=fff`}
                  alt="Avatar"
                  className="w-10 h-10 rounded-full object-cover"
                />
              </button>
              {isPopUser && (
                <div className="absolute right-0 mt-3 w-56 bg-white rounded-lg shadow-lg border border-[#E5E7EB] overflow-hidden z-50">
                  <div className="px-4 py-3 border-b border-[#E5E7EB]">
                    <p className="font-semibold text-[#11182C]">{user.name}</p>
                    <p className="text-xs text-[#6B7280]">{user.email}</p>
                  </div>
                  <Link to="/profile" onClick={() => setIsPopUser(false)} className="block px-4 py-2 hover:bg-[#F9FAFB] text-[#4B5563]">
                    <i className="fa-solid fa-user mr-2 text-[#10B981]" /> Tài khoản của tôi
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="w-full text-left px-4 py-2 text-[#EF4444] hover:bg-[#F9FAFB]"
                  >
                    <i className="fa-solid fa-right-from-bracket mr-2" /> Đăng xuất
                  </button>
                </div>
              )}
            </>
          ) : (
            <div className="flex gap-3">
              <Link
                to="/register"
                className="px-5 py-2.5 bg-[#10B981] text-white rounded-full hover:bg-[#059669] font-medium shadow"
              >
                Đăng ký
              </Link>
              <Link
                to="/login"
                className="px-5 py-2.5 border border-[#10B981] text-[#10B981] rounded-full hover:bg-[#ECFDF5] font-medium"
              >
                Đăng nhập
              </Link>
            </div>
          )}
        </div>

        {/* Mobile Menu Button */}
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="md:hidden text-2xl p-2 rounded-lg text-[#10B981] hover:bg-[#F9FAFB] mobile-menu-button"
        >
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"}`}></i>
        </button>
      </div>

      {/* Overlay */}
      <div
        onClick={() => setIsOpen(false)}
        className={`fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity duration-300 md:hidden ${isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
          }`}
      ></div>

      {/* Mobile Menu */}
      <div
        ref={mobileMenuRef}
        className={`fixed top-0 right-0 z-50 w-72 h-full bg-white shadow-xl transition-transform duration-300 md:hidden ${isOpen ? "translate-x-0" : "translate-x-full"
          }`}
      >
        <div className="flex flex-col h-full">
          {/* Header User Info */}
          <div className="p-4 border-b border-[#E5E7EB]">
            {user ? (
              <div className="flex items-center gap-3">
                <img
                  src={user.avt || `https://ui-avatars.com/api/?name=${user.name}&background=10B981&color=fff`}
                  alt="Avatar"
                  className="w-10 h-10 rounded-full object-cover"
                />
                <div>
                  <p className="font-semibold text-[#11182C]">{user.name}</p>
                  <p className="text-xs text-[#6B7280]">{user.email}</p>
                </div>
              </div>
            ) : (
              <div className="flex flex-col gap-2">
                <Link
                  to="/register"
                  onClick={() => setIsOpen(false)}
                  className="px-4 py-2 bg-[#10B981] text-white rounded-lg hover:bg-[#059669] text-center font-medium"
                >
                  Đăng ký
                </Link>
                <Link
                  to="/login"
                  onClick={() => setIsOpen(false)}
                  className="px-4 py-2 border border-[#10B981] text-[#10B981] rounded-lg hover:bg-[#ECFDF5] text-center font-medium"
                >
                  Đăng nhập
                </Link>
              </div>
            )}
          </div>

          {/* Navigation */}
          <nav className="flex-grow p-4 space-y-2">
            <NavLink to="/" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-home w-5 text-[#10B981]"></i> Trang chủ
            </NavLink>
            <NavLink to="/partner" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-handshake w-5 text-[#10B981]"></i> Đối tác
            </NavLink>
            <NavLink to="/posts" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-newspaper w-5 text-[#10B981]"></i> Tin tức
            </NavLink>
            <NavLink to="/contacts" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-envelope w-5 text-[#10B981]"></i> Liên hệ
            </NavLink>
            <NavLink to="/tournaments" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-trophy w-5 text-[#F59E0B]"></i> Giải đấu
            </NavLink>
            {user && (
              <NavLink to="/profile" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
                <i className="fa-solid fa-user w-5 text-[#10B981]"></i> Tài khoản của tôi
              </NavLink>
            )}
          </nav>

          {/* Logout */}
          {user && (
            <div className="p-4 border-t border-[#E5E7EB]">
              <button
                onClick={handleLogout}
                className="w-full flex items-center gap-3 p-3 rounded-lg text-[#EF4444] hover:bg-[#F9FAFB]"
              >
                <i className="fa-solid fa-right-from-bracket w-5"></i> Đăng xuất
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

export default Header;
