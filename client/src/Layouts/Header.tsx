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
    return isActive ? `${baseClasses} border-b-2 border-[#348738]` : baseClasses;
  };

  const getMobileNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses =
      "flex items-center gap-3 hover:text-green-700 transition-colors duration-300 p-2 rounded-lg hover:bg-green-50 text-[#348738]";
    return isActive ? `${baseClasses} bg-green-100 font-semibold` : baseClasses;
  };

  return (
    <header className="bg-white text-[#348738] shadow-md sticky top-0 z-40">
      {/* Header container */}
      <div className="container max-w-7xl mx-auto flex justify-between items-center px-4 py-3 relative z-10">
        {/* Logo */}
        <Link to="/" className="flex items-center space-x-3">
          <img
            src="/logo.png"
            alt="Logo"
            className="w-16 h-16 bg-white rounded-full shadow-lg p-1"
          />
          <div className="hidden sm:block">
            <h1 className="text-xl font-bold text-[#2d6a2d]">BCP Sports</h1>
            <p className="text-xs text-[#348738]">Đặt sân thể thao</p>
          </div>
        </Link>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex gap-8">
          <NavLink to="/venues" className={getNavLinkClass}>
            Tìm kiếm sân
          </NavLink>
          <NavLink to="/partner" className={getNavLinkClass}>
            Dành cho đối tác
          </NavLink>
          <NavLink to="/posts" className={getNavLinkClass}>
            Tin tức
          </NavLink>
          <NavLink to="/contacts" className={getNavLinkClass}>
            Liên hệ
          </NavLink>
          <NavLink to="/posts" className={getNavLinkClass}>
            Giải đấu
          </NavLink>
        </nav>

        {/* User or Auth buttons */}
        <div className="hidden md:flex items-center gap-4">
          <div className="relative" ref={popoverRef}>
            {user ? (
              <button
                onClick={() => setIsPopUser(!isPopUser)}
                className="relative group rounded-full transition-all duration-300 w-12 h-12 flex items-center justify-center bg-[#348738] text-white ring-2 ring-transparent hover:ring-green-400"
              >
                <img
                  src={
                    user.avt ||
                    `https://ui-avatars.com/api/?name=${user.name.charAt(
                      0
                    )}&background=random&color=fff`
                  }
                  alt="Avatar"
                  className="w-11 h-11 rounded-full object-cover"
                />
              </button>
            ) : (
              <div className="flex items-center gap-3">
                <Link
                  to="/register"
                  className="px-5 py-2.5 bg-[#2d6a2d] rounded-full hover:bg-[#276127] transition text-white font-medium shadow-md"
                >
                  Đăng ký
                </Link>
                <Link
                  to="/login"
                  className="px-5 py-2.5 bg-[#348738] rounded-full hover:bg-[#2d6a2d] transition text-white font-medium shadow-md"
                >
                  Đăng nhập
                </Link>
              </div>
            )}

            {/* Popover user menu */}
            {isPopUser && user && (
              <div className="absolute right-0 mt-3 w-56 bg-white text-[#348738] shadow-xl rounded-lg border border-gray-200 z-50 overflow-hidden">
                <div className="px-4 py-3 border-b border-gray-200">
                  <p className="font-semibold truncate">{user.name}</p>
                  <p className="text-xs text-gray-500 truncate">{user.email}</p>
                </div>
                <Link to={"/profile"} onClick={() => setIsPopUser(false)}>
                  <span className="w-full text-left px-4 py-2 hover:bg-green-50 transition flex items-center gap-2">
                    <i className="fa-solid fa-user w-4"></i> Tài khoản của tôi
                  </span>
                </Link>
                <button
                  className="w-full text-left px-4 py-2 hover:bg-green-50 transition flex items-center gap-2 text-red-500"
                  onClick={handleLogout}
                >
                  <i className="fa-solid fa-right-from-bracket w-4"></i> Đăng xuất
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Mobile menu button */}
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="md:hidden text-2xl focus:outline-none p-2 rounded-lg text-[#348738] hover:bg-green-50 mobile-menu-button"
        >
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"}`}></i>
        </button>
      </div>

      {/* Overlay */}
      <div
        className={`fixed inset-0 z-40 bg-black/30 backdrop-blur-sm transition-opacity duration-300 md:hidden ${isOpen ? "opacity-100" : "opacity-0 pointer-events-none"
          }`}
        onClick={() => setIsOpen(false)}
      ></div>

      {/* Mobile Menu */}
      <div
        ref={mobileMenuRef}
        className={`fixed top-0 right-0 z-50 w-72 h-full bg-white text-[#348738] shadow-xl transition-transform transform duration-300 md:hidden ${isOpen ? "translate-x-0" : "translate-x-full"
          }`}
      >
        <div className="flex flex-col h-full">
          <div className="p-4 border-b border-gray-200">
            {user ? (
              <div className="flex items-center gap-3">
                <img
                  src={
                    user.avt ||
                    `https://ui-avatars.com/api/?name=${user.name}&background=random&color=fff`
                  }
                  alt="Avatar"
                  className="w-10 h-10 rounded-full object-cover bg-white border"
                />
                <div>
                  <p className="font-semibold">{user.name}</p>
                  <p className="text-xs text-gray-500">{user.email}</p>
                </div>
              </div>
            ) : (
              <div className="flex flex-col gap-2">
                <Link
                  to="/register"
                  onClick={() => setIsOpen(false)}
                  className="w-full text-center px-4 py-2 bg-[#2d6a2d] rounded-lg hover:bg-[#276127] transition text-white font-medium"
                >
                  Đăng ký
                </Link>
                <Link
                  to="/login"
                  onClick={() => setIsOpen(false)}
                  className="w-full text-center px-4 py-2 bg-[#348738] rounded-lg hover:bg-[#2d6a2d] transition text-white font-medium"
                >
                  Đăng nhập
                </Link>
              </div>
            )}
          </div>

          <nav className="flex-grow p-4 space-y-2">
            <NavLink
              to="/"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-home w-5"></i>
              <span>Tìm kiếm sân</span>
            </NavLink>
            <NavLink
              to="/partner"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-handshake w-5"></i>
              <span>Dành cho đối tác</span>
            </NavLink>
            <NavLink
              to="/#"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-newspaper w-5"></i>
              <span>Tin tức</span>
            </NavLink>
            <NavLink
              to="/#"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-envelope w-5"></i>
              <span>Liên hệ</span>
            </NavLink>
            <NavLink
              to="/#"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-envelope w-5"></i>
              <span>Giải đấu</span>
            </NavLink>
            <NavLink
              to="/profile"
              className={getMobileNavLinkClass}
              onClick={() => setIsOpen(false)}
            >
              <i className="fa-solid fa-user w-5"></i>
              <span>Tài khoản của tôi</span>
            </NavLink>
          </nav>

          {user && (
            <div className="p-4 border-t border-gray-200">
              <button
                onClick={handleLogout}
                className="w-full flex items-center gap-3 p-3 rounded-lg text-red-500 hover:bg-green-50 hover:text-red-600"
              >
                <i className="fa-solid fa-right-from-bracket w-5"></i>
                <span>Đăng xuất</span>
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

export default Header;
