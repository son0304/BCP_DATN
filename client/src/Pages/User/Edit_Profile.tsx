import React, { useEffect, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import type { User } from "../../Types/user";

export const Edit_Profile = () => {
  const navigate = useNavigate();
  const location = useLocation();

  // --- State Data ---
  const [user, setUser] = useState<User | null>(null);

  // Form States
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [province, setProvince] = useState('');
  const [district, setDistrict] = useState('');

  // Image States
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  // UI States
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  // 1. Load dữ liệu
  useEffect(() => {
    let currentUser = location.state?.user;

    if (!currentUser) {
      const stored = localStorage.getItem("user");
      if (stored) currentUser = JSON.parse(stored);
    }

    if (currentUser) {
      setUser(currentUser);
      setName(currentUser.name || '');
      setPhone(currentUser.phone || '');

      // Xử lý dữ liệu địa chỉ (nếu là object hoặc string)
      const pName = typeof currentUser.province === 'object' ? currentUser.province?.name : currentUser.province;
      const dName = typeof currentUser.district === 'object' ? currentUser.district?.name : currentUser.district;

      setProvince(pName || '');
      setDistrict(dName || '');
    } else {
      navigate('/login');
    }
  }, [location, navigate]);

  // 2. Xử lý khi chọn ảnh
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setErrorMsg("Ảnh quá lớn! Vui lòng chọn ảnh dưới 5MB.");
        return;
      }
      setSelectedFile(file);
      setPreviewUrl(URL.createObjectURL(file));
      setErrorMsg(null);
    }
  };

  // 3. Xử lý Submit Form
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setErrorMsg(null);

    try {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('phone', phone);
      formData.append('province', province);
      formData.append('district', district);
      if (selectedFile) {
        formData.append('avt', selectedFile);
      }
      formData.append('_method', 'PUT'); // Hỗ trợ một số backend Laravel/PHP

      // --- CALL API HERE ---
      // await axios.post(`/api/users/${user.id}`, formData);

      // --- GIẢ LẬP ---
      await new Promise(r => setTimeout(r, 1000));

      // Cập nhật LocalStorage
      if (user) {
        const newUser = {
          ...user,
          name, phone, province, district,
          avt: previewUrl ? [{ id: Date.now(), url: previewUrl }] : user.avt
        };
        localStorage.setItem('user', JSON.stringify(newUser));
      }
      console.log(formData);

      // navigate('/profile');

    } catch (err) {
      setErrorMsg("Đã xảy ra lỗi, vui lòng thử lại sau.");
    } finally {
      setIsLoading(false);
    }
  };

  if (!user) return null;

  const currentAvatar = previewUrl || (user.avt && user.avt.length > 0 ? user.avt[0].url : "/default-avatar.png");

  return (
    <div className="min-h-screen bg-gray-100 flex items-center justify-center p-4 font-sans">

      {/* CARD CONTAINER */}
      <div className="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden relative animate-fade-in-up">

        {/* --- HEADER XANH --- */}
        <div className="bg-[#10B981] h-36 w-full relative">
          <Link
            to="/profile"
            className="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 text-white rounded-full flex items-center justify-center transition-all backdrop-blur-sm"
          >
            <i className="fa-solid fa-xmark"></i>
          </Link>
        </div>

        {/* --- FORM BODY --- */}
        <div className="px-8 pb-8">

          <form onSubmit={handleSubmit}>
            {/* 1. AVATAR (Nằm đè lên header) */}
            <div className="relative -mt-16 mb-6 flex flex-col items-center">
              <div className="relative group">
                <div className="w-32 h-32 rounded-full border-[5px] border-white shadow-md overflow-hidden bg-white">
                  <img
                    src={currentAvatar}
                    alt="Avatar"
                    className="w-full h-full object-cover"
                  />
                </div>

                {/* Nút Camera */}
                <label
                  htmlFor="avatar-upload"
                  className="absolute bottom-1 right-1 w-9 h-9 bg-gray-800 text-white rounded-full border-2 border-white flex items-center justify-center cursor-pointer hover:bg-black transition-all shadow-sm"
                  title="Thay đổi ảnh đại diện"
                >
                  <i className="fa-solid fa-camera text-xs"></i>
                </label>
                <input
                  id="avatar-upload"
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={handleImageChange}
                />
              </div>

              <h2 className="mt-3 text-xl font-bold text-gray-800">{user.name}</h2>
              <p className="text-sm text-gray-500">{user.email}</p>
            </div>

            {/* Thông báo lỗi */}
            {errorMsg && (
              <div className="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg text-center border border-red-100">
                <i className="fa-solid fa-circle-exclamation mr-1"></i> {errorMsg}
              </div>
            )}

            {/* 2. CÁC INPUT */}
            <div className="space-y-5">

              {/* Hàng 1: Tên & SĐT */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Họ và tên</label>
                  <div className="relative group">
                    <i className="fa-regular fa-user absolute left-4 top-3.5 text-gray-400 group-focus-within:text-[#10B981] transition-colors"></i>
                    <input
                      type="text"
                      value={name}
                      onChange={e => setName(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 border-none focus:bg-white focus:ring-2 focus:ring-[#10B981]/50 transition-all font-medium text-gray-700"
                      placeholder="Nhập tên hiển thị"
                    />
                  </div>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Số điện thoại</label>
                  <div className="relative group">
                    <i className="fa-solid fa-phone absolute left-4 top-3.5 text-gray-400 group-focus-within:text-[#10B981] transition-colors text-xs"></i>
                    <input
                      type="tel"
                      value={phone}
                      onChange={e => setPhone(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 border-none focus:bg-white focus:ring-2 focus:ring-[#10B981]/50 transition-all font-medium text-gray-700"
                      placeholder="Nhập số điện thoại"
                    />
                  </div>
                </div>
              </div>

              {/* Email (Readonly) */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Email <span className="text-gray-400 font-normal normal-case">(Không thể chỉnh sửa)</span></label>
                <div className="relative">
                  <i className="fa-regular fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                  <input
                    type="email"
                    value={user.email}
                    disabled
                    className="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 cursor-not-allowed"
                  />
                </div>
              </div>

              {/* Hàng 3: Tỉnh & Huyện */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2 border-t border-gray-100">
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Tỉnh / Thành phố</label>
                  <div className="relative group">
                    <i className="fa-solid fa-location-dot absolute left-4 top-3.5 text-gray-400 group-focus-within:text-[#10B981] transition-colors"></i>
                    <input
                      type="text"
                      value={province}
                      onChange={e => setProvince(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 border-none focus:bg-white focus:ring-2 focus:ring-[#10B981]/50 transition-all font-medium text-gray-700"
                      placeholder="Hà Nội, TP.HCM..."
                    />
                  </div>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-gray-500 uppercase tracking-wider ml-1">Quận / Huyện</label>
                  <div className="relative group">
                    <i className="fa-solid fa-map-pin absolute left-4 top-3.5 text-gray-400 group-focus-within:text-[#10B981] transition-colors"></i>
                    <input
                      type="text"
                      value={district}
                      onChange={e => setDistrict(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 border-none focus:bg-white focus:ring-2 focus:ring-[#10B981]/50 transition-all font-medium text-gray-700"
                      placeholder="Quận Cầu Giấy..."
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* 3. BUTTONS */}
            <div className="mt-8 flex gap-4">
              <Link
                to="/profile"
                className="flex-1 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold text-center hover:bg-gray-50 transition-all"
              >
                Hủy bỏ
              </Link>
              <button
                type="submit"
                disabled={isLoading}
                className="flex-1 py-3 rounded-xl bg-[#10B981] text-white font-bold hover:bg-[#059669] shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-wait"
              >
                {isLoading ? <i className="fa-solid fa-circle-notch fa-spin"></i> : <i className="fa-regular fa-floppy-disk"></i>}
                Lưu thay đổi
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  );
};