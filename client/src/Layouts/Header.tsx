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
      if (mobileMenuRef.current && !mobileMenuRef.current.contains(target) && !target.parentElement?.closest('.mobile-menu-button')) {
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
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setIsPopUser(false);
    setIsOpen(false);
    showNotification('Đã đăng xuất', 'success')
    navigate('/');
  };

  const getNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses = "relative group px-4 py-2 rounded-lg hover:bg-white/10 transition-all duration-300 font-medium focus:outline-none";
    return isActive ? `${baseClasses} border-b-2 border-orange-500` : baseClasses;
  };

  const getMobileNavLinkClass = ({ isActive }: { isActive: boolean }) => {
    const baseClasses = "flex items-center gap-3 hover:text-green-200 transition-colors duration-300 p-2 rounded-lg hover:bg-white/10";
    return isActive ? `${baseClasses} bg-white/20 font-semibold` : baseClasses;
  }

  return (
    <header className="bg-gradient-to-r from-[#2d6a2d] to-[#348738] text-white shadow-xl sticky top-0 z-40">
      <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
      <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-400 via-orange-400 to-red-400"></div>

      <div className="container max-w-7xl mx-auto flex justify-between items-center px-4 py-3 relative z-10">
        <Link to="/" className="flex items-center space-x-3">
          <img src="/logo.png" alt="Logo" className="w-16 h-16 bg-white rounded-full shadow-lg p-1" />
          <div className="hidden sm:block">
            <h1 className="text-xl font-bold bg-gradient-to-r from-white to-green-200 bg-clip-text text-transparent">BCP Sports</h1>
            <p className="text-xs text-green-200">Đặt sân thể thao</p>
          </div>
        </Link>

        <nav className="hidden md:flex gap-8">
          <NavLink to="/" className={getNavLinkClass}>Trang Chủ</NavLink>
          <NavLink to="/partner" className={getNavLinkClass}>Dành cho đối tác</NavLink>
        </nav>

        <div className="hidden md:flex items-center gap-4">
          <div className="relative">
            <input
              type="text"
              placeholder="Tìm kiếm sân..."
              className="w-64 px-4 py-3 pl-10 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-300 bg-white/95 backdrop-blur-sm"
            />
            <i className="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>

          <div className="relative" ref={popoverRef}>
            {user ? (
              <button
                onClick={() => setIsPopUser(!isPopUser)}
                className="relative group rounded-full transition-all duration-300 w-12 h-12 flex items-center justify-center bg-white ring-2 ring-offset-2 ring-transparent hover:ring-orange-400"
              >
                <img src={user.avt || `https://ui-avatars.com/api/?name=${user.name.charAt(0)}&background=random&color=fff`} alt="Avatar" className="w-11 h-11 rounded-full object-cover" />
              </button>
            ) : (
              <Link to="/login" className="px-5 py-2.5 bg-orange-500 rounded-full hover:bg-orange-600 transition text-white font-medium shadow-md">
                Đăng nhập
              </Link>
            )}

            {isPopUser && user && (
              <div className="absolute right-0 mt-3 w-56 bg-gray-900 text-white shadow-xl rounded-lg border border-gray-700 z-50 overflow-hidden">
                <div className="px-4 py-3 border-b border-gray-700">
                  <p className="font-semibold text-white truncate">{user.name}</p>
                  <p className="text-xs text-gray-400 truncate">{user.email}</p>
                </div>
                <Link to={'/profile'} onClick={() => setIsPopUser(false)}>
                  <span className="w-full text-left px-4 py-2 hover:bg-gray-800 transition flex items-center gap-2 text-gray-200">
                    <i className="fa-solid fa-user w-4"></i> Tài khoản của tôi
                  </span>
                </Link>
                <button
                  className="w-full text-left px-4 py-2 hover:bg-gray-800 transition text-red-400 flex items-center gap-2"
                  onClick={handleLogout}
                >
                  <i className="fa-solid fa-right-from-bracket w-4"></i> Đăng xuất
                </button>
              </div>
            )}
          </div>
        </div>

        <button onClick={() => setIsOpen(!isOpen)} className="md:hidden text-2xl focus:outline-none p-2 rounded-lg text-white hover:bg-white/10 mobile-menu-button">
          <i className={`fa-solid ${isOpen ? "fa-xmark" : "fa-bars"}`}></i>
        </button>
      </div>

      <div
        className={`fixed inset-0 z-40 bg-black/30 backdrop-blur-sm transition-opacity duration-300 md:hidden ${isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onClick={() => setIsOpen(false)}
      ></div>
      <div
        ref={mobileMenuRef}
        className={`fixed top-0 right-0 z-50 w-72 h-full bg-gradient-to-b from-[#2d6a2d] to-[#348738] shadow-xl transition-transform transform duration-300 md:hidden ${isOpen ? 'translate-x-0' : 'translate-x-full'}`}
      >
        <div className="flex flex-col h-full">
          <div className="p-4 border-b border-white/20">
            {user ? (
              <div className="flex items-center gap-3">
                <img src={user.avt || `https://ui-avatars.com/api/?name=${user.name}&background=random&color=fff`} alt="Avatar" className="w-10 h-10 rounded-full object-cover bg-white" />
                <div>
                  <p className="font-semibold text-white">{user.name}</p>
                  <p className="text-xs text-green-200">{user.email}</p>
                </div>
              </div>
            ) : (
              <Link to="/login" onClick={() => setIsOpen(false)} className="w-full text-center px-4 py-2 bg-orange-500 rounded-lg hover:bg-orange-600 transition text-white font-medium">
                Đăng nhập / Đăng ký
              </Link>
            )}
          </div>

          <nav className="flex-grow p-4 space-y-2">
            <NavLink to="/" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-home w-5"></i>
              <span>Trang chủ</span>
            </NavLink>
            <NavLink to="/partner" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-handshake w-5"></i>
              <span>Dành cho đối tác</span>
            </NavLink>
            <NavLink to="/profile" className={getMobileNavLinkClass} onClick={() => setIsOpen(false)}>
              <i className="fa-solid fa-user w-5"></i>
              <span>Tài khoản của tôi</span>
            </NavLink>
          </nav>

          {user && (
            <div className="p-4 border-t border-white/20">
              <button
                onClick={handleLogout}
                className="w-full flex items-center gap-3 p-3 rounded-lg text-red-300 hover:bg-white/10 hover:text-red-400"
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
