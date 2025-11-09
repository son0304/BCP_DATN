// src/pages/Detail_User.tsx
import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import type { User } from '../../Types/user';
import Booking_history from './Booking_history';

const InfoItem: React.FC<{ icon: string; label: string; value?: string | number | null }> = ({ icon, label, value }) => (
    <div>
        <dt className="text-sm font-medium text-gray-500 flex items-center">
            <i className={`fa-solid ${icon} text-[#348738] w-5 mr-2`}></i>
            <span>{label}</span>
        </dt>
        <dd className="mt-1 text-base text-gray-900">
            {value || <span className="text-gray-400 italic">Chưa cập nhật</span>}
        </dd>
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
                setError("Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.");
            }
        } catch (e) {
            console.error("Lỗi khi parse user từ localStorage:", e);
            setError("Dữ liệu người dùng bị lỗi.");
        }
    }, []);

    if (error || !user) {
        return (
            <div className="flex flex-col items-center justify-center h-full min-h-[400px] text-center p-6">
                <h2 className="text-2xl font-bold text-gray-800">Không thể tải thông tin</h2>
                <p className="text-gray-500 mt-2">
                    {error || "Không có dữ liệu người dùng."}
                </p>
                
            </div>
        );
    }

    // Tạo avatar fallback
    const avatarUrl = user.avt || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name.charAt(0))}&background=random&color=fff`;

    return (
        <div className="bg-gray-50 min-h-screen p-4 sm:p-8">
            <div className="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
                {/* === PHẦN HEADER CỦA CARD === */}
                <div className="p-6 md:p-8 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                        {/* Avatar và Tên */}
                        <div className="flex items-center gap-5">
                            <img
                                src={avatarUrl}
                                alt={user.name}
                                className="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md ring-2 ring-[#348738]"
                            />
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">{user.name}</h1>
                                <p className="text-lg text-gray-600">{user.email}</p>
                                <span className={`mt-2 inline-block px-3 py-1 text-xs font-semibold rounded-full ${user.role_id === 1 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                                    }`}>
                                    {user.role_id === 1 ? 'Admin' : 'User'}
                                </span>
                            </div>
                        </div>
                        {/* Nút Hành động */}
                        <div className="flex-shrink-0">
                            <Link
                                to={`/profile/edit`} // Sửa link thành trang edit profile
                                state={{ user: user }} // Truyền dữ liệu user qua state
                                className="px-5 py-2.5 bg-orange-500 rounded-full hover:bg-orange-600 transition text-white font-medium shadow-md"
                            >
                                <i className="fa-solid fa-pen me-2"></i>
                                Chỉnh sửa
                            </Link>
                        </div>
                    </div>
                </div>

                {/* === PHẦN THÂN CỦA CARD (CHI TIẾT) === */}
                <div className="p-6 md:p-8">
                    <dl className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <InfoItem
                            icon="fa-phone"
                            label="Số điện thoại"
                            value={user.phone}
                        />
                        <InfoItem
                            icon="fa-check-circle"
                            label="Trạng thái"
                            value={user.is_active ? 'Đang hoạt động' : 'Đã khóa'}
                        />
                        <InfoItem
                            icon="fa-map-pin"
                            label="Tỉnh/Thành phố"
                            value={user.province?.toString()} // Nên thay bằng user.province.name
                        />
                        <InfoItem
                            icon="fa-map-marker-alt"
                            label="Quận/Huyện"
                            value={user.district?.toString()} // Nên thay bằng user.district.name
                        />
                        {/* <div className="md:col-span-2">
                             <InfoItem 
                                icon="fa-compass" 
                                label="Tọa độ (Lat/Lng)" 
                                value={user.lat && user.lng ? `${user.lat}, ${user.lng}` : null} 
                            />
                        </div> */}
                    </dl>
                </div>
            </div>

            <div className="max-w-4xl mx-auto my-2 bg-white rounded-2xl shadow-xl overflow-hidden">
                <Booking_history user={user} />
            </div>
        </div>
    );
};

export default Detail_User;