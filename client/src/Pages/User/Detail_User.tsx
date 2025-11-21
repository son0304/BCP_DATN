// src/pages/Detail_User.tsx
import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import type { User } from '../../Types/user';
import Booking_history from './Booking_history';

// Component con hiển thị từng mục thông tin
const InfoCard: React.FC<{ icon: string; label: string; value?: string | number | null; color: string }> = ({ icon, label, value, color }) => (
    <div className="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:shadow-md transition-shadow duration-300">
        <div className={`w-12 h-12 rounded-full flex items-center justify-center text-white shadow-sm ${color}`}>
            <i className={`fa-solid ${icon} text-lg`}></i>
        </div>
        <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide">{label}</p>
            <p className="text-sm md:text-base font-bold text-gray-800 mt-0.5">
                {value || <span className="text-gray-400 italic font-normal">Chưa cập nhật</span>}
            </p>
        </div>
    </div>
);

const Detail_User = () => {
    const [user, setUser] = useState<User | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        try {
            const userStr = localStorage.getItem("user");
            if (userStr) {
                setUser(JSON.parse(userStr));
            } else {
                setError("Không tìm thấy thông tin người dùng.");
            }
        } catch (e) {
            console.error("Lỗi parse user:", e);
            setError("Dữ liệu người dùng bị lỗi.");
        }
    }, []);

    if (error || !user) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-6">
                <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i className="fa-solid fa-user-slash text-3xl text-gray-400"></i>
                </div>
                <h2 className="text-2xl font-bold text-gray-800">Không thể tải hồ sơ</h2>
                <p className="text-gray-500 mt-2">{error || "Vui lòng đăng nhập lại."}</p>
                <Link to="/login" className="mt-6 px-6 py-2 bg-emerald-600 text-white rounded-full hover:bg-emerald-700 transition">
                    Đăng nhập ngay
                </Link>
            </div>
        );
    }

    // Avatar fallback
    const avatarUrl = user.avt || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=10B981&color=fff&size=128`;

    // Xử lý hiển thị địa chỉ an toàn (nếu province/district là object)
    const provinceName = typeof user.province === 'object' && user.province !== null ? (user.province as any).name : user.province;
    const districtName = typeof user.district === 'object' && user.district !== null ? (user.district as any).name : user.district;

    return (
        <div className="bg-gray-50 min-h-screen font-sans pb-10">
            {/* === 1. HEADER COVER & AVATAR === */}
            <div className="relative mb-20 md:mb-24">
                {/* Cover Background */}
                <div className="h-48 md:h-64 bg-gradient-to-r from-emerald-800 via-teal-600 to-emerald-500 relative overflow-hidden">
                    <div className="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    <div className="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black/30 to-transparent"></div>
                </div>

                {/* Container nội dung Header */}
                <div className="max-w-6xl mx-auto px-4 sm:px-6 relative">
                    <div className="absolute -bottom-16 md:-bottom-20 left-6 md:left-10 flex flex-col md:flex-row items-end md:items-center gap-6 w-full">
                        
                        {/* Avatar */}
                        <div className="relative group">
                            <img
                                src={avatarUrl}
                                alt={user.name}
                                className="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-white shadow-2xl ring-4 ring-emerald-50 group-hover:scale-105 transition-transform duration-300 bg-white"
                            />
                            <div className="absolute bottom-2 right-2 w-6 h-6 bg-green-500 border-2 border-white rounded-full" title="Online"></div>
                        </div>

                        {/* Tên & Role */}
                        <div className="pb-2 md:pb-0 flex-1">
                            <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 md:text-white md:drop-shadow-md mb-1">
                                {user.name}
                            </h1>
                            <div className="flex flex-wrap items-center gap-3">
                                <span className="text-gray-600 md:text-emerald-50 font-medium text-sm md:text-base flex items-center gap-1">
                                    <i className="fa-regular fa-envelope"></i> {user.email}
                                </span>
                                <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm border border-white/20 ${
                                    user.role_id === 1 
                                    ? 'bg-red-500 text-white' 
                                    : 'bg-emerald-500 text-white'
                                }`}>
                                    {user.role_id === 1 ? 'Administrator' : 'Member'}
                                </span>
                            </div>
                        </div>

                        {/* Nút Edit (Desktop: Nằm bên phải, Mobile: Nằm dưới) */}
                        <div className="hidden md:block pb-4 pr-10">
                            <Link
                                to="/profile/edit"
                                state={{ user: user }}
                                className="flex items-center gap-2 px-6 py-3 bg-white text-gray-800 font-bold rounded-full shadow-lg hover:bg-emerald-50 hover:text-emerald-700 transition-all transform hover:-translate-y-1"
                            >
                                <i className="fa-solid fa-pen-to-square"></i>
                                <span>Chỉnh sửa hồ sơ</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            {/* Nút Edit cho Mobile */}
            <div className="md:hidden px-4 mb-8 mt-20 flex justify-center">
                <Link
                    to="/profile/edit"
                    state={{ user: user }}
                    className="w-full text-center py-3 bg-emerald-600 text-white font-bold rounded-xl shadow-md active:scale-95 transition"
                >
                    Chỉnh sửa hồ sơ
                </Link>
            </div>

            {/* === 2. NỘI DUNG CHÍNH === */}
            <div className="max-w-6xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {/* Cột Trái: Thông tin chi tiết */}
                <div className="lg:col-span-1 space-y-6">
                    <div className="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 overflow-hidden relative">
                        <div className="absolute top-0 right-0 p-4 opacity-10">
                            <i className="fa-regular fa-id-card text-8xl text-emerald-500"></i>
                        </div>
                        
                        <h3 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2 relative z-10">
                            <span className="w-8 h-1 rounded-full bg-emerald-500 block"></span>
                            Thông tin cá nhân
                        </h3>
                        
                        <div className="space-y-4 relative z-10">
                            <InfoCard 
                                icon="fa-phone" 
                                label="Số điện thoại" 
                                value={user.phone} 
                                color="bg-blue-500" 
                            />
                            <InfoCard 
                                icon="fa-map-location-dot" 
                                label="Địa chỉ" 
                                value={districtName ? `${districtName}, ${provinceName}` : null} 
                                color="bg-orange-500" 
                            />
                            <InfoCard 
                                icon="fa-toggle-on" 
                                label="Trạng thái" 
                                value={user.is_active ? 'Đang hoạt động' : 'Đã khóa'} 
                                color={user.is_active ? 'bg-green-500' : 'bg-gray-400'} 
                            />
                        </div>

                        <div className="mt-8 pt-6 border-t border-gray-100 text-center">
                            <p className="text-xs text-gray-400">Thành viên từ năm {new Date().getFullYear()}</p>
                        </div>
                    </div>
                </div>

                {/* Cột Phải: Lịch sử đặt sân */}
                <div className="lg:col-span-2">
                    <div className="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden min-h-[400px]">
                        <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <h3 className="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i className="fa-solid fa-clock-rotate-left text-emerald-600"></i>
                                Lịch sử đặt sân
                            </h3>
                            {/* <button className="text-sm text-emerald-600 font-semibold hover:underline">Xem tất cả</button> */}
                        </div>
                        
                        <div className="p-2">
                             {/* Truyền user vào component lịch sử */}
                            <Booking_history user={user} />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    );
};

export default Detail_User;